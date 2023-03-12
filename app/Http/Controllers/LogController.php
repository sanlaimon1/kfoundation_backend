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

        $managers = Admin::all();

        return view( 'log.index', compact('logs','managers') );
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
        $date_parts = explode('至', $date_string);
        $start_date = trim($date_parts[0]);
        $end_date = trim($date_parts[1]);
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
