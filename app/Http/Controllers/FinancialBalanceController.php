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
        foreach($records as $record)
        {
            $detail = $record->details;
            $res = json_decode($detail, true);
            //如果是json正常解析
            if(json_last_error()==JSON_ERROR_NONE)
            {
                $phone = array_key_exists('phone', $res) ? $res['phone'] : '';
                $itemid = array_key_exists('itemid',$res) ? $res['itemid'] : '';
                $project_name = array_key_exists('project_name', $res) ? $res['project_name'] : '';
                $production_name = array_key_exists('production_name', $res) ? $res['production_name'] : '';
                $total = array_key_exists('total',$res) ? $res['total'] : '';
                $this_fee = array_key_exists('this_fee', $res) ? $res['this_fee'] : '';
                $money = array_key_exists('money', $res) ? $res['money'] : '';
                $amount = array_key_exists('amount', $res) ? $res['amount'] : '';
                $addmoney = array_key_exists('addmoney', $res) ? $res['addmoney'] : '';
                $type = array_key_exists('type', $res) ? $res['type'] : '';
                $action = __($type, ['phone' => $phone, 'itemid' => $itemid, 'project_name' => $project_name,'production_name' => $production_name,'total' => $total, 'this_fee' => $this_fee, 'money' => $money, 'amount' => $amount, 'addmoney' => $addmoney
                ]);
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

        $types = config('types.balance_financial_type');

        $title = '余额流水记录';

        return view( 'financialbalance.index', compact('record_datas','records', 'types', 'title') );
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
