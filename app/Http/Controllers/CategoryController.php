<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use DB;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }
    
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

        $sort = (int)$sort;

        DB::beginTransaction();
        try {
            //code...
            $newcategory = new Category();
            $newcategory->cate_name = $category_name;
            $newcategory->sort = $sort;
            $newcategory->save();

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '删除用户';
            $newlog->ip = $request->ip();
            $newlog->route = 'category.store';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
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

        $sort = (int)$sort;

        DB::beginTransaction();
        try {
            //code...
            $newcategory = Category::find($id);
            $newcategory->cate_name = $category_name;
            $newcategory->sort = $sort;
            $newcategory->save();

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '删除用户';
            $newlog->ip = $request->ip();
            $newlog->route = 'category.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            return '添加错误，事务回滚';
        }

        return redirect()->route('category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        DB::beginTransaction();
        try {
            $id = (int)$id;
            $category = Category::find($id);
            $category->enable = 0;
            $category->save();

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '删除用户';
            $newlog->ip = $request->ip();
            $newlog->route = 'category.destroy';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            return '添加错误，事务回滚';
        }
        return redirect()->route('category.index');
    }
}
