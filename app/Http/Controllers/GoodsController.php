<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;
use App\Models\Level;

class GoodsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of goods
     * 商品列表
     */
    public function index()
    {
        $goods = Goods::where('enable',1)->orderBy('sort', 'asc')->paginate(20);
        
        return view('goods.index', compact('goods'));
    }

    /**
     * 创建商品
     */
    public function create()
    {
        $levels = Level::select('level_id','level_name')->get();

        $level_items = [];

        foreach($levels as $one) {
            $level_items[ $one->level_id ] = $one->level_name;
        }

        return view('goods.create', compact('level_items'));
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
        $id = (int)$id;
        $one = Goods::find($id);
        $one->enable = 0;
        $one->save();
        return redirect()->route('goods.index');
    }
}
