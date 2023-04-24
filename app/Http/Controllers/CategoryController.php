<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class CategoryController extends Controller
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
    private $path_name = "/category";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $categories = Category::where('enable', 1)->orderBy('sort', 'asc')->paginate($perPage);
        return view('category/index', compact('categories'));
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

        return view('category/create');
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

        //表单验证
        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'sort' => ['required', 'integer', 'gt:0'],
            'lang' => 'required'
        ]);

        $category_name = trim($request->cate_name);
        $lang = trim($request->get('lang'));
        $sort = trim($request->sort);

        $sort = (int)$sort;

        DB::beginTransaction();
        try {
            //code...
            $newcategory = new Category();
            $newcategory->cate_name = $category_name;
            $newcategory->sort = $sort;
            $newcategory->lang = $lang;

            if(!$newcategory->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $store_action = ['username' => $username, 'type' => 'log.store_action'];
            $action = json_encode($store_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'category.store';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');
            $category_data = array(
                'id' => $newcategory->id,
                'cate_name' => $newcategory->cate_name,
                'sort' => $newcategory->sort,
                'lang' => $newcategory->lang

            );
            $category_json = json_encode($category_data);
            DB::commit();
            LogFile::channel("category_store")->info($category_json);

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("category_store_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('category.index');
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
     */
    public function edit(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 16) ){
            return "您没有权限访问这个路径";
        }

        $category = Category::find($id);
        return view('category.edit', compact('category'));
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
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'sort' => ['required', 'integer', 'gt:0'],
            'lang' => 'required'
        ]);

        $category_name = trim($request->cate_name);
        $lang = trim($request->get('lang'));
        $sort = trim($request->sort);

        $sort = (int)$sort;

        DB::beginTransaction();
        try {
            //code...
            $newcategory = Category::find($id);
            $newcategory->cate_name = $category_name;
            $newcategory->sort = $sort;
            $newcategory->lang  = $lang;
            if(!$newcategory->save())
                throw new \Exception('事务中断3');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $update_action = ['username' => $username, 'type' => 'log.update_action'];
            $action = json_encode($update_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'category.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断4');

            $category_data = array(
                'id' => $newcategory->id,
                'cate_name' => $newcategory->cate_name,
                'sort' => $newcategory->sort,
                'lang' => $newcategory->lang

            );
            $category_json = json_encode($category_data);
            DB::commit();
            LogFile::channel("category_update")->info($category_json);

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("category_update_error")->error($message);
            return '添加错误，事务回滚';
        }
        return redirect()->route('category.index');
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

        DB::beginTransaction();
        try {
            $id = (int)$id;
            $category = Category::find($id);
            $category->enable = 0;
            if(!$category->save())
                throw new \Exception('事务中断5');

            $category_data = array(
                'id' => $category->id,
                'cate_name' => $category->cate_name,
                'sort' => $category->sort,
                'enable' => $category->enable
            );
            $category_json = json_encode($category_data);

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $delete_action = ['username' => $username, 'type' => 'log.delete_action'];
            $action = json_encode($delete_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'category.destroy';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断6');

            DB::commit();
            LogFile::channel("category_destroy")->info("文章分类 刪除成功");

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("category_destroy_error")->error($message);
            return '添加错误，事务回滚';
        }
        return redirect()->route('category.index');
    }
}
