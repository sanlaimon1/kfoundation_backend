<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Interest;
use DB;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }
        
        $records = Interest::orderBy('refund_time', 'desc')->orderBy('created_at', 'desc')->paginate(20);

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
        $date = $request->date;
        $status = $request->status;

        if($pid != null && $customer != null && $created_at != null && $date != null && $status != null)
        {
            $interest_search = DB::table('interest')
                            ->join('customers', 'customers.id', 'interest.cid')
                            ->join('projects', 'projects.id', 'interest.pid')
                            ->whereDate('interest.refund_time', '=', $date)
                            ->whereDate('interest.created_at', '=', $created_at)
                            ->where([['customers.phone', '=', $customer], ['projects.project_name', '=', $pid], ['interest.status', '=', $status]])
                            ->orderBy('interest.refund_time', 'desc')
                            ->select('customers.id as cid', 'customers.phone as cphone', 'interest.*', 'projects.id as pid', 'projects.project_name as pname')
                            ->get();
        } else {
            $interest_search = DB::table('interest')
                            ->join('customers', 'customers.id', 'interest.cid')
                            ->join('projects', 'projects.id', 'interest.pid')
                            ->whereDate('interest.refund_time', '=', $date)
                            ->orwhereDate('interest.created_at', '=', $created_at)
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
