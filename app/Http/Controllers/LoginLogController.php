<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginLog;
use DB;
use Carbon\Carbon;

class LoginLogController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 查询用户登录日志
     */
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $logs = LoginLog::orderBy('created_at', 'desc')->paginate($perPage);

        return view('loginlog.index', compact('logs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $id = (int) $id;
        //查询一条记录
        $one = LoginLog::find($id);

        return view('loginlog.show', ['one'=>$one]);
    }

    public function loginlog_search(Request $request)
    {
        $date = Carbon::parse($request->date)->format('Y-m-d');
        $user = $request->phone;
        $action = $request->action;
        if($date != null && $user != null && $action != null){
            $loginlog_search = DB::table('login_logs')
                            ->join('customers', 'customers.id', 'login_logs.userid')
                            ->whereDate('login_logs.created_at', $date)
                            ->where([['login_logs.action', $action], ['customers.phone', $user]])
                            ->select('login_logs.*', 'customers.phone as phone')
                            ->get();
        } else {
            $loginlog_search = DB::table('login_logs')
                            ->join('customers', 'customers.id', 'login_logs.userid')
                            ->whereDate('login_logs.created_at', $date)
                            ->orWhere('login_logs.action', $action)
                            ->orWhere('customers.phone', $user)
                            ->select('login_logs.*', 'customers.phone as phone')
                            ->get();
        }
        return response()->json([
            'loginlog_search' => $loginlog_search,
        ]);
    }
}
