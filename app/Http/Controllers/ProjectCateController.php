<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectCate;

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
