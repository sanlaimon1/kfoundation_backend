<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\FinancialIntegration;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinancialIntegrationController extends Controller
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
    private $path_name = "/integration";

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
        $records = FinancialIntegration::orderBy('created_at', 'desc')->paginate($perPage);

        $types = config('types.integration_financial_type');

        $title = '积分流水记录';

        return view( 'financialintegration.index', compact('records', 'types','title') );
    }

    public function integration_search(Request $request)
    {
        $types = config('types.integration_financial_type');
        $financialintegration_id = $request->financialintegration_id;
        $customer = $request->customer;
        $financial_type = $request->financial_type;

        $date_string = $request->date;
        $date_parts = explode('至', $date_string);
        $start_date = trim($date_parts[0]);
        $end_date = trim($date_parts[1]);

        if($financialintegration_id != null && $customer != null && $financial_type != 0 && $date_string != null)
        {
            $integration_search = DB::table('financial_integration')
                            ->join('customers', 'customers.id', 'financial_integration.userid')
                            ->where([['financial_integration.id', '=', $financialintegration_id], ['customers.phone', '=', $customer], ['financial_integration.financial_type', '=', $financial_type]])
                            ->whereBetween('financial_integration.created_at', [$start_date, $end_date])
                            ->orderBy('financial_integration.created_at', 'desc')
                            ->select('customers.phone', 'financial_integration.*')
                            ->get();
        } else {
            $integration_search = DB::table('financial_integration')
                            ->join('customers', 'customers.id', 'financial_integration.userid')
                            ->whereBetween('financial_integration.created_at', [$start_date, $end_date])
                            ->orwhere('financial_integration.financial_type', '=', $financial_type)
                            ->orwhere('financial_integration.id', '=', $financialintegration_id)
                            ->orwhere('customers.phone', '=', $customer)
                            ->orderBy('financial_integration.created_at', 'desc')
                            ->select('customers.phone', 'financial_integration.*')
                            ->get();
        }

        return response()->json([
            'integration_search' => $integration_search,
            'types' => $types,
        ]);
    }

}
