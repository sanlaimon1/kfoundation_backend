<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\ProjectCate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $projectcates = ProjectCate::orderBy('sort', 'asc')->paginate(10);

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

        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 4) ){
            return "您没有权限访问这个路径";
        } 
        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'comment' => ['required','string','max:200'],
            'sort' => ['required', 'integer', 'gte:0'],
        ]);

        $category_name = trim($request->cate_name);
        $comment = trim($request->comment);
        $sort = trim($request->sort);

        $newprojectcates = new ProjectCate();
        $newprojectcates->cate_name = $category_name;
        $newprojectcates->comment = $comment;
        $newprojectcates->created_at = date('Y-m-d H:i:s');
        $newprojectcates->sort = $sort;
        $newprojectcates->save();

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
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'comment' => ['required','string','max:200'],
            'sort' => ['required', 'integer', 'gte:0'],
        ]);

        $category_name = trim($request->cate_name);
        $comment = trim($request->comment);
        $sort = trim($request->sort);

        $oneprojectcate = ProjectCate::find($id);
        $oneprojectcate->cate_name = $category_name;
        $oneprojectcate->comment = $comment;
        $oneprojectcate->sort = $sort;
        $oneprojectcate->save();

        return redirect()->route('projectcate.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }

        $one = ProjectCate::find($id);
        $one->enable = 0;
        $one->save();
        return redirect()->route('projectcate.index');
    }
}
