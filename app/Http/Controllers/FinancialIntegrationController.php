<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinancialIntegration;

class FinancialIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $records = FinancialIntegration::orderBy('created_at', 'desc')->paginate(20);

        $types = config('types.integration_financial_type');

        $title = '积分流水记录';

        return view( 'platformcoin.index', compact('records', 'types','title') );
    }

}
