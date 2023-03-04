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
        $request->validate([
            'goods_name' => ['required', 'string', 'max:45'],
            "litpic.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            'score' => ['required', 'integer', 'gte:0'],
            'level_id' => ['required', 'integer', 'exists:levels,level_id'],
            'store_num' => ['required', 'integer', 'gte:0'],
            'count_exchange' => ['required', 'integer', 'gte:0'],
            'sort' => ['required',  'integer', 'gte:0'],
            'enable' => ['required', 'integer', 'in:0,1'],
            'comment' => ['required', 'string',  'max:100'],
        ]);

        $goods_name = trim( $request->get('goods_name') );
        $score = trim( $request->get('score') );
        $level_id = trim( $request->get('level_id') );
        $store_num = trim( $request->get('store_num') );
        $count_exchange = trim( $request->get('count_exchange') );
        $sort = trim( $request->get('sort') );
        $enable = trim( $request->get('enable') );
        $comment = trim( $request->get('comment') );

        $score = (int)$score;
        $store_num = (int)$store_num;
        $count_exchange = (int)$count_exchange;
        $sort = (int)$sort;

        if($request->hasFile('litpic')){
            $get_litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/'),$get_litpic);
            $res_litpic = '/images/'.$get_litpic;
        }

        $newgood = new Goods;
        $newgood->goods_name = $goods_name;
        $newgood->litpic = $res_litpic;
        $newgood->score = $score;
        $newgood->level_id = $level_id;
        $newgood->store_num = $store_num;
        $newgood->count_exchange = $count_exchange;
        $newgood->sort = $sort;
        $newgood->enable = $enable;
        $newgood->comment = $comment;
        
        $newgood->save();

        return redirect()->route('goods.index');
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
        $goods = Goods::find($id);

        $levels = Level::select('level_id','level_name')->get();

        $level_items = [];

        foreach($levels as $one) {
            $level_items[ $one->level_id ] = $one->level_name;
        }

        return view('goods.edit', compact('goods' ,'level_items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'goods_name' => ['required', 'string', 'max:45'],
            "litpic.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            'score' => ['required', 'integer', 'gte:0'],
            'level_id' => ['required', 'integer', 'exists:levels,level_id'],
            'store_num' => ['required', 'integer', 'gte:0'],
            'count_exchange' => ['required', 'integer', 'gte:0'],
            'sort' => ['required',  'integer', 'gte:0'],
            'enable' => ['required', 'integer', 'in:0,1'],
            'comment' => ['required', 'string',  'max:100'],
        ]);

        $goods_name = trim( $request->get('goods_name') );
        $score = trim( $request->get('score') );
        $level_id = trim( $request->get('level_id') );
        $store_num = trim( $request->get('store_num') );
        $count_exchange = trim( $request->get('count_exchange') );
        $sort = trim( $request->get('sort') );
        $enable = trim( $request->get('enable') );
        $comment = trim( $request->get('comment') );

        $score = (int)$score;
        $store_num = (int)$store_num;
        $count_exchange = (int)$count_exchange;
        $sort = (int)$sort;

        $newgood = Goods::find($id);
        $newgood->goods_name = $goods_name;

        if($request->hasFile('litpic')){
            $get_litpic = time().'.'.$request->litpic->extension();
            $request->litpic->move(public_path('/images/'),$get_litpic);
            $res_litpic = '/images/'.$get_litpic;
        }else{
            $res_litpic = $newgood->litpic;
        }
        
        $newgood->litpic = $res_litpic;
        $newgood->score = $score;
        $newgood->level_id = $level_id;
        $newgood->store_num = $store_num;
        $newgood->count_exchange = $count_exchange;
        $newgood->sort = $sort;
        $newgood->enable = $enable;
        $newgood->comment = $comment;
        
        $newgood->save();

        return redirect()->route('goods.index');
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
