<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Syslog;

class SyslogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 系统日志列表 list of system logs
     */
    public function index()
    {
        $logs = Syslog::orderBy('created_at','desc')->paginate(10);

        return view( 'syslog.index', compact('logs') );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $id = (int) $id;
        //查询一条记录
        $one = Syslog::find($id);

        return view('syslog.show', ['one'=>$one]);
    }

    public function log_search(Request $request)
    {
        /*$search_logs = DB::table('logs')
                            ->join('admins','admins.id','=','logs.adminid')
                            ->whereDate('logs.created_at','=',$request->date)
                            ->orwhere('logs.adminid',$request->adminid)
                            ->orwhere('logs.action','=',$request->action)
                            ->select('logs.*','admins.username')
                            ->get();
        return response()->json([
            'search_logs' => $search_logs
        ]);*/
    }
}
