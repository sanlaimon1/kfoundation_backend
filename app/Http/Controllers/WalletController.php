<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Log;
use App\Models\Customer;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Redis;

class WalletController extends Controller
{

    private $path_name = "/wallet";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 查询钱包列表
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        // $uid = Customer::;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = Wallet::orderBy('created_at', 'desc')->paginate($perPage);

        $types = config('types.client_wallet_types');

        $title = "用户钱包列表";

        $mywallets_string = $mywallets_dropdownlist_string = '';
        if(Redis::exists('mywallets')) {
            $mywallets_string = Redis::get('mywallets');
        } else {
            $lists = Wallet::select(['id','payid','address','realname'])
                        // ->where('userid', $uid)
                        ->orderBy('created_at', 'desc')
                        ->get();

            $mywallets = $mywallets_dropdownlist = [];
            foreach($lists as $one_wallet) {
                $mywallets[ $one_wallet->id ] = [
                                'payid'=>$one_wallet->payid,
                                'payment_name'=>$one_wallet->payment->payment_name,
                                'address'=>$one_wallet->address,
                                'realname'=>$one_wallet->realname,
                                'rate'=>$one_wallet->payment->rate,
                            ];

                $mywallets_dropdownlist[] = [
                                'id'=>$one_wallet->id, 'payment_name'=>$one_wallet->payment->payment_name,
                            ];
            }
            $mywallets_string = json_encode($mywallets);
            Redis::set('mywallets', $mywallets_string);
            $mywallets_dropdownlist_string = json_encode($mywallets_dropdownlist);
            Redis::set('mywallets_dropdownlist', $mywallets_dropdownlist_string);
        }
        return view( 'wallet.index', compact('records', 'types', 'title') );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $uid = Auth::user()->id;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }

        DB::beginTransaction();
        try {
            //code...
            $wallet = Wallet::find($id);
            $customer_name = DB::table('customers')
                            ->where('customers.id','=',$wallet->userid)
                            ->select('realname')->first();
            if(!$wallet->delete())
                throw new \Exception('事务中断1');

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

            if(!$log->save())
                throw new \Exception('事务中断2');
            DB::commit();

            $mywallets_string = $mywallets_dropdownlist_string = '';
            $old_wallet_redis = Redis::get('mywallet');
            $lists = Wallet::select(['id','payid','address','realname'])
                        // ->where('userid', $uid)
                        ->orderBy('created_at', 'desc')
                        ->get();

            $mywallets = $mywallets_dropdownlist = [];
            foreach($lists as $one_wallet) {
                $mywallets[ $one_wallet->id ] = [
                                'payid'=>$one_wallet->payid,
                                'payment_name'=>$one_wallet->payment->payment_name,
                                'address'=>$one_wallet->address,
                                'realname'=>$one_wallet->realname,
                                'rate'=>$one_wallet->payment->rate,
                            ];

                $mywallets_dropdownlist[] = [
                                'id'=>$one_wallet->id, 'payment_name'=>$one_wallet->payment->payment_name,
                            ];
            }
            $mywallets_string = json_encode($mywallets);
            if($old_wallet_redis != $mywallets_string){
                Redis::set('mywallets', $mywallets_string);
                $mywallets_dropdownlist_string = json_encode($mywallets_dropdownlist);
                Redis::set('mywallets_dropdownlist', $mywallets_dropdownlist_string);
            }

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
        $date_string = $request->created_at;
        $date_parts = explode('至', $date_string);
        $start_date = trim($date_parts[0]);
        $end_date = trim($date_parts[1]);


        if($fid !=null && $phone != null &&  $payid !=null && $date_string!=null)
        {
            $search_wallet = DB::table('wallets')
                            ->join('customers','customers.id','=','wallets.userid')
                            ->whereBetween('wallets.created_at', [$start_date, $end_date])
                            ->where([['wallets.id','=',$fid],['customers.phone','=',$phone],['wallets.payid','=',$payid]])
                            ->orderBy('wallets.created_at','desc')
                            ->select('wallets.*','customers.id as customerid','customers.phone as phone')
                            ->get();

        }else{
            $search_wallet = DB::table('wallets')
                            ->join('customers','customers.id','=','wallets.userid')
                            ->whereBetween('wallets.created_at', [$start_date, $end_date])
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
