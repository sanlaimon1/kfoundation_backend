<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\ProjectCate;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 项目列表
     */
    public function index()
    {
        $projects = Project::where('enable', 1)->orderBy('created_at', 'desc')->paginate(10);

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
        $request->validate([
            "project_name" => "required",
            "cate_name" => "required",
            "guarantee" => "required",
            "risk" => "required",
            "usage" => "required",
            "frontend" => "required",
            "return_mode" => "required",
            "amount" => "required",
            "is_given" => "required",
            "team_rate" => "required",
            "like_rate" => "required",
            "benefit_rate" => "required",
            "fake_process" => "required",
            "days" => "required",
            "min_invest" => "required",
            "max_invest" => "required",
            "max_time" => "required",
            "desc" => "required",
            "is_homepage" => "required",
            "is_recommend" => "required",
            "level_id" => "required",
            "litpic" => "required",
            "detail" => "required",
        ]);
        if($request->hasFile('litpic')){
            $litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/project_imgs/'),$litpic);
            $litpic = '/images/project_imgs/'.$litpic;
        }
        DB::beginTransaction();
        try {

            $project = new Project();
            $project->project_name  = $request->project_name;
            $project->cid  = $request->cate_name;
            $project->guarantee  = $request->guarantee;
            $project->risk  = $request->risk;
            $project->usage  = $request->usage;
            $project->frontend  = $request->frontend;
            $project->return_mode  = $request->return_mode;
            $project->amount  = $request->amount;
            $project->is_given  = $request->is_given;
            $project->team_rate  = $request->team_rate;
            $project->like_rate = $request->like_rate;
            $project->benefit_rate  = $request->benefit_rate;
            $project->fake_process  = $request->fake_process;
            $project->days  = $request->days;
            $project->min_invest  = $request->min_invest;
            $project->max_invest  = $request->max_invest;
            $project->max_times  = $request->max_time;
            $project->desc  = $request->desc;
            $project->is_homepage  = $request->is_homepage;
            $project->is_recommend  = $request->is_recommend;
            $project->level_id  = $request->level_id;
            $project->litpic = $litpic;
            $project->details = $request->detail;
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $project = Project::find($id);
        $return_modes = config('types.return_mode');
        return view('project.show', compact('project','return_modes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
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
        $request->validate([
            "project_name" => "required",
            "cate_name" => "required",
            "guarantee" => "required",
            "risk" => "required",
            "usage" => "required",
            "frontend" => "required",
            "return_mode" => "required",
            "amount" => "required",
            "is_given" => "required",
            "team_rate" => "required",
            "like_rate" => "required",
            "benefit_rate" => "required",
            "fake_process" => "required",
            "days" => "required",
            "min_invest" => "required",
            "max_invest" => "required",
            "max_time" => "required",
            "desc" => "required",
            "is_homepage" => "required",
            "is_recommend" => "required",
            "level_id" => "required",
            "litpic" => "required|sometimes",
            "detail" => "required",
        ]);
        if($request->hasFile('litpic')){
            $litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/project_imgs/'),$litpic);
            $litpic = '/images/project_imgs/'.$litpic;
        }else{
            $litpic = $request->old_liptic;
        }
        DB::beginTransaction();
        try {

            $project = Project::find($id);
            $project->project_name  = $request->project_name;
            $project->cid  = $request->cate_name;
            $project->guarantee  = $request->guarantee;
            $project->risk  = $request->risk;
            $project->usage  = $request->usage;
            $project->frontend  = $request->frontend;
            $project->return_mode  = $request->return_mode;
            $project->amount  = $request->amount;
            $project->is_given  = $request->is_given;
            $project->team_rate  = $request->team_rate;
            $project->like_rate = $request->like_rate;
            $project->benefit_rate  = $request->benefit_rate;
            $project->fake_process  = $request->fake_process;
            $project->days  = $request->days;
            $project->min_invest  = $request->min_invest;
            $project->max_invest  = $request->max_invest;
            $project->max_times  = $request->max_time;
            $project->desc  = $request->desc;
            $project->is_homepage  = $request->is_homepage;
            $project->is_recommend  = $request->is_recommend;
            $project->level_id  = $request->level_id;
            $project->litpic = $litpic;
            $project->details = $request->detail;
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
