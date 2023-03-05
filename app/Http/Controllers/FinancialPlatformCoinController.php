<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialPlatformCoin;

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

}
