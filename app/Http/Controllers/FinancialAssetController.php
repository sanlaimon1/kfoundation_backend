<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\FinancialAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class FinancialAssetController extends Controller
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
    private $path_name = "/asset";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 资产流水记录
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = FinancialAsset::orderBy('created_at', 'desc')->paginate($perPage);
        //rebuild datas
        $record_datas = [];
        foreach($records as $record)
        {
            $detail = $record->details;
            $res = json_decode($detail, true);
            //如果是json正常解析
            if(json_last_error()==JSON_ERROR_NONE)
            {
                $phone = array_key_exists('phone', $res) ? $res['phone'] : '';
                $pid = array_key_exists('pid',$res) ? $res['pid'] : '';
                $project_name = array_key_exists('project_name', $res) ? $res['project_name'] : '';
                $type = array_key_exists('type', $res) ? $res['type'] : '';
                $action = __($type, ['phone'=>$phone, 'pid' => $pid, 'project_name' => $project_name]);
            }
            $record_datas[] = [
                'id' => $record->id,
                'userid' => $record->userid,
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
        $types = config('types.asset_financial_type');

        $title = '资产流水记录';

        return view( 'financialasset.index', compact('record_datas', 'types', 'title') );
    }

    public function asset_search(Request $request)
    {
        $types = config('types.asset_financial_type');
        $financialasset_id = $request->financialasset_id;
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

        if($financialasset_id != null && $customer != null && $financial_type != 0 && $date_string != null)
        {
            $asset_search = DB::table('financial_asset')
                            ->join('customers', 'customers.id', 'financial_asset.userid')
                            ->where([['financial_asset.id', '=', $financialasset_id], ['customers.phone', '=', $customer], ['financial_asset.financial_type', '=', $financial_type]])
                            ->whereBetween('financial_asset.created_at', [$start_date, $end_date])
                            ->orderBy('financial_asset.created_at', 'desc')
                            ->select('customers.phone', 'financial_asset.*')
                            ->get();
        } else {
            $asset_search = DB::table('financial_asset')
                            ->join('customers', 'customers.id', 'financial_asset.userid')
                            ->whereBetween('financial_asset.created_at', [$start_date, $end_date])
                            ->orwhere('financial_asset.financial_type', '=', $financial_type)
                            ->orwhere('financial_asset.id', '=', $financialasset_id)
                            ->orwhere('customers.phone', '=', $customer)
                            ->orderBy('financial_asset.created_at', 'desc')
                            ->select('customers.phone', 'financial_asset.*')
                            ->get();
        }

        return response()->json([
            'asset_search' => $asset_search,
            'types' => $types,
        ]);
    }

}
