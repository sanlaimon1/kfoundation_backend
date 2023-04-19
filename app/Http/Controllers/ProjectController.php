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
use Illuminate\Support\Facades\Log as LogFile;

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
        $projects = Project::where('enable', 1)->orderBy('sort', 'asc')->paginate($perPage);

        //项目类型
        $cates = ProjectCate::select('id','cate_name')->where('enable',1)->orderBy('sort', 'desc')->get();

        $types = [];
        foreach($cates as $one_cat) {
            $types[ $one_cat->id ] = $one_cat->cate_name;
        }

        $array_project = [];
        if (!Redis::exists("project:homepage:md5")) {
            $project = Project::select('id', 'project_name', 'return_mode', 'days', 'weeks', 'months')
                                ->where('is_homepage', 1)->where('enable', 1)
                                ->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->limit(6)->get();
            foreach ($project as $one) {
                $data['id'] = $one->id;
                $data['project_name'] = $one->project_name;
                $data['return_mode'] = $one->return_mode;
                $data['days'] = $one->days;
                $data['weeks'] = $one->weeks;
                $data['months'] = $one->months;
                array_push($array_project, $data);
            };
            $redis_project = json_encode($array_project);
            Redis::set( "project:homepage:string", $redis_project );
            Redis::set( "project:homepage:md5", md5($redis_project) );
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
            "team_rate" => ['required', 'numeric', 'gte:0'],
            "like_rate" => ['required', 'numeric', 'gte:0'],
            "benefit_rate" => ['required', 'numeric', 'gte:0'],
            "fake_process" => ['required', 'numeric', 'gte:0'],
            "days" => ['required', 'integer', 'gt:0'],
            "min_invest" => ['required', 'numeric', 'gte:0'],
            "max_invest" => ['required', 'numeric', 'gte:0'],
            "max_time" => ['required', 'numeric', 'gte:0'],
            "desc" => ['required', 'string', 'max:100'],
            "is_homepage" => ['required', 'integer', 'in:0,1'],
            "is_recommend" => ['required', 'integer', 'in:0,1'],
            "level_id" => ['required', 'integer', 'exists:levels,level_id'],
            "litpic.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            "detail" => ['required','string'],
            "project_scale" => ['required', 'numeric', 'gte:0'],
            "status"  =>  "required|in:0,1",
            "sort" => "required|integer|gte:0",
            "lang" => "required"
        ]);

        if($request->hasFile('litpic')){
            $litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/project_imgs/'),$litpic);
            $litpic = '/images/project_imgs/'.$litpic;
        } else {
            $litpic = '';
        }
        $detail = trim( htmlspecialchars( $request->detail ));
        $lang = trim($request->get('lang'));
        $status = (int)$request->status;
        $sort = (int)$request->sort;
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
            $project->status = $status;
            $project->sort = $sort;
            $project->lang = $lang;
            if(!$project->save())
            throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . ' 添加项目:' . $project->project_name;
            $newlog->ip = $request->ip();
            $newlog->route = 'project.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            $projects = array([
                'id' => $project->id,
                'project_name' => $project->project_name,
                'cid' => $project->cid,
                'guarantee' => $project->guarantee,
                'risk' => $project->risk,
                'usage' => $project->usage,
                'status' => $project->status,
                'return_mode' => $project->return_mode,
                'amount' => $project->amount,
                'is_given' => $project->is_given,
                'team_rate' => $project->team_rate,
                'like_rate' => $project->like_rate,
                'project_scale' => $project->project_scale,
                'benefit_rate' => $project->benefit_rate,
                'fake_process' => $project->fake_process,
                'days' => $project->days,
                'min_invest' => $project->min_invest,
                'max_invest' => $project->max_invest,
                'desc' => $project->desc,
                'is_homepage' => $project->is_homepage,
                'is_recommend' => $project->is_recommend,
                'level_id' => $project->level_id,
                'litpic' => $project->litpic,
                'details' => $project->details,
                'enable' => $project->enable,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'increment_process' => $project->increment_process,
                'sort' => $project->sort,
                'weeks' => $project->weeks,
                'months' => $project->months,
                'bind_projectid' => $project->bind_projectid,
                'lang' => $project->lang,
            ]);

            $project_json = json_encode($projects);
            LogFile::channel("project_store")>info($project_json);
            DB::commit();

            $old_redis_project = Redis::get("project:homepage:md5");
            $project = Project::select('id', 'project_name', 'return_mode', 'days', 'weeks', 'months')
                                ->where('is_homepage', 1)->where('enable', 1)
                                ->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->limit(6)->get();
            $array_project = [];
            foreach ($project as $one) {
                $data['id'] = $one->id;
                $data['project_name'] = $one->project_name;
                $data['return_mode'] = $one->return_mode;
                $data['days'] = $one->days;
                $data['weeks'] = $one->weeks;
                $data['months'] = $one->months;
                array_push($array_project, $data);
            };
            $redis_project = json_encode($array_project);
            if (md5($redis_project) != $old_redis_project) {
                Redis::set( "project:homepage:string", $redis_project );
                Redis::set( "project:homepage:md5", md5($redis_project) );
            }
        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            $errorMessage = $e->getMessage();
            LogFile::channel("project_store_error")->error($errorMessage);
            return $errorMessage;
            //return '删除错误，事务回滚';
        }
        return redirect()->route('project.index');

    }

    /**
     * 显示一个项目详细信息
     * display detail of a project
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
        //绑定产品
        $bind_project_name = '没有绑定';
        $bind_projectid = $project->bind_projectid;
        if($bind_projectid!=0) {
            $bind_project = Project::find($bind_projectid);
            if(!empty($bind_project)) {
                $bind_project_name = $bind_project->project_name;
            }
        }

        return view('project.show', compact('project','return_modes', 'bind_project_name', 'bind_projectid'));
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
            "team_rate" => ['required', 'numeric', 'gte:0'],
            "like_rate" => ['required', 'numeric', 'gte:0'],
            "benefit_rate" => ['required', 'numeric', 'gte:0'],
            "fake_process" => ['required', 'numeric', 'gte:0'],
            "min_invest" => ['required', 'numeric', 'gte:0'],
            "max_invest" => ['required', 'numeric', 'gte:0'],
            "max_time" => ['required', 'numeric', 'gte:0'],
            "desc" => ['required', 'string', 'max:100'],
            "is_homepage" => ['required', 'integer', 'in:0,1'],
            "is_recommend" => ['required', 'integer', 'in:0,1'],
            "level_id" => ['required', 'integer', 'exists:levels,level_id'],
            "litpic.*" => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            "detail" => ['required','string'],
            "project_scale" => ['required', 'numeric', 'gte:0'],
            "status"  =>  "required|in:0,1",
            "sort" => "required|integer|gte:0",
            "lang" => "required"
        ]);
        $lang = trim($request->get('lang'));
        $status = (int)$request->status;
        $sort = (int)$request->sort;
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
            $project->status = $status;
            $project->sort = $sort;
            $project->lang = $lang;
            $project->save();
            if(!$project->save())
            throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action ='管理员' . $username . ' 修改项目:' . $project->project_name;
            $newlog->ip = $request->ip();
            $newlog->route = 'project.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            $projects = array([
                'id' => $project->id,
                'project_name' => $project->project_name,
                'cid' => $project->cid,
                'guarantee' => $project->guarantee,
                'risk' => $project->risk,
                'usage' => $project->usage,
                'status' => $project->status,
                'return_mode' => $project->return_mode,
                'amount' => $project->amount,
                'is_given' => $project->is_given,
                'team_rate' => $project->team_rate,
                'like_rate' => $project->like_rate,
                'project_scale' => $project->project_scale,
                'benefit_rate' => $project->benefit_rate,
                'fake_process' => $project->fake_process,
                'days' => $project->days,
                'min_invest' => $project->min_invest,
                'max_invest' => $project->max_invest,
                'desc' => $project->desc,
                'is_homepage' => $project->is_homepage,
                'is_recommend' => $project->is_recommend,
                'level_id' => $project->level_id,
                'litpic' => $project->litpic,
                'details' => $project->details,
                'enable' => $project->enable,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
                'increment_process' => $project->increment_process,
                'sort' => $project->sort,
                'weeks' => $project->weeks,
                'months' => $project->months,
                'bind_projectid' => $project->bind_projectid,
                'lang' => $project->lang,
            ]);

            $project_json = json_encode($projects);
            LogFile::channel("project_update")->info($project_json);
            DB::commit();

            $old_redis_project = Redis::get("project:homepage:md5");
            $project = Project::select('id', 'project_name', 'return_mode', 'days', 'weeks', 'months')
                                ->where('is_homepage', 1)->where('enable', 1)
                                ->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->limit(6)->get();
            $array_project = [];
            foreach ($project as $one) {
                $data['id'] = $one->id;
                $data['project_name'] = $one->project_name;
                $data['return_mode'] = $one->return_mode;
                $data['days'] = $one->days;
                $data['weeks'] = $one->weeks;
                $data['months'] = $one->months;
                array_push($array_project, $data);
            };
            $redis_project = json_encode($array_project);
            if (md5($redis_project) != $old_redis_project) {
                Redis::set( "project:homepage:string", $redis_project );
                Redis::set( "project:homepage:md5", md5($redis_project) );
            }
        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            $errorMessage = $e->getMessage();
            LogFile::channel("project_update_error")->error($errorMessage);
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
            $projects = array([
                'id' => $one->id,
                'project_name' => $one->project_name,
                'enable' => $one->enable,
                'created_at' => $one->created_at,
                'updated_at' => $one->updated_at,
            ]);
            $project_json = json_encode($projects);
            LogFile::channel("project_destroy")>info($project_json);
            DB::commit();

            $old_redis_project = Redis::get("project:homepage:md5");
            $project = Project::select('id', 'project_name', 'return_mode', 'days', 'weeks', 'months')
                                ->where('is_homepage', 1)->where('enable', 1)
                                ->orderBy('sort', 'asc')->orderBy('created_at', 'desc')->limit(6)->get();
            $array_project = [];
            foreach ($project as $one) {
                $data['id'] = $one->id;
                $data['project_name'] = $one->project_name;
                $data['return_mode'] = $one->return_mode;
                $data['days'] = $one->days;
                $data['weeks'] = $one->weeks;
                $data['months'] = $one->months;
                array_push($array_project, $data);
            };
            $redis_project = json_encode($array_project);

            if (md5($redis_project) != $old_redis_project) {
                Redis::set( "project:homepage:string", $redis_project );
                Redis::set( "project:homepage:md5", md5($redis_project) );
            }

        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            $errorMessage = $e->getMessage();
            LogFile::channel("project_destroy_error")->error($errorMessage);
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

    /**
     * 编辑绑定产品
     */
    public function editBindProject(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 16) ){
            return "您没有权限访问这个路径";
        }

        $project = Project::find($id);

        //查询项目列表
        $selected_projects = Project::select(['id','project_name'])
                                    ->where('enable', 1)
                                    ->where('status', 1)
                                    ->where('id', '!=', $id)
                                    ->get();
        $select_array = [];
        $select_array[0] = '未绑定';
        foreach( $selected_projects as $one) {
            $select_array[$one->id] = $one->project_name;
        }

        return view('project.edit2', compact('project', 'select_array'));
    }

    /**
     * 编辑绑定产品
     */
    public function updateBindProject(Request $request, string $id)
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
            "bindid" =>  ['required', 'integer', 'exists:projects,id'],
        ]);
        $bindid = (int)$request->bindid;
        $id = (int)$id;

        if($bindid==$id) {
            $arr = ['code'=>-2, 'message'=> '不能绑定自己的id'];
            return json_encode( $arr );
        }

        DB::beginTransaction();
        try {
            $project = Project::find($id);
            $project->bind_projectid  = $request->bindid;
            $project->save();
            if(!$project->save())
                throw new \Exception('事务中断8-1');

            //查询绑定项目
            $bind_project = Project::find($bindid);

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action ='管理员' . $username . ' 绑定项目' . $bindid . ' ' . $bind_project->project_name . ' 到 ' . $project->project_name;
            $newlog->ip = $request->ip();
            $newlog->route = 'bind.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断8-2');

            $bind_projects = array([
                'id' => $project->id,
                'project_name' => $project->project_name,
                'bind_projectid' => $project->bind_projectid,
                'created_at' => $project->created_at,
                'updated_at' => $project->updated_at,
            ]);
            $project_json = json_encode($bind_projects);
            LogFile::channel("bind_project_update")>info($project_json);
            DB::commit();

        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = $e->getMessage();
            LogFile::channel("bind_project_update_error")->error($errorMessage);
            return $errorMessage;
            //return '绑定错误，事务回滚';
        }

        return redirect()->route('project.index');
    }
}
