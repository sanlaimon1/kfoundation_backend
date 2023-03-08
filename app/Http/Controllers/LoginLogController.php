<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LoginLog;

class LoginLogController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 查询用户登录日志
     */
    public function index()
    {
        $logs = LoginLog::orderBy('created_at', 'desc')->paginate(10);

        return view('loginlog.index', compact('logs'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $id = (int) $id;
        //查询一条记录
        $one = LoginLog::find($id);

        return view('loginlog.show', ['one'=>$one]);
    }
}
