<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Order3;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class Order3Controller extends Controller
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
    private $path_name = "/order3";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $order3 = Order3::orderBy('created_at','desc')->paginate($perPage);
        return view('order3.index',compact('order3'));
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
    public function edit(Request $request,string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 16) ){
                return "您没有权限访问这个路径";
            }

            DB::beginTransaction();
            try {
                //code...
                $order3 = Order3::find($id);
                $order3->status = 1;
                if(!$order3->save())
                    throw new \Exception('事务中断1');

                $myself = Auth::user();
                $log = new Log();
                $log->adminid = $myself->id;
                $order3_status1_action = ['username' => $myself->username, 'order3id' => $order3->id, 'type' => 'log.order3_status1_action'];
                $action = json_encode($order3_status1_action);
                $log->action = $action;
                $log->ip = $this->getUserIP();
                $log->route = 'order3.change_status1';
                $input = $request->all();
                $input_json = json_encode( $input );
                $log->parameters = $input_json;  // 请求参数
                $log->created_at = date('Y-m-d H:i:s');

                if(!$log->save())
                    throw new \Exception('事务中断2');

                $order3_datas = array([
                    'id' => $order3->id,
                    'status' => $order3->status,
                    'created_at' => $order3->created_at,
                    'updated_at' => $order3->updated_at,
                ]);
                $order3_datas_json = json_encode($order3_datas);
                DB::commit();
                LogFile::channel("order3_status1")->info($order3_datas_json);

            } catch (\Exception $e) {
                DB::rollBack();
                //echo $e->getMessage();
                $message = $e->getMessage();
                LogFile::channel("order3_status1_error")->error($message);
                return '添加错误，事务回滚';
            }

            return redirect()->route('order3.index');
    }

    public function show(Request $request, string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));

            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 8) ){
                return "您没有权限访问这个路径";
            }

            DB::beginTransaction();
            try {
                //code...
                $order3 = Order3::find($id);
                $order3->status = 2;
                if(!$order3->save())
                    throw new \Exception('事务中断3');

                $myself = Auth::user();
                $log = new Log();
                $log->adminid = $myself->id;
                $order3_status2_action = ['username' => $myself->username, 'order3id' => $order3->id, 'type' => 'log.order3_status2_action'];
                $action = json_encode($order3_status2_action);
                $log->action = $action;
                $log->ip = $this->getUserIP();
                $log->route = 'order3.change_status2';
                $input = $request->all();
                $input_json = json_encode( $input );
                $log->parameters = $input_json;  // 请求参数
                $log->created_at = date('Y-m-d H:i:s');

                if(!$log->save())
                    throw new \Exception('事务中断4');

                $order3_datas = array([
                    'id' => $order3->id,
                    'status' => $order3->status,
                    'created_at' => $order3->created_at,
                    'updated_at' => $order3->updated_at,
                ]);
                $order3_datas_json = json_encode($order3_datas);
                LogFile::channel("order3_status2")->info($order3_datas_json);
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                //echo $e->getMessage();
                $message = $e->getMessage();
                LogFile::channel("order3_status2_error")->error($message);
                return 'error';
            }

            return redirect()->route('order3.index');
    }

    public function getUserIP()
    {
        if (getenv('HTTP_CLIENT_IP')){
            $ip = getenv('HTTP_CLIENT_IP');
        }
        if (getenv('HTTP_X_REAL_IP'))
        {
            $ip = getenv('HTTP_X_REAL_IP');
        }
            else if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
                $ips = explode(',', $ip);
                $ip = $ips[0];
            }
            else if (getenv('REMOTE_ADDR'))
            {
                $ip = getenv('REMOTE_ADDR');
            }
            else
            {
                $ip = '0.0.0.0';
            }
            return $ip;
    }



}
