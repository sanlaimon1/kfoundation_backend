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

        //rebuild datas
        $log_datas = [];
        foreach($logs as $one_log)
        {
            $action = $one_log->action;
            $res = json_decode($action, true);
            //如果是json正常解析
            if(json_last_error()==JSON_ERROR_NONE)
            {
                $phone = array_key_exists('phone', $res) ? $res['phone'] : '';
                $type = array_key_exists('type', $res) ? $res['type'] : '';
                $action = __($type, ['phone'=>$phone]);
            }
            $log_datas[] = [
                'id'=>$one_log->id,
                'phone'=>$one_log->customer->phone,
                'action'=>$action,
                'ip'=>$one_log->ip,
                'state'=>$one_log->state,
                'province'=>$one_log->province,
                'city'=>$one_log->city,
                'isp'=>$one_log->isp,
                'created_at'=>$one_log->created_at,
            ];
        }

        return view('loginlog.index', compact('logs','log_datas'));
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
        $user = $request->phone;
        $action = $request->action;
        $date_string = $request->date;
        if($date_string){
            $date_parts = explode('至', $date_string);
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            $start_date = '';
            $end_date = '';
        }

        if($date_string != null && $user != null && $action != null){
            $loginlog_search = DB::table('login_logs')
                            ->join('customers', 'customers.id', 'login_logs.userid')
                            ->whereBetween('login_logs.created_at', [$start_date, $end_date])
                            ->where([['login_logs.action', $action], ['customers.phone', $user]])
                            ->select('login_logs.*', 'customers.phone as phone')
                            ->get();
        } else {
            $loginlog_search = DB::table('login_logs')
                            ->join('customers', 'customers.id', 'login_logs.userid')
                            ->whereBetween('login_logs.created_at', [$start_date, $end_date])
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
