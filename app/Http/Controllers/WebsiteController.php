<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;

class WebsiteController extends Controller
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
        //查询 cate=1 的数据
        $items = Config::where('cate', 1)->get();

        $item_cate1 = [];

        foreach($items as $one) {
            $item_cate1[ $one->config_name ] = $one;
        }

        $website = $item_cate1['website'];  //网站名称
        $domain_name = $item_cate1['domain_name'];  //网站域名
        $customer_service = $item_cate1['customer_service'];  //客服链接
        $min_withdrawal = $item_cate1['min_withdrawal'];  //最低提现金额
        $min_charge = $item_cate1['min_charge'];  //最低充值金额
        $times_withdrawal_everyday = $item_cate1['times_withdrawal_everyday'];  //每日提现次数
        $kline_homepage = $item_cate1['kline_homepage'];  //首页K线
        $logo = $item_cate1['logo'];  //网站logo
        $video_homepage = $item_cate1['video_homepage'];  //首页视频

        return view('config.website', compact('website','domain_name','customer_service', 
        'min_withdrawal', 'min_charge', 'times_withdrawal_everyday','kline_homepage','logo','video_homepage') );
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
