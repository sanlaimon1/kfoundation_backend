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
                "identity" => ['required', 'integer', 'in:0,1,2'],
                "is_sure" => ['required', 'integer', 'in:0,1'],
                "level_id" => ['required', 'integer', 'exists:levels,level_id'],
                "team_id" => ['required', 'integer', 'exists:teamlevels,tid'],
                'password1' => 'required|confirmed',
                'password1' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],

                'password2' => 'required|confirmed',
                'password2' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
                "idcard_front.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
                "idcard_back.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            ]);
            if($request->hasFile('idcard_front')){
                $idcard_front = time().'.'.$request->idcard_front->extension();
                $request->idcard_front->move(public_path('/images/customer_idimg/'),$idcard_front);
                $idcard_front = '/images/customer_idimg/'.$idcard_front;
            }
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

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 存储条目 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.store';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断2');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $newlog->action = '管理员' . $myself->username . ' 更新客户数据';
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.store';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断4');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $log->action = '管理员'. $myself->username. '冻结用户' .$customer->realname;
                $log->ip = $request->ip();
                $log->route = 'customer.destroy';
                $input = $request->all();
                $input_json = json_encode( $input );
                $log->parameters = $input_json;  // 请求参数
                $log->created_at = date('Y-m-d H:i:s');

                if(!$log->save())
                    throw new \Exception('事务中断6');

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                //echo $e->getMessage();
                return '修改错误，事务回滚';
            }

            return redirect()->route('customer.index');
    }

    public function customer_search(Request $request)
    {
        $fid = $request->fid;
        $phone = $request->phone;

        $date_string = $request->created_at;
        $date_parts = explode('至', $date_string);
        $start_date = trim($date_parts[0]);
        $end_date = trim($date_parts[1]);
        if($fid !=null && $phone != null && $date_string!=null)
        {
            $search_customer = DB::table('customers')
                            ->whereBetween('customers.created_at', [$start_date, $end_date])
                            ->where([['customers.id','=',$fid],['customers.phone','=',$phone],['customers.status','=',1]])
                            ->orderBy('customers.created_at','desc')
                            ->select('customers.*')
                            ->get();

        }else{
            $search_customer = DB::table('customers')
                                ->where('customers.status',1)
                                ->whereBetween('customers.created_at', [$start_date, $end_date])
                                ->orwhere([['customers.id','=',$fid],['customers.status',1]])
                                ->orwhere([['customers.phone','=',$phone],['customers.status',1]])
                                ->orderBy('customers.created_at','desc')
                                ->select('customers.*')
                                ->get();

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
                $newlog->action = '管理员' . Auth::user()->username . '对用户' . $one->phone . ' 踢出';
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.kick';
                $newlog->parameters = json_encode($request->all());
                $newlog->created_at = date('Y-m-d H:i:s');
                if (!$newlog->save())
                    throw new \Exception('事务中断8');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $newlog->action = '管理员' . $username . ' 存儲密碼1 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.password1';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断8');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                'password2' => 'required|confirmed',
                'password2_confirmation' => 'required|same:password2',
                'password2' => ['regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
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
                $newlog->action = '管理员' . $username . ' 存儲密碼2 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'customer.password2';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断10');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的余额上分" . $amount;
                $balance = $customer->balance + $amount;

                $financial_balance = new FinancialBalance();
                $financial_balance->userid = $customer->id;
                $financial_balance->amount = $amount;
                $financial_balance->balance = $customer->balance;
                $financial_balance->direction = 1;
                $financial_balance->financial_type = 5;
                $financial_balance->details = $detail;
                $financial_balance->after_balance = $balance;
                $financial_balance->created_at = date('Y-m-d H:i:s');
                if(!$financial_balance->save())
                throw new \Exception('事务中断11');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断11');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = $detail;
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_balance';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断12');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的资产上分" .  $amount;
                $balance = $customer->balance + $amount;
                $financial_asset = new FinancialAsset();
                $financial_asset->userid = $customer->id;
                $financial_asset->amount = $amount;
                $financial_asset->balance = $customer->balance;
                $financial_asset->direction = 1;
                $financial_asset->financial_type = 5;
                $financial_asset->details = $detail;
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
                $newlog->action = '管理员' . $username . ' 儲存金融資產 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_asset';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断14');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的积分上分" .  $amount;
                $balance = $customer->balance + $amount;
                $financial_integration = new FinancialIntegration();
                $financial_integration->userid = $customer->id;
                $financial_integration->amount = $amount;
                $financial_integration->balance = $customer->balance;
                $financial_integration->direction = 1;
                $financial_integration->financial_type = 5;
                $financial_integration->details = $detail;
                $financial_integration->after_balance = $balance;
                $financial_integration->created_at = date('Y-m-d H:i:s');
                if(!$financial_integration->save())
                throw new \Exception('事务中断15');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断15');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 門店財務整合 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_integration';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断16');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail = "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的平台币上分" .  $amount;
                $balance = $customer->balance + $amount;
                $financial_platform_coin = new FinancialPlatformCoin();
                $financial_platform_coin->userid = $customer->id;
                $financial_platform_coin->amount = $amount;
                $financial_platform_coin->balance = $customer->balance;
                $financial_platform_coin->direction = 1;
                $financial_platform_coin->financial_type = 5;
                $financial_platform_coin->details = $detail;
                $financial_platform_coin->after_balance = $balance;
                $financial_platform_coin->created_at = date('Y-m-d H:i:s');
                if(!$financial_platform_coin->save())
                throw new \Exception('事务中断17');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断17');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 存儲金融平台幣 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.financial_platform_coin';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断18');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail =   "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的余额上下分" .  $amount;
                $balance = $customer->balance - $amount;
                $financial_balance = new FinancialBalance();
                $financial_balance->userid = $customer->id;
                $financial_balance->amount = $amount;
                $financial_balance->balance = $customer->balance;
                $financial_balance->direction = -1;
                $financial_balance->financial_type = 6;
                $financial_balance->details = $detail;
                $financial_balance->after_balance = $balance;
                $financial_balance->created_at = date('Y-m-d H:i:s');
                if(!$financial_balance->save())
                throw new \Exception('事务中断19');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断19');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 存儲財務餘額 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_balance';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断20');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail =     "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的资产下分" .  $amount;
                $balance = $customer->balance - $amount;
                $financial_asset = new FinancialAsset();
                $financial_asset->userid = $customer->id;
                $financial_asset->amount = $amount;
                $financial_asset->balance = $customer->balance;
                $financial_asset->direction = -1;
                $financial_asset->financial_type = 6;
                $financial_asset->details = $detail;
                $financial_asset->after_balance = $balance;
                $financial_asset->created_at = date('Y-m-d H:i:s');
                if(!$financial_asset->save())
                throw new \Exception('事务中断21');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断21');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 存儲財務餘額 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_asset';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断22');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
                return $errorMessage;
                //return '删除错误，事务回滚';
            }
            return redirect()->route('customer.index');
    }

    //商店撤回財務整合
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
                $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的积分下分" .  $amount;
                $balance = $customer->balance - $amount;

                $financial_integration = new FinancialIntegration();
                $financial_integration->userid = $customer->id;
                $financial_integration->amount = $amount;
                $financial_integration->balance = $customer->balance;
                $financial_integration->direction = -1;
                $financial_integration->financial_type = 6;
                $financial_integration->details = $detail;
                $financial_integration->after_balance = $balance;
                $financial_integration->created_at = date('Y-m-d H:i:s');
                if(!$financial_integration->save())
                throw new \Exception('事务中断23');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断23');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 商店撤回財務整合 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_integration';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断24');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
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
                $detail =  "管理员:" . Auth::user()->username . "为客户"  .  $customer->phone .  "的平台币下分" .  $amount;
                $balance = $customer->balance - $amount;

                $financial_platform_coin = new FinancialPlatformCoin();
                $financial_platform_coin->userid = $customer->id;
                $financial_platform_coin->amount = $amount;
                $financial_platform_coin->balance = $customer->balance;
                $financial_platform_coin->direction = -1;
                $financial_platform_coin->financial_type = 6;
                $financial_platform_coin->details = $detail;
                $financial_platform_coin->after_balance = $balance;
                $financial_platform_coin->created_at = date('Y-m-d H:i:s');
                if(!$financial_platform_coin->save())
                throw new \Exception('事务中断25');

                $customer->balance = $balance;
                if(!$customer->save())
                throw new \Exception('事务中断25');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . ' 商店取款金融平台幣 ';
                $newlog->ip = $request->ip();
                $newlog->route = 'withdraw.financial_platform_coin';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断26');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                * $errorCode = $e->getCode();
                * $stackTrace = $e->getTraceAsString();
                */
                $errorMessage = $e->getMessage();
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

        return view('customer.team',  compact('members', 'one_team', 'one_team_extra', 'count_children'));
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

        return view('customer.team_search',  compact('members', 'one_team', 'one_team_extra', 'count_children'));
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
}
