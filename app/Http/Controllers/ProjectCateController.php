<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\ProjectCate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class ProjectCateController extends Controller
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
    private $path_name = "/projectcate";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 项目分类
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }
        $perPage = $request->input('perPage', 10);
        $projectcates = ProjectCate::where('enable',1)
                                    ->orderBy('sort', 'asc')
                                    ->paginate($perPage);

        return view('projectcate.index', compact('projectcates'));
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

        return view('projectcate/create');
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
            'cate_name' => ['required', 'string', 'between:1,40'],
            'comment' => ['required','string','max:200'],
            'sort' => ['required', 'integer', 'gte:0'],
            'lang' => 'required'
        ]);

        $category_name = trim($request->cate_name);
        $comment = trim($request->comment);
        $sort = trim($request->sort);
        $lang = trim($request->lang);
        DB::beginTransaction();
        try {
            $newprojectcates = new ProjectCate();
            $newprojectcates->cate_name = $category_name;
            $newprojectcates->comment = $comment;
            $newprojectcates->created_at = date('Y-m-d H:i:s');
            $newprojectcates->sort = $sort;
            $newprojectcates->lang = $lang;
            if(!$newprojectcates->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $store_action = ['username' => $username, 'type' => 'log.store_action'];
            $action = json_encode($store_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'projectcate.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            $projectcates =array([
                'id' => $newprojectcates->id,
                'cate_name' => $newprojectcates->cate_name,
                'created_at' => $newprojectcates->created_at,
                'sort' => $newprojectcates->sort,
                'comment' => $newprojectcates->comment,
                'lang' => $newprojectcates->lang
            ]);
            $projectcate_json = json_encode($projectcates);
            LogFile::channel("projectcate_store")->info($projectcate_json);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("projectcate_store_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('projectcate.index');

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

        $oneprojectcate = ProjectCate::find($id);
        return view('projectcate.edit', compact('oneprojectcate'));
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
            'comment' => ['required','string','max:200'],
            'sort' => ['required', 'integer', 'gte:0'],
            'lang' => 'required'
        ]);

        $category_name = trim($request->cate_name);
        $comment = trim($request->comment);
        $sort = trim($request->sort);
        $lang = trim($request->lang);

        DB::beginTransaction();
        try {
            $oneprojectcate = ProjectCate::find($id);
            $oneprojectcate->cate_name = $category_name;
            $oneprojectcate->comment = $comment;
            $oneprojectcate->sort = $sort;
            $oneprojectcate->lang = $lang;
            if(!$oneprojectcate->save())
                throw new \Exception('事务中断3');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $update_action = ['username' => $username, 'type' => 'log.update_action'];
            $action = json_encode($update_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'projectcate.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断4');

            $projectcates =array([
                'id' => $oneprojectcate->id,
                'cate_name' => $oneprojectcate->cate_name,
                'created_at' => $oneprojectcate->created_at,
                'sort' => $oneprojectcate->sort,
                'comment' => $oneprojectcate->comment,
                'lang' => $oneprojectcate->lang
            ]);

            $projectcate_json = json_encode($projectcates);
            LogFile::channel("projectcate_update")->info($projectcate_json);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("projectcate_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('projectcate.index');

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
            $one = ProjectCate::find($id);
            $one->enable = 0;

            if(!$one->save())
                throw new \Exception('事务中断5');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $delete_action = ['username' => $username, 'type' => 'log.delete_action'];
            $action = json_encode($delete_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'projectcate.destroy';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断6');

            $projectcates =array([
                'id' => $one->id,
                'cate_name' => $one->cate_name,
                'created_at' => $one->created_at,
                'enable' => $one->enable,
            ]);

            $projectcate_json = json_encode($projectcates);
            LogFile::channel("projectcate_destroy")->info($projectcate_json);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("projectcate_destroy_error")->error($message);
            return '添加错误，事务回滚';
        }
        return redirect()->route('projectcate.index');
    }
}
