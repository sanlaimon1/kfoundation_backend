<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialPlatformCoin;
use DB;

class FinancialPlatformCoinController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 平台币流水记录
     */
    public function index()
    {
        $records = FinancialPlatformCoin::orderBy('created_at', 'desc')->paginate(20);

        $types = config('types.platform_financial_type');

        $title = '平台币流水记录';

        return view( 'platformcoin.index', compact('records', 'types','title') );
    }

    public function platformcoin_search(Request $request)
    {
        $types = config('types.platform_financial_type');
        $platformcoin_id = $request->platformcoin_id;
        $customer = $request->customer;
        $financial_type = $request->financial_type;
        $date = $request->date;
        if($platformcoin_id != null && $customer != null && $financial_type != 0 && $date != null)
        {
            $platformcoin_search = DB::table('financial_platform_coin')
                            ->join('customers', 'customers.id', 'financial_platform_coin.userid')
                            ->where([['financial_platform_coin.id', '=', $platformcoin_id], ['customers.phone', '=', $customer], ['financial_platform_coin.financial_type', '=', $financial_type]])
                            ->whereDate('financial_platform_coin.created_at', '=', $date)
                            ->orderBy('financial_platform_coin.created_at', 'desc')
                            ->select('customers.phone', 'financial_platform_coin.*')
                            ->get();
        } else {
            $platformcoin_search = DB::table('financial_platform_coin')
                            ->join('customers', 'customers.id', 'financial_platform_coin.userid')
                            ->whereDate('financial_platform_coin.created_at', '=', $date)
                            ->orwhere('financial_platform_coin.financial_type', '=', $financial_type)
                            ->orwhere('financial_platform_coin.id', '=', $platformcoin_id)
                            ->orwhere('customers.phone', '=', $customer)
                            ->orderBy('financial_platform_coin.created_at', 'desc')
                            ->select('customers.phone', 'financial_platform_coin.*')
                            ->get();
        }

        return response()->json([
            'platformcoin_search' => $platformcoin_search,
            'types' => $types,
        ]);
    }

}
