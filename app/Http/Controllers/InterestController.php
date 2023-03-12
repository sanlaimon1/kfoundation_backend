<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Interest;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InterestController extends Controller
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
    private $path_name = "/interest";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 返息明细列表
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = Interest::orderBy('refund_time', 'desc')->orderBy('created_at', 'desc')->paginate($perPage);

        $title = "返息明细";

        return view( 'interest.index', compact('records', 'title') );
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function interest_search(Request $request)
    {

        $pid = $request->pid;
        $customer = $request->customer;

        $created_at = $request->created_at;
        $date_parts = explode('至', $created_at);
        $start_date = trim($date_parts[0]);
        $end_date = trim($date_parts[1]);

        $refund_time = $request->refund_time;
        $date_parts2 = explode('至', $refund_time);
        $start_date2 = trim($date_parts2[0]);
        $end_date2 = trim($date_parts2[1]);

        $status = $request->status;

        if($pid != null && $customer != null && $created_at != null && $refund_time != null && $status != null)
        {
            $interest_search = DB::table('interest')
                            ->join('customers', 'customers.id', 'interest.cid')
                            ->join('projects', 'projects.id', 'interest.pid')
                            ->whereBetween('interest.created_at', [$start_date, $end_date])
                            ->whereBetween('interest.refund_time', [$start_date2, $end_date2])
                            ->where([['customers.phone', '=', $customer], ['projects.project_name', '=', $pid], ['interest.status', '=', $status]])
                            ->orderBy('interest.refund_time', 'desc')
                            ->select('customers.id as cid', 'customers.phone as cphone', 'interest.*', 'projects.id as pid', 'projects.project_name as pname')
                            ->get();
        } else {
            $interest_search = DB::table('interest')
                            ->join('customers', 'customers.id', 'interest.cid')
                            ->join('projects', 'projects.id', 'interest.pid')
                            ->whereBetween('interest.created_at', [$start_date, $end_date])
                            ->orWhereBetween('interest.refund_time', [$start_date2, $end_date2])
                            ->orwhere('customers.phone', '=', $customer)
                            ->orwhere('projects.project_name', '=', $pid)
                            ->orwhere('interest.status', '=', $status)
                            ->orderBy('interest.refund_time', 'desc')
                            ->select('customers.id as cid', 'customers.phone as cphone', 'interest.*', 'projects.id as pid', 'projects.project_name as pname')
                            ->get();
        }

        return response()->json([
            'interest_search' => $interest_search,
        ]);
    }

}
