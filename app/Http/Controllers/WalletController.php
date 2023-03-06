<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Log;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
    public function destroy(Request $request,string $id)
    {
        DB::beginTransaction();
        try {
            //code...
            $wallet = Wallet::find($id);
            $customer_name = DB::table('customers')
                            ->where('customers.id','=',$wallet->userid)
                            ->select('realname')->first();
            $wallet->delete();

            $myself = Auth::user();
            $log = new Log();
            $log->adminid = $myself->id;
            $log->action = '管理员'. $myself->username. '删除用户' .$customer_name->realname. '的钱包';
            $log->ip = $this->getUserIP();
            $log->route = 'wallet.destroy';
            $input = $request->all();
            $input_json = json_encode( $input );
            $log->parameters = $input_json;  // 请求参数
            $log->created_at = date('Y-m-d H:i:s');

            $log->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            return 'error';
        }

        return redirect()->route('wallet.index');
    }

    public function wallet_search(Request $request)
    {
        $types = config('types.client_wallet_types');
        $fid = $request->fid;
        $phone = $request->phone;
        $payid = $request->payid;
        $created_at = $request->created_at;
        if($fid !=null && $phone != null &&  $payid !=null && $created_at!=null)
        {
            $search_wallet = DB::table('wallets')
                            ->join('customers','customers.id','=','wallets.userid')
                            ->whereDate('wallets.created_at','=',$created_at)
                            ->where([['wallets.id','=',$fid],['customers.phone','=',$phone],['wallets.payid','=',$payid]])
                            ->orderBy('wallets.created_at','desc')
                            ->select('wallets.*','customers.id as customerid','customers.phone as phone')
                            ->get();

        }else{
            $search_wallet = DB::table('wallets')
                            ->join('customers','customers.id','=','wallets.userid')
                            ->whereDate('wallets.created_at','=',$created_at)
                            ->orwhere('wallets.id','=',$fid)
                            ->orwhere('customers.phone','=',$phone)
                            ->orwhere('wallets.payid','=',$payid)
                            ->select('wallets.*','customers.id as customerid','customers.phone as phone')
                            ->get();

        }
        return response()->json([
            "search_wallet" => $search_wallet,
            "types" => $types
         ]);
    }

    public function getUserIP()
    {
        if (getenv('HTTP_CLIENT_IP')){
            $ip = getenv('HTTP_CLIENT_IP');
        }
        if (getenv('HTTP_X_REAL_IP'))
        {
            $ip = getenv('HTTP_X_REAL_IP');
        }
            else if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $ip = getenv('HTTP_X_FORWARDED_FOR');
                $ips = explode(',', $ip);
                $ip = $ips[0];
            }
            else if (getenv('REMOTE_ADDR'))
            {
                $ip = getenv('REMOTE_ADDR');
            }
            else
            {
                $ip = '0.0.0.0';
            }
            return $ip;
    }
}
