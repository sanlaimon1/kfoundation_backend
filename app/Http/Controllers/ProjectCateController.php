<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectCate;
use Illuminate\Support\Facades\Validator;

class ProjectCateController extends Controller
{

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
        $projectcates = ProjectCate::orderBy('sort', 'asc')->paginate(10);

        return view('projectcate.index', compact('projectcates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('projectcate/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'comment' => ['required','string','max:255'],
            'sort' => ['required', 'integer', 'gt:0'],
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
        $oneprojectcate = ProjectCate::find($id);
        return view('projectcate.edit', compact('oneprojectcate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'comment' => ['required','string','max:255'],
            'sort' => ['required', 'integer', 'gt:0'],
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
        $one = ProjectCate::find($id);
        $one->enable = 0;
        $one->save();
        return redirect()->route('projectcate.index');
    }
}
