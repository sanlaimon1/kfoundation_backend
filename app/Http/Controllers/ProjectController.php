<?php

namespace App\Http\Controllers;

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
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
