<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\FinancialPlatformCoin;
use Illuminate\Support\Facades\DB;
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
        foreach($records as $record)
        {
            $detail = $record->details;
            $res = json_decode($detail, true);
            //如果是json正常解析
            if(json_last_error()==JSON_ERROR_NONE)
            {
                $phone = array_key_exists('phone', $res) ? $res['phone'] : '';
                $tphone = array_key_exists('tphone', $res) ? $res['tphone'] : '';
                $created_at = array_key_exists('created_at', $res) ? $res['created_at'] : '';
                $amount = array_key_exists('amount', $res) ? $res['amount'] : '';
                $addmoney = array_key_exists('addmoney',$res) ? $res['addmoney'] : '';
                $username = array_key_exists('username',$res) ? $res['username'] : '';
                $type = array_key_exists('type', $res) ? $res['type'] : '';
                $action = __($type, ['phone' => $phone, 'tphone' => $tphone, 'created_at' => $created_at,
                            'amount' => $amount, 'addmoney' => $addmoney, 'username' => $username]);
            }
            $record_datas[] = [
                'id' => $record->id,
                'userid' => $record->userid,
                'phone' => $record->customer->phone,
                'amount' => $record->amount,
                'balance' => $record->balance,
                'direction' => $record->direction,
                'financial_type' => $record->financial_type,
                'created_at' => $record->created_at,
                'details' => $action,
                'extra' => $record->extra,
                'after_balance' => $record->after_balance
            ];
        }

        $types = config('types.platform_financial_type');

        $title = '平台币流水记录';

        return view( 'platformcoin.index', compact('record_datas','records', 'types','title') );
    }

    public function platformcoin_search(Request $request)
    {
        $types = config('types.platform_financial_type');
        $platformcoin_id = $request->platformcoin_id;
        $customer = $request->customer;
        $financial_type = $request->financial_type;

        $date_string = $request->date;
        $date_string = $request->date;
        if($date_string){
            $date_parts = explode('至', $date_string);
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            $start_date = '';
            $end_date = '';
        }

        if($platformcoin_id != null && $customer != null && $financial_type != 0 && $date_string != null)
        {
            $platformcoin_search = DB::table('financial_platform_coin')
                            ->join('customers', 'customers.id', 'financial_platform_coin.userid')
                            ->where([['financial_platform_coin.id', '=', $platformcoin_id], ['customers.phone', '=', $customer], ['financial_platform_coin.financial_type', '=', $financial_type]])
                            ->whereBetween('financial_platform_coin.created_at', [$start_date, $end_date])
                            ->orderBy('financial_platform_coin.created_at', 'desc')
                            ->select('customers.phone', 'financial_platform_coin.*')
                            ->get();
        } else {
            $platformcoin_search = DB::table('financial_platform_coin')
                            ->join('customers', 'customers.id', 'financial_platform_coin.userid')
                            ->whereBetween('financial_platform_coin.created_at', [$start_date, $end_date])
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
