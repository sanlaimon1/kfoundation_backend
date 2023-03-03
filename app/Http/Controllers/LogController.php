<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Admin;
use DB;

class LogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the logs.
     * 日志列表
     */
    public function index()
    {
        $logs = Log::orderBy('created_at','desc')->paginate(10);

        $managers = Admin::all();

        return view( 'log.index', compact('logs','managers') );
    }

    /**
     * Display one log
     * 显示日志
     */
    public function show(string $id)
    {
        $id = (int) $id;
        //查询一条记录
        $one = Log::find($id);

        return view('log.show', ['one'=>$one]);
    }


    public function log_search(Request $request)
    {
        $search_logs = DB::table('logs')
                            ->join('admins','admins.id','=','logs.adminid')
                            ->whereDate('logs.created_at','=',$request->date)
                            ->orwhere('logs.adminid',$request->adminid)
                            ->orwhere('logs.action','=',$request->action)
                            ->select('logs.*','admins.username')
                            ->get();
        return response()->json([
            'search_logs' => $search_logs
        ]);
    }

}
