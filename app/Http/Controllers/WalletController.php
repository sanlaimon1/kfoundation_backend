<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;

class WalletController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 查询钱包列表
     */
    public function index()
    {
        $records = Wallet::orderBy('created_at', 'desc')->paginate(20);

        $types = config('types.client_wallet_types');

        $title = "用户钱包列表";

        return view( 'wallet.index', compact('records', 'types', 'title') );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //  delete this record and save into log , start a transcation
    }
}
