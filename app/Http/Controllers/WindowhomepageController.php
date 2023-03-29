<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class WindowhomepageController extends Controller
{
    private $path_name = "/windowhomepage";
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
        //查询 cate=2 的数据
        $items = Config::where('cate', 2)->get();

        $item_cate1 = [];

        foreach($items as $one) {
            $item_cate1[ $one->config_name ] = $one;
        }

        $window_details = $item_cate1['window_details'];  //弹窗详情
        $is_shown = $item_cate1['is_shown'];  //是否显示

        if (!Redis::exists("popwindow:homepage:md5")) {
            $popup_content = Config::find(10)->config_value;

            $popup = [
                'content'=>$popup_content,
                'show'=>1,
            ];

            $popwindow = json_encode($popup);
            Redis::set( "popwindow:homepage:string", $popwindow );
            Redis::set( "popwindow:homepage:md5", md5($popwindow) );
        }

        return view('config.windowhomepage', compact('window_details','is_shown') );
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
            // $one_config->save();
            if(!$one_config->update())
                throw new \Exception('事务中断1');

            $myself = Auth::user();
            $log = new Log();
            $log->adminid = $myself->id;
            $log->action = '管理员'. $myself->username. ' 修改站内信';
            $log->ip = $request->ip();
            $log->route = 'windowhomepage.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $log->parameters = $input_json;  // 请求参数
            $log->created_at = date('Y-m-d H:i:s');

            if(!$log->save())
                throw new \Exception('事务中断2');
            DB::commit();
            LogFile::channel("update")->info("文章列表 更新成功");

            $old_popup = Redis::get("popwindow:homepage:md5");
            $popup_content = Config::find(10)->config_value;

            $popup = [
                'content'=>$popup_content,
                'show'=>1,
            ];
            $popwindow = json_encode($popup);
            if(md5($popwindow) != $old_popup) {
                Redis::set("popwindow:homepage:string", $popwindow);
                Redis::set("popwindow:homepage:md5", md5($popwindow));
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("error")->error($message);
            //echo $e->getMessage();
            return 'error';
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
