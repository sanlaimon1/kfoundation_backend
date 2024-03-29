<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerExtra;
use App\Models\FinancialAsset;
use App\Models\FinancialBalance;
use App\Models\FinancialIntegration;
use App\Models\FinancialPlatformCoin;
use App\Models\Level;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Models\Permission;
use Illuminate\Support\Facades\Redis;
use App\Models\Teamlevel;
use App\Models\TeamExtra;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Rules\InvitedCodeRule;
use Illuminate\Support\Facades\Log as LogFile;

class CustomerController extends Controller
{
    /*
    index   1
    create  2
    store   4
    show    8
    edit    16
    update  32
    destory 64
    */
    private $path_name = "/customer";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 客户列表
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = Customer::where('status',1)
                  ->orderBy('created_at', 'desc')->paginate($perPage);

        $title = "会员列表";

        return view( 'customer.index', compact('records', 'title') );
    }

     /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 2) ){
            return "您没有权限访问这个路径";
        }
        $levels = Level::all();
        $teamlevels = Teamlevel::all();
        $title = "会员列表";

        $customer_identity = config('types.customer_identity');
        return view( 'customer.create',compact('levels','teamlevels','title','customer_identity'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 4) ){
                return "您没有权限访问这个路径";
            }

            $request->validate([
                "phone" => 'required|unique:customers',
                "is_allowed_code" => ['required', 'integer', 'in:0,1'],
                "identity" => ['required', 'integer', 'in:1,2'],
                "is_sure" => ['required', 'integer', 'in:0,1'],
                "level_id" => ['required', 'integer', 'exists:levels,level_id'],
                "team_id" => ['required', 'integer', 'exists:teamlevels,tid'],
                'password1' => 'required|confirmed',
                'password1' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],

                'password2' => 'required|confirmed|min:6|max:6',
                // 'password2' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
                "idcard_front.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
                "idcard_back.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            ]);
            $idcard_front = '/images/default.png';
            if($request->hasFile('idcard_front')){
                $idcard_front = time().'.'.$request->idcard_front->extension();
                $request->idcard_front->move(public_path('/images/customer_idimg/'),$idcard_front);
                $idcard_front = '/images/customer_idimg/'.$idcard_front;
            }
            $idcard_back = '/images/default.png';
            if($request->hasFile('idcard_back')){
                $idcard_back = time().'back'.'.'.$request->idcard_back->extension();
                $request->idcard_back->move(public_path('/images/customer_idimg/'),$idcard_back);
                $idcard_back = '/images/customer_idimg/'.$idcard_back;
            }

            $invited_code = '';
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

            for ($i = 0; $i < 8; $i++) {
                $index = rand(0, strlen($characters) - 1);
                $invited_code .= $characters[$index];
            }

            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $salt = substr(str_shuffle($chars), 0, 6);      //6位随机盐


            $hash_password1 = sha1( $salt . md5($salt . $request->password1) );
            $hash_password2 = sha1( $salt . md5($salt . $request->password2) );

            DB::beginTransaction();
            try {
                $customer = new Customer();
                $customer->phone = $request->phone;
                $customer->realname = $request->realname;
                $customer->invited_code = $invited_code;
                $customer->is_allowed_code = $request->is_allowed_code;
                $customer->identity = $request->identity;
                $customer->is_sure = $request->is_sure;
                $customer->level_id = $request->level_id;
                $customer->team_id = $request->team_id;
                $customer->password = $hash_password1;
                $customer->password2 = $hash_password2;
                $customer->salt = $salt;
                $customer->idcard_front = $idcard_front;
                $customer->idcard_back = $idcard_back;

                if(!$customer->save())
                throw new \Exception('事务中断1');

                $customer_extra = new CustomerExtra();
                $customer_extra->userid = $customer->id;
                $customer_extra->level_ids = 0;
                $customer_extra->all_children_ids = '';

                if(!$customer_extra->save())
                throw new \Exception('事务中断2');

                $team_extra = new TeamExtra();
                $team_extra->userid = $customer->id;

                if(!$team_extra->save())
                throw new \Exception('事务中断3');


                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $store_action = ['username' => $username, 'type' => 'log.customer_store_action'];
                $action = json_encode($store_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.store';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断4');

                $customer = array(
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'realname' => $customer->realname,
                    'invited_code' => $customer->invited_code,
                    'is_allowed_code' => $customer->is_allowed_code,
                    'identity' => $customer->identity,
                    'is_sure' => $customer->is_sure,
                    'level_id' => $customer->level_id,
                    'team_id' => $customer->team_id,
                    'password' => $customer->password,
                    'password2' => $customer->password2,
                    'salt' => $customer->salt,
                    'idcard_front' => $customer->idcard_front,
                    'idcard_back' => $customer->idcard_back,
                );
                $customer_json = json_encode($customer);
                DB::commit();
                LogFile::channel("customer_store")->info($customer_json);

            } catch (\Exception $e) {
                DB::rollback();

                $errorMessage = $e->getMessage();
                LogFile::channel("customer_store_error")->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 8) ){
            return "您没有权限访问这个路径";
        }

        $customer_identity = config('types.customer_identity');

        $customer = Customer::find($id);
        $customer_extra = CustomerExtra::where('userid',$id)->first();
        return view('customer.show',compact('customer','customer_extra', 'customer_identity'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 16) ){
            return "您没有权限访问这个路径";
        }
        $levels = Level::all();
        $teamlevels = Teamlevel::all();
        $customer = Customer::find($id);
        $customer_identity = config('types.customer_identity');
        return view('customer.edit', compact('levels','teamlevels','customer','customer_identity'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(),config('app.redis_second'));

            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 32) ){
                return "您没有权限访问这个路径";
            }
            $customer = Customer::find($id);
            $request->validate([
                "invited_code" => ['required','min:8','max:8', new InvitedCodeRule($id) ],
                "is_allowed_code" => ['required', 'integer', 'in:0,1'],
                "is_sure" => ['required', 'integer', 'in:0,1'],
                "level_id" => ['required', 'integer', 'exists:levels,level_id'],
                "team_id" => ['required', 'integer', 'exists:teamlevels,tid'],
                "idcard_front.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
                "idcard_back.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            ]);
            if($request->hasFile('idcard_front')){
                $idcard_front = time().'.'.$request->idcard_front->extension();
                $request->idcard_front->move(public_path('/images/customer_idimg/'),$idcard_front);
                $idcard_front = '/images/customer_idimg/'.$idcard_front;
            }else{
                $idcard_front = $request->old_idcard_front;
            }
            if($request->hasFile('idcard_back')){
                $idcard_back = time().'back'.'.'.$request->idcard_back->extension();
                $request->idcard_back->move(public_path('/images/customer_idimg/'),$idcard_back);
                $idcard_back = '/images/customer_idimg/'.$idcard_back;
            }else{
                $idcard_back = $request->old_idcard_back;
            }

            DB::beginTransaction();
            try {

                $customer->realname = $request->realname;
                $customer->invited_code = $request->invited_code;
                $customer->is_allowed_code = $request->is_allowed_code;
                $customer->is_sure = $request->is_sure;
                $customer->level_id = $request->level_id;
                $customer->team_id = $request->team_id;
                $customer->idcard_front = $idcard_front;
                $customer->idcard_back = $idcard_back;
                $customer->updated_at = date('Y-m-d H:i:s');
                if(!$customer->save())
                throw new \Exception('事务中断3');

                $myself = Auth::user();
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $update_action = ['username' => $myself->username, 'type' => 'log.customer_update_action'];
                $action = json_encode($update_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.update';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断4');

                $customer = array(
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'realname' => $customer->realname,
                    'invited_code' => $customer->invited_code,
                    'is_allowed_code' => $customer->is_allowed_code,
                    'identity' => $customer->identity,
                    'is_sure' => $customer->is_sure,
                    'level_id' => $customer->level_id,
                    'team_id' => $customer->team_id,
                    'password' => $customer->password,
                    'password2' => $customer->password2,
                    'salt' => $customer->salt,
                    'idcard_front' => $customer->idcard_front,
                    'idcard_back' => $customer->idcard_back,
                );
                $customer_json = json_encode($customer);
                DB::commit();
                LogFile::channel("customer_update")->info("会员列表 更新成功");

            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel("customer_update_error")->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(),config('app.redis_second'));

            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 64) ){
                return "您没有权限访问这个路径";
            }

            DB::beginTransaction();
            try {
                //code...
                $customer = Customer::find($id);
                $customer->status = 0;
                if(!$customer->save())
                    throw new \Exception('事务中断5');

                $myself = Auth::user();
                $log = new Log();
                $log->adminid = $myself->id;
                $delete_action = ['username' => $myself->username, 'customer_name' => $customer->realname, 'type' => 'log.customer_delete_action'];
                $action = json_encode($delete_action);
                $log->action = $action;
                $log->ip = $request->ip();
                $log->route = 'customer.destroy';
                $input = $request->all();
                $input_json = json_encode( $input );
                $log->parameters = $input_json;  // 请求参数
                $log->created_at = date('Y-m-d H:i:s');

                if(!$log->save())
                    throw new \Exception('事务中断6');

                $customer = array(
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'realname' => $customer->realname,
                    'status' => $customer->status
                );
                $customer_json = json_encode($customer);
                DB::commit();
                LogFile::channel("customer_destroy")->info($customer_json);

            } catch (\Exception $e) {
                DB::rollBack();
                //echo $e->getMessage();
                $errorMessage = $e->getMessage();
                LogFile::channel("customer_destroy_error")->error($errorMessage);
                return '修改错误，事务回滚';
            }

            return redirect()->route('customer.index');
    }

    public function customer_search(Request $request)
    {
        $fid = $request->fid;
        $phone = $request->phone;
        $date_string = $request->created_at;
        if($date_string){
            $date_parts = explode('至', $date_string);
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            $start_date = '';
            $end_date = '';
        }

        if($fid !=null && $phone != null && $date_string!=null)
        {
            $search_customer =Customer::whereBetween('customers.created_at', [$start_date, $end_date])
                            ->where([['customers.id','=',$fid],['customers.phone','=',$phone],['customers.status','=',1]])
                            ->orderBy('customers.created_at','desc')
                            ->select('customers.*')
                            ->with('level')
                            ->get();
            foreach($search_customer as $customer)
            {
                $customer->parent_name = $customer->getParentName();
            }
        }else{
            $search_customer = Customer::where('customers.status',1)
                                ->whereBetween('customers.created_at', [$start_date, $end_date])
                                ->orwhere([['customers.id','=',$fid],['customers.status',1]])
                                ->orwhere([['customers.phone','=',$phone],['customers.status',1]])
                                ->orderBy('customers.created_at','desc')
                                ->select('customers.*')
                                ->with('level')
                                ->get();
            foreach($search_customer as $customer)
            {
                $customer->parent_name = $customer->getParentName();
            }
        }

        return response()->json([
            "search_customer" => $search_customer
        ]);
    }

    /**
     * 踢出  kick out
     */
    public function kickout(string $id) {

        $id = (int)$id;
        $one = Customer::find($id);

        return view('customer.kickout',compact('id','one'));
    }

    /**
     * 踢出逻辑  kick logic
     */
    public function kick(Request $request, string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(),config('app.redis_second'));

            $id = (int)$id;
            $one = Customer::find($id);

            if($one->access_token==='null') {

                return '无需操作';
            }

            DB::beginTransaction();
            try {
                $one->access_token = 'null';
                if (!$one->save())
                    throw new \Exception('事务中断7');

                if( Redis::exists($one->access_token) )
                {
                    Redis::del($one->access_token);
                }
                //添加管理员日志
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $kick_action = ['username' => Auth::user()->username, 'phone' => $one->phone, 'type' => 'log.customer_kick_action'];
                $action = json_encode($kick_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.kick';
                $newlog->parameters = json_encode($request->all());
                $newlog->created_at = date('Y-m-d H:i:s');
                if (!$newlog->save())
                    throw new \Exception('事务中断8');
                $customer = array(
                    'id' => $one->id,
                    'phone' => $one->phone,
                    'realname' => $one->realname,
                    'access_token' => $one->access_token
                );
                $customer_json = json_encode($customer);
                DB::commit();
                LogFile::channel('customer_kick')->info($customer_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('customer_kick_error')->error($errorMessage);
                return $errorMessage;
                //return '踢出错误，事务回滚';
            }

            return redirect()->route('customer.kickout', ['id'=>$id]);
    }

    //修改密码
    public function modify_pass(string $id) {
        $id = (int)$id;
        $customer = Customer::find($id);
        return view('customer.password', compact('id', 'customer'));
    }

    //存儲密碼1
    public function customer_password1(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'password' => 'required|confirmed',
                'password_confirmation' => 'required|same:password',
                'password' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
            ]);

            $customer_id = $request->customer_id;
            $customer = Customer::find($customer_id);

            $salt = $customer->salt;
            $hash_password1 = sha1( $salt . md5($salt . $request->password) );

            DB::beginTransaction();
            try {
                $customer->password = $hash_password1;
                if(!$customer->save())
                throw new \Exception('事务中断7');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $pwd1_action = ['username' => $username, 'type' => 'log.customer_pwd1_action'];
                $action = json_encode($pwd1_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.password1';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断8');

                $customer = array(
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'realname' => $customer->realname,
                    'password' => $customer->password
                );
                $customer_json = json_encode($customer);
                DB::commit();
                LogFile::channel('customer_password1')->info($customer_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('customer_password1_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //存儲密碼2
    public function customer_password2(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'password2' => 'required|confirmed|min:6|max:6',
                'password2_confirmation' => 'required|same:password2',
                // 'password2' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
                // 'password2' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/','regex:/^[0-9]{6}$/'],

            ]);

            $customer_id = $request->customer_id;
            $customer = Customer::find($customer_id);

            $salt = $customer->salt;
            $hash_password2 = sha1( $salt . md5($salt . $request->password2) );

            DB::beginTransaction();
            try {
                $customer->password2 = $hash_password2;
                if(!$customer->save())
                throw new \Exception('事务中断9');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $pwd2_action = ['username' => $username, 'type' => 'log.customer_pwd2_action'];
                $action = json_encode($pwd2_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.password2';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断10');

                $customer = array(
                    'id' => $customer->id,
                    'phone' => $customer->phone,
                    'realname' => $customer->realname,
                    'password2' => $customer->password2
                );
                $customer_json = json_encode($customer);
                DB::commit();
                LogFile::channel('customer_password2')->info($customer_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('customer_password2_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //上分
    public function charge(string $id)
    {
        $id = (int)$id;
        $customer = Customer::find($id);

        return view('customer.charge', compact('id', 'customer'));
    }

    //存儲財務餘額
    public function charge_financial_balance(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'financial_balance_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->financial_balance_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的余额上分" . $amount;
                $detail =  ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount, 'type' => 'finance.charge_balance'];
                
                $balance = $customer->balance + $amount;

                $financial_balance = new FinancialBalance();
                $financial_balance->userid = $customer->id;
                $financial_balance->amount = $amount;
                $financial_balance->balance = $customer->balance;
                $financial_balance->direction = 1;
                $financial_balance->financial_type = 5;
                $financial_balance->details = json_encode($detail);
                $financial_balance->after_balance = $balance;
                $financial_balance->created_at = date('Y-m-d H:i:s');
                if(!$financial_balance->save())
                throw new \Exception('事务中断11');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断11');

                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $charge_action = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount, 'type' => 'log.customer_charge_fbalance_action'];
                $action = json_encode($charge_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_balance';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断12');

                $financial_balance = array(
                    'id' => $financial_balance->id,
                    'userid' => $financial_balance->userid,
                    'amount' => $financial_balance->amount,
                    'balance' => $financial_balance->balance,
                    'direction' => $financial_balance->direction,
                    'financial_type' => $financial_balance->financial_type,
                    'details' => $financial_balance->details,
                    'after_balance' => $financial_balance->after_balance,
                    'created_at' => $financial_balance->created_at,
                );
                $financial_balance_json = json_encode($financial_balance);
                DB::commit();
                LogFile::channel('financial_balance')->info($financial_balance_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('financial_balance_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //儲存金融資產
    public function charge_financial_asset(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));
            $request->validate([
                'financial_asset_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->financial_asset_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的资产上分" .  $amount;
                $detail =  ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount, 'type' => 'finance.charge_asset'];

                $balance = $customer->balance + $amount;
                $financial_asset = new FinancialAsset();
                $financial_asset->userid = $customer->id;
                $financial_asset->amount = $amount;
                $financial_asset->balance = $customer->balance;
                $financial_asset->direction = 1;
                $financial_asset->financial_type = 5;
                $financial_asset->details = json_encode($detail);
                $financial_asset->after_balance = $balance;
                $financial_asset->created_at = date('Y-m-d H:i:s');
                if(!$financial_asset->save())
                throw new \Exception('事务中断13');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断13');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $log_action = ['username' => $username,'type' => 'log.charge_balance_log'];
                $newlog->action = json_encode($log_action); //'管理员' . $username . ' 儲存金融資產 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_asset';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断14');
                    $financial_asset = array(
                        'id' => $financial_asset->id,
                        'userid' => $financial_asset->userid,
                        'amount' => $financial_asset->amount,
                        'balance' => $financial_asset->balance,
                        'direction' => $financial_asset->direction,
                        'financial_type' => $financial_asset->financial_type,
                        'details' => $financial_asset->details,
                        'after_balance' => $financial_asset->after_balance,
                        'created_at' => $financial_asset->created_at,
                    );
                    $financial_asset_json = json_encode($financial_asset);
                    DB::commit();
                    LogFile::channel('financial_asset')->info($financial_asset_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('financial_asset_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //門店財務整合
    public function charge_financial_integration(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'financial_integration_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->financial_integration_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的积分上分" .  $amount;
                $detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'finance.charge_integration'];

                $balance = $customer->balance + $amount;
                $financial_integration = new FinancialIntegration();
                $financial_integration->userid = $customer->id;
                $financial_integration->amount = $amount;
                $financial_integration->balance = $customer->balance;
                $financial_integration->direction = 1;
                $financial_integration->financial_type = 5;
                $financial_integration->details = json_encode($detail);
                $financial_integration->after_balance = $balance;
                $financial_integration->created_at = date('Y-m-d H:i:s');
                if(!$financial_integration->save())
                throw new \Exception('事务中断15');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断15');
                
                $username = Auth::user()->username;
                $log_action = ['username' => $username, 'type' => 'log.charge_integration_log'];
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = json_encode($log_action); //'管理员' . $username . ' 門店財務整合 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_integration';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断16');

                $financial_integration = array(
                    'id' => $financial_integration->id,
                    'userid' => $financial_integration->userid,
                    'amount' => $financial_integration->amount,
                    'balance' => $financial_integration->balance,
                    'direction' => $financial_integration->direction,
                    'financial_type' => $financial_integration->financial_type,
                    'details' => $financial_integration->details,
                    'after_balance' => $financial_integration->after_balance,
                    'created_at' => $financial_integration->created_at,
                );
                $financial_integration_json = json_encode($financial_integration);
                DB::commit();
                LogFile::channel('financial_integration')->info($financial_integration_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('financial_integration_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //存儲金融平台幣
    public function charge_financial_platform_coin(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'financial_platform_coin_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->financial_platform_coin_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail = "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的平台币上分" .  $amount;
                $detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'finance.charge_platform_coin'];

                $balance = $customer->balance + $amount;
                $financial_platform_coin = new FinancialPlatformCoin();
                $financial_platform_coin->userid = $customer->id;
                $financial_platform_coin->amount = $amount;
                $financial_platform_coin->balance = $customer->balance;
                $financial_platform_coin->direction = 1;
                $financial_platform_coin->financial_type = 5;
                $financial_platform_coin->details = json_encode($detail);
                $financial_platform_coin->after_balance = $balance;
                $financial_platform_coin->created_at = date('Y-m-d H:i:s');
                if(!$financial_platform_coin->save())
                throw new \Exception('事务中断17');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断17');

                $username = Auth::user()->username;
                $log_action = ['username' => $username,'type' => 'log.charge_platform_coin_log'];

                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = json_encode($log_action); //'管理员' . $username . ' 存儲金融平台幣 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_platform_coin';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断18');

                $financial_platform_coin = array(
                    'id' => $financial_platform_coin->id,
                    'userid' => $financial_platform_coin->userid,
                    'amount' => $financial_platform_coin->amount,
                    'balance' => $financial_platform_coin->balance,
                    'direction' => $financial_platform_coin->direction,
                    'financial_type' => $financial_platform_coin->financial_type,
                    'details' => $financial_platform_coin->details,
                    'after_balance' => $financial_platform_coin->after_balance,
                    'created_at' => $financial_platform_coin->created_at,
                );
                $financial_platform_coin_json = json_encode($financial_platform_coin);
                DB::commit();
                LogFile::channel('financial_platform_coin')->info($financial_platform_coin_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('financial_platform_coin_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //下分
    public function withdrawal(string $id) {
        $id = (int)$id;
        $customer = Customer::find($id);

        return view('customer.withdrawal', compact('id', 'customer'));
    }

    //門店提款餘額
    public function withdraw_financial_balance(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'withdraw_balance_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->withdraw_balance_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =   "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的余额上下分" .  $amount;
                $detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'finance.withdraw_balance'];
                $balance = $customer->balance - $amount;
                $financial_balance = new FinancialBalance();
                $financial_balance->userid = $customer->id;
                $financial_balance->amount = $amount;
                $financial_balance->balance = $customer->balance;
                $financial_balance->direction = -1;
                $financial_balance->financial_type = 6;
                $financial_balance->details = json_encode($detail);
                $financial_balance->after_balance = $balance;
                $financial_balance->created_at = date('Y-m-d H:i:s');
                if(!$financial_balance->save())
                throw new \Exception('事务中断19');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断19');

                $username = Auth::user()->username;
                $log_action = ['username' => $username, 'type' => 'log.withdraw_balance_log'];
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = json_encode($log_action); //'管理员' . $username . ' 存儲財務餘額 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_balance';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断20');

                $financial_balance = array(
                    'id' => $financial_balance->id,
                    'userid' => $financial_balance->userid,
                    'amount' => $financial_balance->amount,
                    'balance' => $financial_balance->balance,
                    'direction' => $financial_balance->direction,
                    'financial_type' => $financial_balance->financial_type,
                    'details' => $financial_balance->details,
                    'after_balance' => $financial_balance->after_balance,
                    'created_at' => $financial_balance->created_at,
                );
                $financial_balance_json = json_encode($financial_balance);
                DB::commit();
                LogFile::channel('withdraw_financial_balance')->info($financial_balance_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('withdraw_financial_balance_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //存入和提取金融資產
    public function withdraw_financial_asset(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'withdraw_asset_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->withdraw_asset_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =     "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的资产下分" .  $amount;
                $detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'finance.withdraw_asset'];
                $balance = $customer->balance - $amount;
                $financial_asset = new FinancialAsset();
                $financial_asset->userid = $customer->id;
                $financial_asset->amount = $amount;
                $financial_asset->balance = $customer->balance;
                $financial_asset->direction = -1;
                $financial_asset->financial_type = 6;
                $financial_asset->details = json_encode($detail);
                $financial_asset->after_balance = $balance;
                $financial_asset->created_at = date('Y-m-d H:i:s');
                if(!$financial_asset->save())
                throw new \Exception('事务中断21');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断21');

                $log_detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'log.withdraw_asset_log'];

                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = json_encode($log_detail);
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_asset';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断22');

                $financial_asset = array(
                    'id' => $financial_asset->id,
                    'userid' => $financial_asset->userid,
                    'amount' => $financial_asset->amount,
                    'balance' => $financial_asset->balance,
                    'direction' => $financial_asset->direction,
                    'financial_type' => $financial_asset->financial_type,
                    'details' => $financial_asset->details,
                    'after_balance' => $financial_asset->after_balance,
                    'created_at' => $financial_asset->created_at,
                );
                $financial_asset_json = json_encode($financial_asset);
                DB::commit();
                LogFile::channel('withdraw_financial_asset')->info($financial_asset_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('withdraw_financial_asset_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //积分下分
    public function withdraw_financial_integration(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'withdraw_integration_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->withdraw_integration_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的积分下分" .  $amount;
                $detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'finance.withdraw_integration'];

                $balance = $customer->balance - $amount;

                $financial_integration = new FinancialIntegration();
                $financial_integration->userid = $customer->id;
                $financial_integration->amount = $amount;
                $financial_integration->balance = $customer->balance;
                $financial_integration->direction = -1;
                $financial_integration->financial_type = 6;
                $financial_integration->details = json_encode($detail);
                $financial_integration->after_balance = $balance;
                $financial_integration->created_at = date('Y-m-d H:i:s');
                if(!$financial_integration->save())
                throw new \Exception('事务中断23');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断23');
                $log_detail = ['username' => Auth::user()->username, 'phone' => $customer->phone, 'amount' => $amount , 'type' => 'log.withdraw_integration_log'];

                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = json_encode($log_detail);
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_integration';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断24');

                $financial_integration = array(
                    'id' => $financial_integration->id,
                    'userid' => $financial_integration->userid,
                    'amount' => $financial_integration->amount,
                    'balance' => $financial_integration->balance,
                    'direction' => $financial_integration->direction,
                    'financial_type' => $financial_integration->financial_type,
                    'details' => $financial_integration->details,
                    'after_balance' => $financial_integration->after_balance,
                    'created_at' => $financial_integration->created_at,
                );
                $financial_integration_json = json_encode($financial_integration);
                DB::commit();
                LogFile::channel('withdraw_financial_integration')->info($financial_integration_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('withdraw_financial_integration_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //商店取款金融平台幣
    public function withdraw_financial_platform_coin(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $request->validate([
                'withdraw_platform_coin_amount' => ['required', 'numeric', 'gt:0']
            ]);
            $customer_id = $request->customer_id;
            $amount =  $request->withdraw_platform_coin_amount;

            DB::beginTransaction();
            try{
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                $customer = Customer::find($customer_id);
                // $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的平台币下分" .  $amount;
                $detail = ['username' => Auth::user()->name, 'phone' => $customer->phone, 'amount', 'type' => 'finance.withdraw_platform_coin'];
                $balance = $customer->balance - $amount;

                $financial_platform_coin = new FinancialPlatformCoin();
                $financial_platform_coin->userid = $customer->id;
                $financial_platform_coin->amount = $amount;
                $financial_platform_coin->balance = $customer->balance;
                $financial_platform_coin->direction = -1;
                $financial_platform_coin->financial_type = 6;
                $financial_platform_coin->details = json_encode($detail);
                $financial_platform_coin->after_balance = $balance;
                $financial_platform_coin->created_at = date('Y-m-d H:i:s');
                if(!$financial_platform_coin->save())
                throw new \Exception('事务中断25');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断25');

                $username = Auth::user()->username;
                $log_action = ['username' => $username, 'type' => 'log.withdraw_platform_coin_log'];
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = json_encode($log_action); //'管理员' . $username . ' 商店取款金融平台幣 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_platform_coin';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断26');

                    $financial_platform_coin = array(
                        'id' => $financial_platform_coin->id,
                        'userid' => $financial_platform_coin->userid,
                        'amount' => $financial_platform_coin->amount,
                        'balance' => $financial_platform_coin->balance,
                        'direction' => $financial_platform_coin->direction,
                        'financial_type' => $financial_platform_coin->financial_type,
                        'details' => $financial_platform_coin->details,
                        'after_balance' => $financial_platform_coin->after_balance,
                        'created_at' => $financial_platform_coin->created_at,
                    );
                    $financial_platform_coin_json = json_encode($financial_platform_coin);
                    DB::commit();
                    LogFile::channel('withdraw_financial_platform_coin')->info($financial_platform_coin_json);
            } catch (\Exception $e) {
                DB::rollback();
                $errorMessage = $e->getMessage();
                LogFile::channel('withdraw_financial_platform_coin_error')->error($errorMessage);
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }


    public function team(string $id)
    {
        $id = (int)$id;

        //查询一个用户
        $one_user = Customer::find( $id );
        if(empty($one_user)) {
            return '用户不存在';
        }

        $members = Customer::where('parent_id', $id)->orderBy('created_at', 'desc')->paginate(100);

        $one_team = Teamlevel::find( $one_user->team_id );   //团队等级
        $one_team_extra = TeamExtra::where( 'userid', $id )->first();    //团队的额外信息
        $customer_extra = CustomerExtra::where('userid',  $id )->first();       //用户额外信息
        if(empty($customer_extra)) {
            return '无记录数据异常,  请管理员检查';
        }

        $children = explode(',', $customer_extra->all_children_ids );
        //团队总人数
        $count_children = count($children);

        return view('customer.team', compact('members', 'one_team', 'one_team_extra', 'count_children', 'customer_extra', 'id'));
    }


    public function list_children(string $id) {
        $id = (int)$id;

        $customer_extra = CustomerExtra::where( 'userid',  $id )->first();       //用户额外信息
        $level = $customer_extra->level;
        $all_children_ids = $customer_extra->all_children_ids;

        $members = Customer::where('parent_id', $id)->orderBy('created_at', 'desc')->get();

        return view('customer.children', compact( 'level', 'all_children_ids', 'members' ));
    }

    public function team_search(Request $request){
        $phone = $request->phone;
        //查询一个用户
        $one_user = Customer::where('phone',$phone)->first();
        $id = $one_user->id;
        if(empty($one_user)) {
            return '用户不存在';
        }
        $members = Customer::where('phone',$phone)->orderBy('created_at', 'desc')->get();
        $one_team = Teamlevel::find( $one_user->team_id );   //团队等级
        $one_team_extra = TeamExtra::where( 'userid', $one_user->id )->first();    //团队的额外信息
        $customer_extra = CustomerExtra::where('userid',  $one_user->id )->first();       //用户额外信息
        if(empty($customer_extra)) {
            return '无记录数据异常,  请管理员检查';
        }

        $children = explode(',', $customer_extra->all_children_ids );
        //团队总人数
        $count_children = count($children);

        return view('customer.team_search',  compact('members', 'one_team', 'one_team_extra', 'count_children','customer_extra','id'));
    }

    public function change_sheep(Request $request)
    {

        $id = $request->id;
        $is_sheep = $request->is_sheep;
        $customer = Customer::find($id);

        if($is_sheep == 1){
            $customer->is_sheep = 0;
            $customer->save();
        }else{
            $customer->is_sheep = 1;
            $customer->save();
        }
        $member = Customer::where('id',$id)->first();
        return response()->json([
            "member" => $member,
        ]);
    }

    public function set_sheep(Request $request)
    {

        $ids = $request->input('checkedItemIds');
        $set_sheep = Customer::whereIn('id',$ids)->update(['is_sheep' => 1]);
        return response()->json([
            "message" => "设置羊毛党成功"
        ]);

    }

    public function unset_sheep(Request $request)
    {
        $ids = $request->input('checkedItemIds');
        $set_sheep = Customer::whereIn('id',$ids)->update(['is_sheep' => 0]);
        return response()->json([
            "message" => "取消设置羊毛党成功"
        ]);
    }

    /**
     * 查询1级下级会员  query children customers with sub-level 1
     */
    public function queryLevel1(string $parentid) {
        $parentid = (int)$parentid;

        $members = Customer::where('parent_id', $parentid)
                    ->orderBy('created_at','desc')
                    ->get();

        return view('customer.members_level', compact( 'members' ));
    }

    /**
     * 查询其他级的下级会员  query children customers with sub-level X
     */
    public function queryLevelx(Request $request, string $id) {
        $is_xlevel = $request->has('xlevel');
        if(!$is_xlevel) {
            return '没有层级参数';
        }

        $xlevel = $request->get('xlevel');
        if( !is_numeric($xlevel) ) {
            return '层级参数必须是数字';
        }
        $id = (int)$id;

        $current_extra = CustomerExtra::where('userid', $id)->first();

        $members_extra = CustomerExtra::select('userid')
                        ->where('level', $xlevel)
                        ->whereIn( 'userid', explode(',', $current_extra->all_children_ids) )
                        ->get();

        $userid_array = [];
        foreach( $members_extra as $one_extra ) {
            $userid_array[] = $one_extra->userid;
        }

        $members = Customer::whereIn('id', $userid_array)
                    ->orderBy('created_at','desc')
                    ->get();

        return view('customer.members_level', compact( 'members' ));
    }

}
