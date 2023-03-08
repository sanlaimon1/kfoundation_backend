<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\FinancialAsset;
use DB;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $records = FinancialAsset::orderBy('created_at', 'desc')->paginate(20);

        $types = config('types.asset_financial_type');

        $title = '资产流水记录';

        return view( 'financialasset.index', compact('records', 'types', 'title') );
    }

    public function asset_search(Request $request)
    {
        $types = config('types.asset_financial_type');
        $financialasset_id = $request->financialasset_id;
        $customer = $request->customer;
        $financial_type = $request->financial_type;
        $date = $request->date;
        if($financialasset_id != null && $customer != null && $financial_type != 0 && $date != null)
        {
            $asset_search = DB::table('financial_asset')
                            ->join('customers', 'customers.id', 'financial_asset.userid')
                            ->where([['financial_asset.id', '=', $financialasset_id], ['customers.phone', '=', $customer], ['financial_asset.financial_type', '=', $financial_type]])
                            ->whereDate('financial_asset.created_at', '=', $date)
                            ->orderBy('financial_asset.created_at', 'desc')
                            ->select('customers.phone', 'financial_asset.*')
                            ->get();
        } else {
            $asset_search = DB::table('financial_asset')
                            ->join('customers', 'customers.id', 'financial_asset.userid')
                            ->whereDate('financial_asset.created_at', '=', $date)
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
