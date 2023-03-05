<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialBalance;

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

}
