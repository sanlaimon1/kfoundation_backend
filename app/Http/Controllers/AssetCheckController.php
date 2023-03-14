<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetCheck;
use App\Models\BalanceCheck;
use App\Models\Customer;
use App\Models\FinancialAsset;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class AssetCheckController extends Controller
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
    private $path_name = "/charge";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 资产充值审核
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 1)) {
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = AssetCheck::orderBy('created_at', 'desc')->paginate($perPage);

        $types = [0 => '待审核', 1 => '通过', 2 => '拒绝'];

        $title = '资产充值审核';

        return view('charge.index', compact('records', 'types', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * page of pass
     */
    public function show(string $id)
    {

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 8)) {
            return "您没有权限访问这个路径";
        }

        $id = (int)$id;
        $one = AssetCheck::find($id);

        //状态 0 待审核 1 通过 2 拒绝
        //$status = [0=>'待审核', 1=>'通过', 2=>'拒绝'];

        return view('charge.show', compact('id', 'one'));
    }

    /**
     * page of reject
     */
    public function edit(string $id)
    {

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 16)) {
            return "您没有权限访问这个路径";
        }

        $id = (int)$id;
        $one = AssetCheck::find($id);

        //状态 0 待审核 1 通过 2 拒绝
        $status = [0 => '待审核', 1 => '通过', 2 => '拒绝'];

        return view('charge.edit', compact('id', 'one', 'status'));
    }

    /**
     * 同意充值
     */
    public function update(Request $request, string $id)
    {
        if (Redis::exists("permission:".Auth::id())) 
            return "10秒内不能重复提交";

            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), 10);
            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if (!(($permission->auth2 ?? 0) & 32)) {
                return "您没有权限访问这个路径";
            }

            $id = (int)$id;
            //事务开启
            DB::beginTransaction();
            try {
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                //更改订单状态
                $one = AssetCheck::find($id);
                $one->status = 1;
                $one->adminid = Auth::id();
                if (!$one->save())
                    throw new \Exception('事务中断1');

                $asset_check = AssetCheck::where('status', "=", 0)->get();
                Redis::set('asset_check_status', $asset_check->count());

                //更改用户余额
                $userid = $one->userid;
                $one_user = Customer::find($userid);
                $asset = $one_user->asset; //更改前的余额
                $one_user->asset = $asset + $one->amount;
                if (!$one_user->save())
                    throw new \Exception('事务中断2');

                //添加财务记录
                $username = Auth::user()->username;
                $newfinance = new FinancialAsset;
                $newfinance->userid = $userid;
                $newfinance->amount = $one->amount;
                $newfinance->balance = $asset;
                $newfinance->direction = 1;    //加资产
                $newfinance->financial_type = 3;  //通过用户申请获得
                $newfinance->created_at = date('Y-m-d H:i:s');
                $newfinance->details = '管理员' . $username . '对用户' . $one_user->phone . '的' . $one->amount . '金额的资产充值申请 审核通过';
                $order_arr = ['charge_id' => $id];
                $newfinance->extra = json_encode($order_arr);  //{"charge_id": 1}
                $newfinance->after_balance = $one_user->asset;

                if (!$newfinance->save())
                    throw new \Exception('事务中断3');

                //添加管理员日志
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员' . $username . '对用户' . $one->customer->phone . '的' . $one->amount . '金额的资产充值申请 审核通过';
                $newlog->ip = $request->ip();
                $newlog->route = 'charge.update';
                $newlog->parameters = json_encode($request->all());
                $newlog->created_at = date('Y-m-d H:i:s');
                if (!$newlog->save())
                    throw new \Exception('事务中断4');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
                return $errorMessage;
                //return '审核通过错误，事务回滚';
            }

            return redirect()->route('charge.show', ['charge' => $id]);
    }

    /**
     * 拒绝充值
     */
    public function destroy(Request $request, string $id)
    {
        if (Redis::exists("permission:".Auth::id())) 
            return "10秒内不能重复提交";

            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), 10);
            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if (!(($permission->auth2 ?? 0) & 64)) {
                return "您没有权限访问这个路径";
            }

            $request->validate([
                'comment' => ['required', 'string', 'max:200'],
            ]);

            $comment = trim($request->comment);
            $id = (int)$id;
            //事务开启
            DB::beginTransaction();
            try {
                DB::statement('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
                //更改订单状态
                $one = AssetCheck::find($id);
                $one->status = 2;
                $one->adminid = Auth::id();
                $one->comment = $comment;
                if (!$one->save())
                    throw new \Exception('事务中断5');

            $asset_check = AssetCheck::where('status', "=", 0)->get();
            Redis::set('asset_check_status', $asset_check->count());

            $username = Auth::user()->username;
            //添加管理员日志
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . '对用户' . $one->customer->phone . '的' . $one->amount . '金额的资产充值申请 审核拒绝';
            $newlog->ip = $request->ip();
            $newlog->route = 'charge.destory';
            $newlog->parameters = json_encode($request->all());
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
                throw new \Exception('事务中断6');

                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $errorMessage = $e->getMessage();
                return $errorMessage;
                //return '审核通过错误，事务回滚';
            }

            return redirect()->route('charge.show', ['charge' => $id]);
    }

    public function charge_search(Request $request)
    {

        $fid = $request->fid;
        $customer = $request->customer;
        $financial_type = $request->financial_type;

        $date_string = $request->date;
        $date_parts = explode('至', $date_string);
        $start_date = trim($date_parts[0]);
        $end_date = trim($date_parts[1]);

        if($fid != null && $customer != null && $financial_type != null && $date_string != null)
        {
            $charge_search = DB::table('asset_check')
                            ->join('customers', 'customers.id', 'asset_check.userid')
                            ->where([['asset_check.id', '=', $fid], ['customers.phone', '=', $customer], ['asset_check.status', '=', $financial_type]])
                            ->whereBetween('asset_check.created_at', [$start_date, $end_date])
                            ->orderBy('asset_check.created_at', 'desc')
                            ->select('customers.phone', 'asset_check.*')
                            ->get();

        } else {
            $charge_search = DB::table('asset_check')
                            ->join('customers', 'customers.id', 'asset_check.userid')
                            ->whereBetween('asset_check.created_at', [$start_date, $end_date])
                            ->orwhere('asset_check.id', '=', $fid)
                            ->orwhere('customers.phone','=',$customer)
                            ->orwhere('asset_check.status','=', $financial_type)
                            ->orderBy('asset_check.created_at', 'desc')
                            ->select('customers.phone', 'asset_check.*')
                            ->get();
                            //dd($charge_search);

        }

        return response()->json([
            'charge_search' => $charge_search

        ]);

    }
}
