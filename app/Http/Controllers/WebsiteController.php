<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;
use Illuminate\Support\Facades\DB;

class WebsiteController extends Controller
{
    private $path_name = "/website";
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
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }
        $items = Config::where('cate', 1)->get();

        $item_cate1 = [];

        foreach($items as $one) {
            $item_cate1[ $one->config_name ] = $one;
        }

        // store string data of web_name/ is_kline/  in redis
        $static_url = config("app.static_url");
        $web_name = '';
        if(Redis::exists('web_name')) {
            $web_name = Redis::get('web_name');
        } else {
            $web_name = Config::find(1)->config_value;
            Redis::set('web_name', $web_name);
        }

        $is_kline = '';
        if(Redis::exists('is_kline')) {
            $is_kline = Redis::get('is_kline');
        } else {
            $is_kline = Config::find(7)->config_value;
            Redis::set('is_kline', $is_kline);
        }

        $customer_service = '';
        if(Redis::exists('customer_service')) {
            $customer_service = Redis::get('customer_service');
        } else {
            $customer_service = Config::find(3)->config_value;
            Redis::set('customer_service', $customer_service);
        }

        $home_video = '';
        if(Redis::exists('home_video')) {
            $home_video = Redis::get('home_video');
        } else {
            $home_video = $static_url.Config::find(9)->config_value;
            Redis::set('home_video', $home_video);
        }

        $logo = '';
        if(Redis::exists('logo')) {
            $logo = Redis::get('logo');
        } else {
            $logo = $static_url . Config::find(8)->config_value;
            Redis::set('logo', $logo);
        }

        $data = [
            "web_name" => $web_name,
            "customer_service" => $customer_service,
            "logo" => $logo,
            "home_video" => $home_video,
            "is_kline" => $is_kline,
        ];

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
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        //修改数据
        if(!is_numeric($id)) {
            $arr = ['code'=>-1, 'message'=>'id必须是整数'];
            return json_encode( $arr );
        }

        if($id == 8){
            if($request->hasFile('logo')){
                $get_logo = time().'.'.$request->logo->extension();
                $request->logo->move(public_path('/images/'),$get_logo);
                $config_value = '/images/'.$get_logo;
            }
        }else if($id == 9){
            if($request->hasFile('video_home')){
                $get_video_home = time().'.'.$request->video_home->extension();
                $request->video_home->move(public_path('/mp4File/'),$get_video_home);
                $config_value = '/mp4File/'.$get_video_home;

                // $config_value = $this->convertMp4ToWebP($request->file('video_home'));
            }
        }else{
            //收到值
            $config_value = trim( htmlspecialchars( $request->get('config_value') ));
        }

        DB::beginTransaction();
        try {
            $id = (int)$id;
            //查询一条数据
            $one_config = Config::find($id);
            $one_config->config_value = $config_value;
            $website = array(
                'id' => $one_config->id,
                "config_value" => $one_config->config_value,
            );
            $website_json = json_encode($website);
            if(!$one_config->save())
                throw new \Exception('事务中断2');
            DB::commit();
            
            LogFile::channel("website_update")->info($website_json);

            // store string data of web_name/ is_kline/  in redis
            $static_url = config("app.static_url");
            $web_name = '';

            if(Redis::get('web_name') == Config::find(1)->config_value) {
                $web_name = Redis::get('web_name');
            } else {
                $web_name = Config::find(1)->config_value;
                Redis::set('web_name', $web_name);
            }

            $is_kline = '';
            if(Redis::get('is_kline') == Config::find(7)->config_value) {
                $is_kline = Redis::get('is_kline');
            } else {
                $is_kline = Config::find(7)->config_value;
                Redis::set('is_kline', $is_kline);
            }

            $customer_service = '';
            if(Redis::get('customer_service') == Config::find(3)->config_value) {
                $customer_service = Redis::get('customer_service');
            } else {
                $customer_service = Config::find(3)->config_value;
                Redis::set('customer_service', $customer_service);
            }

            $home_video = '';
            if(Redis::get('home_video') == $static_url . Config::find(9)->config_value) {
                $home_video = Redis::get('home_video');
            } else {
                $home_video = $static_url . Config::find(9)->config_value;
                Redis::set('home_video', $home_video);
            }

            $logo = '';
            if(Redis::get('logo') == $static_url . Config::find(8)->config_value) {
                $logo = Redis::get('logo');
            } else {
                $logo = $static_url . Config::find(8)->config_value;
                Redis::set('logo', $logo);
            }

            $data = [
                "web_name" => $web_name,
                "customer_service" => $customer_service,
                "logo" => $logo,
                "home_video" => $home_video,
                "is_kline" => $is_kline,
            ];

            if($id == 8 || $id == 9){
                return redirect()->route('website.index');
            }else{
                $arr = ['code'=>1, 'message'=>'保存成功'];
                return response()->json( $arr );
            }
        }catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("website_update_error")->error($message);
            //echo $e->getMessage();
            return 'error';
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
