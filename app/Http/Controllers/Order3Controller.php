<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order3;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class Order3Controller extends Controller
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
        $order3 = Order3::orderBy('created_at','desc')->paginate(20);
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
        DB::beginTransaction();
        try {
            //code...
            $order3 = Order3::find($id);
            $order3->status = 1;
            $order3->save();

            $myself = Auth::user();
            $log = new Log();
            $log->adminid = $myself->id;
            $log->action = '管理员'. $myself->username. '通过生活缴费的订单' .$order3->id;
            $log->ip = $this->getUserIP();
            $log->route = 'order3.edit';
            $input = $request->all();
            $input_json = json_encode( $input );
            $log->parameters = $input_json;  // 请求参数
            $log->created_at = date('Y-m-d H:i:s');

            $log->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            return 'error';
        }

        return redirect()->route('order3.index');
    }

    public function show(Request $request, string $id)
    {
        DB::beginTransaction();
        try {
            //code...
            $order3 = Order3::find($id);
            $order3->status = 2;
            $order3->save();

            $myself = Auth::user();
            $log = new Log();
            $log->adminid = $myself->id;
            $log->action = '管理员'. $myself->username. '拒绝生活缴费的订单' .$order3->id;
            $log->ip = $this->getUserIP();
            $log->route = 'order3.show';
            $input = $request->all();
            $input_json = json_encode( $input );
            $log->parameters = $input_json;  // 请求参数
            $log->created_at = date('Y-m-d H:i:s');

            $log->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
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
