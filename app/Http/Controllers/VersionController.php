<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class VersionController extends Controller
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
    private $path_name = "/version";

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

        //查询 cate=3 的数据
        $items = Config::where('cate', 3)->get();

        $item_cate3 = [];

        foreach($items as $one) {
            $item_cate3[ $one->config_name ] = $one;
        }

        $app_version = $item_cate3['app_version'];  //APP版本号
        $android = $item_cate3['android'];  //网站域名
        $ios = $item_cate3['ios'];  //客服链接
        $app_update = $item_cate3['app_update'];  //android 
        $android_apk = $item_cate3['android_apk'];  //安卓apk更新地址

        return view('config.version', compact('app_version','android','ios','app_update', 'android_apk') );
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

        DB::beginTransaction();
        try {
             //查询一条数据
            $one_config = Config::find($id);
            $one_config->config_value = $config_value;
            // $one_config->save();
            if(!$one_config->update())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . ' 修改站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'version.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();

            $version = array(
                "id" => $id,
                "config_value" => $config_value,
            );
            $version_json = json_encode($version);
            LogFile::channel("version_update")->info($version_json);

        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("version_update_error")->error($message);
            return '添加错误，事务回滚';
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
