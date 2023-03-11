<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\FinancialPlatformCoin;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FinancialPlatformCoinController extends Controller
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
    private $path_name = "/platformcoin";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 平台币流水记录
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = FinancialPlatformCoin::orderBy('created_at', 'desc')->paginate($perPage);

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
        $date = Carbon::parse($request->date)->format('Y-m-d');
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
