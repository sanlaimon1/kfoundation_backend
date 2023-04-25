<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Log;
use App\Models\Admin;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LogController extends Controller
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
    private $path_name = "/log";

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
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $logs = Log::orderBy('created_at','desc')->paginate($perPage);

        foreach ($logs as $log) {
            $action = $log->action;
            $res = json_decode($action, true);
            //如果是json正常解析
            if(json_last_error()==JSON_ERROR_NONE) {
                $username = array_key_exists('username',$res) ? $res['username'] : '';
                $userphone = array_key_exists('userphone',$res) ? $res['userphone'] : '';
                $amount = array_key_exists('amount',$res) ? $res['amount'] : ''; 
                $ipaddress = array_key_exists('ipaddress',$res) ? $res['ipaddress'] : '';
                $customer_name = array_key_exists('customer_name',$res) ? $res['customer_name'] : '';
                $phone = array_key_exists('phone', $res) ? $res['phone'] : '';
                $order3id = array_key_exists('order3id', $res) ? $res['order3id'] : '';
                $roletitle = array_key_exists('roletitle', $res) ? $res['roletitle'] : '';
                $projectname = array_key_exists('projectname', $res) ? $res['projectname'] : '';
                $realname = array_key_exists('realname', $res) ? $res['realname'] : '';
                $id = array_key_exists('id', $res) ? $res['id'] : '';
                $bindid = array_key_exists('bindid', $res) ? $res['bindid'] : '';
                $bindprojectname = array_key_exists('bindprojectname', $res) ? $res['bindprojectname'] : '';
                $type = array_key_exists('type', $res) ? $res['type'] : '';

                $action = __($type, ['username' => $username, 'userphone' => $userphone,'amount' => $amount, 'ipaddress' => $ipaddress,
                            'customer_name' => $customer_name, 'phone' => $phone, 'order3id' => $order3id,
                            'roletitle' => $roletitle, 'projectname' => $projectname, 'realname' => $realname, 
                            'id' => $id, 'bindid' => $bindid, 'bindprojectname' => $bindprojectname]);
            }
            
            $record_logs[] = [
                'id' => $log->id,
                'adminid' => $log->adminid,
                'admin_username' => $log->oneadmin->username,
                'action' => $action,
                'ip' => $log->ip,
                'route' => $log->route,
                'parameters' => $log->parameters,
                'created_at' => $log->created_at
            ];
        }
        $managers = Admin::all();

        return view( 'log.index', compact('record_logs','logs','managers') );
    }

    /**
     * Display one log
     * 显示日志
     */
    public function show(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 8) ){
            return "您没有权限访问这个路径";
        }

        $id = (int) $id;
        //查询一条记录
        $one = Log::find($id);

        return view('log.show', ['one'=>$one]);
    }


    public function log_search(Request $request)
    {
        $date_string = $request->date;
        if($date_string){
            $date_parts = explode('至', $date_string);
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            $start_date = '';
            $end_date = '';
        }

        $search_logs = DB::table('logs')
                            ->join('admins','admins.id','=','logs.adminid')
                            ->whereBetween('logs.created_at', [$start_date, $end_date])
                            ->orwhere('logs.adminid',$request->adminid)
                            ->orwhere('logs.action','=',$request->action)
                            ->select('logs.*','admins.username')
                            ->get();
        return response()->json([
            'search_logs' => $search_logs
        ]);
    }

}
