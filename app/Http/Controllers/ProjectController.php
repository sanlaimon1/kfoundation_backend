<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Level;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectCate;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ProjectController extends Controller
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
    private $path_name = "/project";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 项目列表
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $projects = Project::where('enable', 1)->orderBy('created_at', 'desc')->paginate($perPage);

        //项目类型
        $cates = ProjectCate::select('id','cate_name')->where('enable',1)->orderBy('sort', 'desc')->get();

        $types = [];
        foreach($cates as $one_cat) {
            $types[ $one_cat->id ] = $one_cat->cate_name;
        }

        return view('project.index', compact('projects', 'types'));
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

        //项目类型
        $cates = ProjectCate::select('id','cate_name')->where('enable',1)->orderBy('sort', 'desc')->get();

        $types = [];
        foreach($cates as $one_cat) {
            $types[ $one_cat->id ] = $one_cat->cate_name;
        }

        $levels = Level::all();
        $return_modes = config('types.return_mode');

        return view('project.create', compact('types','levels','return_modes'));
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
            "project_name" => ['required', 'string', 'max:45'],
            "cate_name" =>  ['required', 'integer', 'exists:project_categories,id'],
            "guarantee" => ['required', 'string', 'max:45'],
            "risk" => ['required', 'string', 'max:45'],
            "usage" => ['required', 'string', 'max:45'],
            "return_mode" => ['required', 'integer'],
            "amount" => "required",
            "is_given" => ['required', 'integer', 'in:0,1'],
            "team_rate" => "required",
            "like_rate" => "required",
            "benefit_rate" => "required",
            "fake_process" => "required",
            "days" => "required",
            "min_invest" => "required",
            "max_invest" => "required",
            "max_time" => "required",
            "desc" => ['required', 'string', 'max:100'],
            "is_homepage" => ['required', 'integer', 'in:0,1'],
            "is_recommend" => ['required', 'integer', 'in:0,1'],
            "level_id" => ['required', 'integer', 'exists:levels,level_id'],
            "litpic.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            "detail" => ['required','string'],
            "project_scale" => "required"
        ]);
        if($request->hasFile('litpic')){
            $litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/project_imgs/'),$litpic);
            $litpic = '/images/project_imgs/'.$litpic;
        }
        $detail = trim( htmlspecialchars( $request->detail ));
        DB::beginTransaction();
        try {

            $project = new Project();
            $project->project_name  = $request->project_name;
            $project->cid  = $request->cate_name;
            $project->guarantee  = $request->guarantee;
            $project->risk  = $request->risk;
            $project->usage  = $request->usage;
            $project->return_mode  = $request->return_mode;
            $project->amount  = $request->amount;
            $project->is_given  = $request->is_given;
            $project->team_rate  = $request->team_rate;
            $project->like_rate = $request->like_rate;
            $project->benefit_rate  = $request->benefit_rate;
            $project->fake_process  = $request->fake_process;

            if($request->return_mode == 3){

                $project->weeks  = $request->days;

            } else if($request->return_mode == 4){

                $project->months  = $request->days;

            } else {

                $project->days  = $request->days;

            }
             
            $project->min_invest  = $request->min_invest;
            $project->max_invest  = $request->max_invest;
            $project->max_times  = $request->max_time;
            $project->desc  = $request->desc;
            $project->is_homepage  = $request->is_homepage;
            $project->is_recommend  = $request->is_recommend;
            $project->level_id  = $request->level_id;
            $project->litpic = $litpic;
            $project->details = $detail;
            $project->project_scale = $request->project_scale;
            if(!$project->save())
            throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . ' 存储条目 ';
            $newlog->ip = $request->ip();
            $newlog->route = 'project.store';
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
        return redirect()->route('project.index');

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

        $project = Project::find($id);
        $return_modes = config('types.return_mode');
        return view('project.show', compact('project','return_modes'));
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

        $project = Project::find($id);

        //项目类型
        $cates = ProjectCate::select('id','cate_name')->where('enable',1)->orderBy('sort', 'desc')->get();

        $types = [];
        foreach($cates as $one_cat) {
            $types[ $one_cat->id ] = $one_cat->cate_name;
        }
        $levels = Level::all();
        $return_modes = config('types.return_mode');

        return view('project.edit', compact('project', 'types','levels','return_modes'));
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
            "project_name" => ['required', 'string', 'max:45'],
            "cate_name" =>  ['required', 'integer', 'exists:project_categories,id'],
            "guarantee" => ['required', 'string', 'max:45'],
            "risk" => ['required', 'string', 'max:45'],
            "usage" => ['required', 'string', 'max:45'],
            "amount" => "required",
            "is_given" => ['required', 'integer', 'in:0,1'],
            "team_rate" => "required",
            "like_rate" => "required",
            "benefit_rate" => "required",
            "fake_process" => "required",
            "min_invest" => "required",
            "max_invest" => "required",
            "max_time" => "required",
            "desc" => ['required', 'string', 'max:100'],
            "is_homepage" => ['required', 'integer', 'in:0,1'],
            "is_recommend" => ['required', 'integer', 'in:0,1'],
            "level_id" => ['required', 'integer', 'exists:levels,level_id'],
            "litpic.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            "detail" => ['required','string'],
            "project_scale" => "required"
        ]);

        if($request->hasFile('litpic')){
            $litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/project_imgs/'),$litpic);
            $litpic = '/images/project_imgs/'.$litpic;
        }else{
            $litpic = $request->old_liptic;
        }
        $detail = trim( htmlspecialchars( $request->detail ));
        DB::beginTransaction();
        try {

            $project = Project::find($id);
            $project->project_name  = $request->project_name;
            $project->cid  = $request->cate_name;
            $project->guarantee  = $request->guarantee;
            $project->risk  = $request->risk;
            $project->usage  = $request->usage;
            $project->amount  = $request->amount;
            $project->is_given  = $request->is_given;
            $project->team_rate  = $request->team_rate;
            $project->like_rate = $request->like_rate;
            $project->benefit_rate  = $request->benefit_rate;
            $project->fake_process  = $request->fake_process;
            $project->min_invest  = $request->min_invest;
            $project->max_invest  = $request->max_invest;
            $project->max_times  = $request->max_time;
            $project->desc  = $request->desc;
            $project->is_homepage  = $request->is_homepage;
            $project->is_recommend  = $request->is_recommend;
            $project->level_id  = $request->level_id;
            $project->litpic = $litpic;
            $project->details = $detail;
            $project->project_scale = $request->project_scale;
            $project->save();
            if(!$project->save())
            throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . ' 存储条目 ';
            $newlog->ip = $request->ip();
            $newlog->route = 'project.store';
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
        return redirect()->route('project.index');
    }

    /**
     * not to delete really ,   set enable=0
     * and with transcation
     */
    public function destroy(Request $request, string $id)
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
        $one = Project::find($id);
        if(empty($one))
            return '项目不存在';

        DB::beginTransaction();
        try {
            $one->enable = 0;
            if(!$one->save())
                    throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . ' 删除项目 ' . $id . ' ' . $one->project_name;
            $newlog->ip = $request->ip();
            $newlog->route = 'project.destroy';
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

        return redirect()->route('project.index');
    }

    public function project_search(Request $request)
    {
        $search_projects = Project::where('enable', 1)->where('project_name','like', '%' . $request->project_name . '%')
                                ->where('cid',$request->cate_type)
                                ->orderBy('created_at', 'desc')
                                ->get()->load('projectcate');
        return response()->json($search_projects);
    }
}
