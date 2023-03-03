<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::where('enable', 1)->orderBy('sort', 'asc')->paginate(10);
        return view('category/index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('category/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //表单验证
        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'sort' => ['required', 'integer', 'gt:0'],
        ]);

        $category_name = trim($request->cate_name);
        $sort = trim($request->sort);

        $category = new Category();
        $category->cate_name = $category_name;
        $category->sort = $sort;
        $category->save();

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
        $category = Category::find($id);
        return view('category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'cate_name' => ['required', 'string', 'between:1,40'],
            'sort' => ['required', 'integer', 'gt:0'],
        ]);

        $category_name = trim($request->cate_name);
        $sort = trim($request->sort);

        $category = Category::find($id);
        $category->cate_name = $category_name;
        $category->sort = $sort;
        $category->save();

        return redirect()->route('category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        $category->enable = 0;
        $category->save();
        return redirect()->route('category.index');
    }
}
