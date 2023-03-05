<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialAsset;

class FinancialAssetController extends Controller
{
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
        $records = FinancialAsset::orderBy('created_at', 'desc')->paginate(20);

        $types = config('types.asset_financial_type');

        $title = '资产流水记录';

        return view( 'financialasset.index', compact('records', 'types', 'title') );
    }

}
