<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class RoleController extends Controller
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
    private $path_name = "/role";


    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

   
    /**
     * 列出所有角色 list all of roles
     */
    public function index()
    {
        // $path_name = "/" . $request->path();
        // $role_id = Auth::user()->rid;        
        // $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        // if( !($permission->auth2 & 1) ){
        //     return "您没有权限访问这个路径";
        // }


        $roles = Role::where('status', 1)->orderBy('sort', 'asc')->paginate(10);

        return view('role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !($permission->auth2 & 2) ){
            return "您没有权限访问这个路径";
        }

        //一级栏目
        $first_menus = config('data.main_menu');

        return view('role.create', compact('first_menus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !($permission->auth2 & 4) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'title' => ['required', 'string'],
            'status' => ['required', 'integer'],
            'soft' => ['required', 'integer', 'gt:0'],
            // // 'description' => ['required', 'string'],
            // 'auth' => ['required', 'integer', 'gt:0'],
            // 'auth2' => ['required', 'integer', 'gt:0'],
        ]);

        DB::beginTransaction();
        try {

            $role = new Role();
            $role->title = $request->title;
            $role->status = $request->status;
            $role->sort = $request->soft;
            $role->desc = $request->description;
            // $role->auth = $request->auth;
            // $role->auth2 = $request->auth2;

            if (!$role->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = "127.0.0.1";
            $newlog->route = 'role.store';
            $newlog->parameters = json_encode($request->all());
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            return '添加错误，事务回滚';
        }

        return redirect(route("role.index"));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {


        $first_menus = config('data.main_menu');    //一级菜单
        $id = (int)$id;
        $all_datas = config('data');

        $items = [];

        foreach($first_menus as $urlpath=>$item_name ) {
            if(array_key_exists($urlpath, $all_datas)) {
                $items[ $urlpath ] = ['main_name'=>$item_name, 'sub_menu'=>$all_datas[ $urlpath ]];
            }
        }

        return view('role.show', compact('first_menus', 'id', 'items') );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !($permission->auth2 & 16) ){
            return "您没有权限访问这个路径";
        }

        $role = Role::findOrFail($id);
        return view('role.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !($permission->auth2 & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'title' => ['required', 'string'],
            'status' => ['required', 'integer'],
            'soft' => ['required', 'integer', 'gt:0'],
            // // 'description' => ['required', 'string'],
            // 'auth' => ['required', 'integer', 'gt:0'],
            // 'auth2' => ['required', 'integer', 'gt:0'],
        ]);

        DB::beginTransaction();
        try {

            $role = Role::find($id);
            $role->title = $request->title;
            $role->status = $request->status;
            $role->sort = $request->soft;
            $role->desc = $request->description;
            // $role->auth = $request->auth;
            // $role->auth2 = $request->auth2;

            if (!$role->update())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = "127.0.0.1";
            $newlog->route = 'role.update';
            $newlog->parameters = json_encode($request->all());
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            return '添加错误，事务回滚';
        }

        return redirect(route("role.index"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !($permission->auth2 & 64) ){
            return "您没有权限访问这个路径";
        }

        DB::beginTransaction();
        try {

            $id = (int)$id;
            $one = Role::find($id);
            $one->status = 0;

            if (!$one->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = "127.0.0.1";
            $newlog->route = 'role.delete';
            $newlog->parameters = "delete parameter";
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            return '添加错误，事务回滚';
        }

        return redirect()->route('role.index');
    }

    /**
     * list menu of uri   列出uri
     * roles.listuri
     */
    public function listuri(string $key)
    {
        $datas = config('data');
        //判断key是否存在
        if (!array_key_exists($key, $datas)) {
            $arr = ['code' => -10, 'message' => $key . '不存在'];
            return response()->json($arr);
        }

        $items = $datas[$key];
        $arr = ['code' => 1, 'message' => '请求成功', 'datas' => $items];
        return response()->json($arr);
    }
}
