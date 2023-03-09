<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Level;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use App\Models\Permission;
use Illuminate\Support\Facades\Redis;
use App\Models\Teamlevel;

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
    public function index()
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $records = Customer::where('status',1)
                  ->orderBy('created_at', 'desc')->paginate(20);

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
            "password1" => 'required|confirmed', 
            "password2" => 'required|confirmed', 
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
        return view('customer.show',compact('customer', 'customer_identity'));
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
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            "is_allowed_code" => ['required', 'integer', 'in:0,1'],
            "identity" => ['required', 'integer', 'in:0,1,2'],
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
            $customer = Customer::find($id);
            $customer->realname = $request->realname;
            $customer->is_allowed_code = $request->is_allowed_code;
            $customer->identity = $request->identity;
            $customer->is_sure = $request->is_sure;
            $customer->level_id = $request->level_id;
            $customer->team_id = $request->team_id;
            $customer->idcard_front = $idcard_front;
            $customer->idcard_back = $idcard_back;
            $customer->updated_at = date('Y-m-d H:i:s');
            if(!$customer->save())
            throw new \Exception('事务中断1');

            $myself = Auth::user();
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $myself->username . ' 更新客户数据';
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
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
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
        $created_at = $request->created_at;
        if($fid !=null && $phone != null && $created_at!=null)
        {
            $search_customer = DB::table('customers')
                            ->whereDate('customers.created_at','=',$created_at)
                            ->where([['customers.id','=',$fid],['customers.phone','=',$phone],['customers.status','=',1]])
                            ->orderBy('customers.created_at','desc')
                            ->select('customers.*')
                            ->get();

        }else{
            $search_customer = DB::table('customers')
                                ->where('customers.status',1)
                                ->whereDate('customers.created_at','=',$created_at)
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
    public function kick(Request $request, string $id) {

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

    //上分
    public function charge(string $id) {
        $id = (int)$id;
        $one = Customer::find($id);

        return view('customer.charge', compact('id', 'one'));
    }

    //下分
    public function withdrawal(string $id) {
        $id = (int)$id;
        $one = Customer::find($id);

        return view('customer.withdrawal', compact('id', 'one'));
    }

    //修改密码
    public function modify_pass(string $id) {
        $id = (int)$id;
        $one = Customer::find($id);

        return view('customer.password', compact('id', 'one'));
    }

}
