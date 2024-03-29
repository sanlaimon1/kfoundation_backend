<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class SysUsersController extends Controller
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
    private $path_name = "/sysusers";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 系统管理员 System Users
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        //列出管理员
        $sysusers = Admin::select('id','username','status','create_at','desc','rid','login_at')
            ->where('is_deleted', 0)
            ->orderBy('create_at', 'desc')
            ->paginate($perPage);

        return view('sysusers.index', compact('sysusers') );
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

        //查询所有角色
        $role_items = Role::select('rid','title')->where('status', 1)->orderBy('sort','asc')->get();
        $roles = [];
        foreach( $role_items as $one_role) {
            $roles[ $one_role->rid ] = $one_role->title;
        }

        return view('sysusers.create', compact('roles'));
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

        $rules = [
            'username' => ['required', 'string', 'between:6,10', 'unique:admins'],
            'desc' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'between:8,15', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
            'cpassword' => ['required', 'string', 'between:8,15', 'same:password'],
            'status' => ['required', 'integer', 'in:0,1'],
            'rid' => ['required', 'integer', 'exists:roles,rid'],
        ];

        $messages = [
            'password.regex' => '密码必须必须是字母和数字的组合。',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $username = trim($request->get('username'));
        $desc = trim($request->get('desc'));
        $password = trim($request->get('password'));
        $status = trim($request->get('status'));
        $rid = trim($request->get('rid'));

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $salt = substr(str_shuffle($chars), 0, 6);      //6位随机盐
        //构建密码
        $saltpassword = md5( md5( $salt . $password ) . $salt );

        DB::beginTransaction();
        try {
            $newadmin = new Admin;
            $newadmin->username = $username;
            $newadmin->desc = $desc;
            $newadmin->password = $saltpassword;
            $newadmin->create_at = date('Y-m-d H:i:s');
            $newadmin->salt = $salt;
            $newadmin->save();

            if(!$newadmin->save())
                throw new \Exception('事务中断1');

            $newadmins = array(
                'id' => $newadmin->id,
                'username' => $newadmin->username,
                'desc' => $newadmin->desc,
                'password' => $newadmin->password,
                'created_at' => $newadmin->created_at,
                'salt' => $newadmin->salt,
            );

            $newadmin_json = json_encode($newadmins);

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $store_action = ['username' => $username, 'type' => 'log.store_action'];
            $action = json_encode($store_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'sysusers.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("sysuser_store")->info($newadmin_json);

        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("sysuser_store_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('sysusers.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * edit a manager  编辑一个管理员
     */
    public function edit(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 16) ){
            return "您没有权限访问这个路径";
        }

        $id = (int)$id;
        $one = Admin::find($id);
        //查询所有角色
        $role_items = Role::select('rid','title')->where('status', 1)->orderBy('sort','asc')->get();
        $roles = [];
        foreach( $role_items as $one_role) {
            $roles[ $one_role->rid ] = $one_role->title;
        }
        return view( 'sysusers.edit', compact('one','id','roles') );
    }

    /**
     * Show the form for editing the specified resource.
     * modify the password ,  修改密码
     */
    public function modify_pass(string $id)
    {
        $id = (int)$id;
        $one = Admin::find($id);

        return view( 'sysusers.pass', compact('one','id') );
    }

    /**
     * 修改逻辑  modify a manager
     */
    public function update(Request $request, string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $rules = [
            'desc' => ['required', 'string', 'max:100'],
            'status' => ['required', 'integer', 'in:0,1'],
            'rid' => ['required', 'integer', 'exists:roles,rid'],
        ];

        $messages = [
            'status.in' => '状态的数值错误',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $desc = trim( $request->get('desc') );
        $status = trim( $request->get('status') );
        $rid = trim( $request->get('rid') );

        $id = (int)$id;

        $one = Admin::find($id);
        if(empty($one))
            return '用户不存在';

        DB::beginTransaction();
        try {
            $one->desc = $desc;
            $one->status = $status;
            $one->rid = $rid;

            if(!$one->save())
                throw new \Exception('事务中断3');

            $admin = array(
                'id' => $one->id,
                'desc' => $one->desc,
                'status' => $one->status,
                'rid' => $one->rid,
            );

            $admin_json = json_encode($admin);

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $update_action = ['username' => $username, 'type' => 'log.update_action'];
            $action = json_encode($update_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'sysusers.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断4');

            DB::commit();
            LogFile::channel("sysuser_update")->info($admin_json);
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("sysuser_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('sysusers.edit',['sysuser'=>$id])->with('message', '用户 ' . $one->username . ' 修改成功！');
    }

    /**
     * 修改逻辑  modify a manager
     */
    public function update_pass(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $rules = [
            'password' => ['required', 'string', 'between:8,15', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
            'cpassword' => ['required', 'string', 'between:8,15', 'same:password'],
        ];

        $messages = [
            'password.regex' => '密码必须必须是字母和数字的组合。',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $password = trim( $request->get('password') );
        $id = trim( $request->get('id') );

        $id = (int)$id;
        $one = Admin::find($id);
        if(!empty($one))
        {
            DB::beginTransaction();
            try {
                $salt = $one->salt;
                $saltpassword = md5( md5( $salt . $password ) . $salt );
                $one->password = $saltpassword;

                if(!$one->save())
                    throw new \Exception('事务中断5');

                $password = array(
                    'id' => $one->id,
                    'salt' => $one->salt,
                    'password' => $one->password,
                );

                $password_json = json_encode($password);

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $update_action = ['username' => $username, 'type' => 'log.update_pass_action'];
                $action = json_encode($update_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'sysusers.update_pass';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断6');

                DB::commit();
                LogFile::channel("sysuser_password_update")->info($password_json);
            } catch (\Exception $e) {
                DB::rollback();
                $message = $e->getMessage();
                LogFile::channel("sysuser_password_update_error")->error($message);
                return '添加错误，事务回滚';
            }

        } else {
            return '用户不存在';
        }

        return redirect()->route('sysusers.modifypass',['id'=>$id])->with('message', '用户 ' . $one->username . ' 的密码修改成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }

        $id = (int)$id;

        DB::beginTransaction();
        try {
            $one = Admin::find($id);
            $one->is_deleted = 1;
            if(!$one->save())
                throw new \Exception('事务中断7');

            $sysuser = array(
                'id' => $one->id,
                'is_deleted' => $one->is_deleted,
            );

            $sysuser_json = json_encode($sysuser);

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $delete_action = ['username' => $username, 'type' => 'log.delete_action'];
            $action = json_encode($delete_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'sysusers.destroy';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断8');

            DB::commit();
            LogFile::channel("sysuser_destroy")->info($sysuser_json);
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("sysuser_destroy_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('sysusers.index');
    }
}
