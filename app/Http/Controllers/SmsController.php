<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class SmsController extends Controller
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
    private $path_name = "/sms";

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
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        //查询 cate=4 的数据
        $items = Config::where('cate', 4)->get();

        $item_cate4 = [];

        foreach($items as $one) {
            $item_cate4[ $one->config_name ] = $one;
        }

        $smsapi = $item_cate4['smsapi'];  //发送短信接口
        $alicloud_accesskey_id = $item_cate4['alicloud_accesskey_id'];  //阿里云短信AccessKey ID
        $alicloud_accesskey_secret = $item_cate4['alicloud_accesskey_secret'];  //阿里云短信AccessKey Secret
        $smsbao_accesskey_id = $item_cate4['smsbao_accesskey_id'];  //短信宝账号
        $smsbao_accesskey_secret = $item_cate4['smsbao_accesskey_secret'];  //短信宝密码

        $smsapi_array = config('data.smsapi_array');

        return view('config.sms', compact('smsapi','smsapi_array','alicloud_accesskey_id','alicloud_accesskey_secret','smsbao_accesskey_id','smsbao_accesskey_secret') );
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

        //收到值
        $config_value = trim( htmlspecialchars( $request->get('config_value') ));

        $id = (int)$id;
        //查询一条数据
        DB::beginTransaction();
        try {
            $one_config = Config::find($id);
            $one_config->config_value = $config_value;
            if(!$one_config->save())
                throw new \Exception('事务中断1');

            $sms = array(
                'id' => $one_config->id,
                'config_value' => $one_config->config_value,
            );

            $sms_json = json_encode($sms);

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $update_action = ['username' => $username, 'type' => 'log.update_action'];
            $action = json_encode($update_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'sms.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("sms_update")->info($sms_json);

        } catch (\Exception $e) {
            DB::rollback();
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            $message = $e->getMessage();
            LogFile::channel("sms_update_error")->error($message);
            return '修改错误，事务回滚';
        }

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
