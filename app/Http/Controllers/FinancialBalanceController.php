<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\FinancialBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinancialBalanceController extends Controller
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
    private $path_name = "/balance";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 余额流水记录
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = FinancialBalance::orderBy('created_at', 'desc')->paginate($perPage);

        $types = config('types.balance_financial_type');

        $title = '余额流水记录';

        return view( 'financialbalance.index', compact('records', 'types', 'title') );
    }

    public function balance_search(Request $request)
    {
        $types = config('types.balance_financial_type');
        $financial_id = $request->financial_id;
        $customer = $request->customer;
        $financial_type = $request->financial_type;

        $date_string = $request->date;
        if($date_string){
            $date_parts = explode('至', $date_string);
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            $start_date = '';
            $end_date = '';
        }


        if($financial_id != null && $customer != null && $financial_type != 0 && $date_string != null)
        {
            $balance_search = DB::table('financial_balance')
                            ->join('customers', 'customers.id', 'financial_balance.userid')
                            ->where([['financial_balance.id', '=', $financial_id], ['customers.phone', '=', $customer], ['financial_balance.financial_type', '=', $financial_type]])
                            ->whereBetween('financial_balance.created_at', [$start_date, $end_date])
                            ->orderBy('financial_balance.created_at', 'desc')
                            ->select('customers.phone', 'financial_balance.*')
                            ->get();
        } else {
            $balance_search = DB::table('financial_balance')
                            ->join('customers', 'customers.id', 'financial_balance.userid')
                            ->whereBetween('financial_balance.created_at', [$start_date, $end_date])
                            ->orwhere('financial_balance.financial_type', '=', $financial_type)
                            ->orwhere('financial_balance.id', '=', $financial_id)
                            ->orwhere('customers.phone', '=', $customer)
                            ->orderBy('financial_balance.created_at', 'desc')
                            ->select('customers.phone', 'financial_balance.*')
                            ->get();
        }

        return response()->json([
            'balance_search' => $balance_search,
            'types' => $types,
        ]);

    }
}
