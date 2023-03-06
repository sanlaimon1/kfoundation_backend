<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialBalance;
use DB;

class FinancialBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 余额流水记录
     */
    public function index()
    {
        $records = FinancialBalance::orderBy('created_at', 'desc')->paginate(20);

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
        $date = $request->date;
        if($financial_id != null && $customer != null && $financial_type != 0 && $date != null)
        {
            $balance_search = DB::table('financial_balance')
                            ->join('customers', 'customers.id', 'financial_balance.userid')
                            ->where([['financial_balance.id', '=', $financial_id], ['customers.phone', '=', $customer], ['financial_balance.financial_type', '=', $financial_type]])
                            ->whereDate('financial_balance.created_at', '=', $date)
                            ->orderBy('financial_balance.created_at', 'desc')
                            ->select('customers.phone', 'financial_balance.*')
                            ->get();
        } else {
            $balance_search = DB::table('financial_balance')
                            ->join('customers', 'customers.id', 'financial_balance.userid')
                            ->whereDate('financial_balance.created_at', '=', $date)
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
