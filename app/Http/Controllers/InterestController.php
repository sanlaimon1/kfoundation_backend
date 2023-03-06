<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Interest;

class InterestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 返息明细列表
     */
    public function index()
    {
        $records = Interest::orderBy('refund_time', 'desc')->orderBy('created_at', 'desc')->paginate(20);

        $title = "返息明细";

        return view( 'interest.index', compact('records', 'title') );
    }

    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

}
