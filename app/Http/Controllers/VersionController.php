<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;

class VersionController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        //查询 cate=3 的数据
        $items = Config::where('cate', 3)->get();

        $item_cate3 = [];

        foreach($items as $one) {
            $item_cate3[ $one->config_name ] = $one;
        }

        $app_version = $item_cate3['app_version'];  //APP版本号
        $android = $item_cate3['android'];  //网站域名
        $ios = $item_cate3['ios'];  //客服链接
        $app_update = $item_cate3['app_update'];  //最低提现金额

        return view('config.version', compact('app_version','android','ios','app_update') );
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
        //修改数据
        if(!is_numeric($id)) {
            $arr = ['code'=>-1, 'message'=>'id必须是整数'];
            return json_encode( $arr );
        }

        //收到值
        $config_value = trim( htmlspecialchars( $request->get('config_value') ));

        $id = (int)$id;
        //查询一条数据
        $one_config = Config::find($id);
        $one_config->config_value = $config_value;
        $one_config->save();

        $arr = ['code'=>1, 'message'=>'保存成功'];
        return response()->json( $arr );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
