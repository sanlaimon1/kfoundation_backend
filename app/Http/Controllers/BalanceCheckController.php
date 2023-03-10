<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BalanceCheck;
use App\Models\Customer;
use App\Models\FinancialBalance;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class BalanceCheckController extends Controller
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
    private $path_name = "/withdrawal";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 余额提现审核
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 1)) {
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $records = BalanceCheck::orderBy('created_at', 'desc')->paginate($perPage);

        $types = [0 => '待审核', 1 => '通过', 2 => '拒绝'];

        $title = '余额提现审核';

        return view('withdrawal.index', compact('records', 'types', 'title'));
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
        $one = BalanceCheck::find($id);

        //状态 0 待审核 1 通过 2 拒绝
        $status = [0 => '待审核', 1 => '通过', 2 => '拒绝'];

        return view('withdrawal.show', compact('id', 'one', 'status'));
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
        $one = BalanceCheck::find($id);

        //状态 0 待审核 1 通过 2 拒绝
        $status = [0 => '待审核', 1 => '通过', 2 => '拒绝'];

        return view('withdrawal.edit', compact('id', 'one', 'status'));
    }

    /**
     * 通过提现  pass
     * set status=1  and modify customer.balance and add a record at FinancialBalance
     * add a record at Log
     * with the highest level of transcation of mysql
     */
    public function update(Request $request, string $id)
    {
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
            $one = BalanceCheck::find($id);
            $one->status = 1;
            $one->adminid = Auth::id();
            if (!$one->save())
                throw new \Exception('事务中断1');
            $balance_check = BalanceCheck::where('status', "=", 0)->get();
            Redis::set('balance_check_status', $balance_check->count());

            $username = Auth::user()->username;
            //添加管理员日志
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . '对用户' . $one->customer->phone . '的' . $one->amount . '金额的申请 审核通过';
            $newlog->ip = $request->ip();
            $newlog->route = 'withdrawal.update';
            $newlog->parameters = json_encode($request->all());
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
                throw new \Exception('事务中断2');

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

        return redirect()->route('withdrawal.show', ['withdrawal' => $id]);
    }

    /**
     * 拒绝  reject
     * set status=2 and modify customer.balance and add a record at FinancialBalance
     * add a record at Log
     * with the highest level of transcation of mysql
     */
    public function destroy(Request $request, string $id)
    {
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
            $one = BalanceCheck::find($id);
            $one->status = 2;
            $one->adminid = Auth::id();
            $one->comment = $comment;
            if (!$one->save())
                throw new \Exception('事务中断1');

            //更改用户余额
            $userid = $one->userid;
            $one_user = Customer::find($userid);
            $balance = $one_user->balance; //更改前的余额
            $one_user->balance = $balance + $one->amount;
            if (!$one_user->save())
                throw new \Exception('事务中断2');

            //添加财务记录
            $username = Auth::user()->username;
            $newfinance = new FinancialBalance;
            $newfinance->userid = $userid;
            $newfinance->amount = $one->amount;
            $newfinance->balance = $balance;
            $newfinance->direction = 1;    //加余额
            $newfinance->financial_type = 2;  //提现
            $newfinance->created_at = date('Y-m-d H:i:s');
            $newfinance->details = '管理员' . $username . '对用户' . $one_user->phone . '的' . $one->amount . '金额的余额提现申请 审核拒绝';
            $order_arr = ['withdrawal_id' => $id];
            $newfinance->extra = json_encode($order_arr);  //{"withdrawal_id": 1}
            $newfinance->after_balance = $one_user->balance;

            if (!$newfinance->save())
                throw new \Exception('事务中断3');

            //添加管理员日志
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $username . '对用户' . $one_user->phone . '的' . $one->amount . '金额的余额提现申请 审核拒绝';
            $newlog->ip = $request->ip();
            $newlog->route = 'withdrawal.destroy';
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

        return redirect()->route('withdrawal.show', ['withdrawal' => $id]);
    }
    public function withdrawal_search(Request $request)
    {
        //dd($request);
        $bid = $request->bid;
        $customer = $request->customer;
        $status = $request->status;
        $date = Carbon::parse($request->date)->format('Y-m-d');
        if($bid != null && $customer != null && $status != null && $date != null)
        {
            $withdrawal_search = DB::table('balance_check')
                            ->join('customers', 'customers.id', 'balance_check.userid')
                            ->where([['balance_check.id', '=', $bid], ['customers.phone', '=', $customer], ['balance_check.status', '=', $status]])
                            ->whereDate('balance_check.created_at', '=', $date)
                            ->orderBy('balance_check.created_at', 'desc')
                            ->select('customers.phone', 'balance_check.*')
                            ->get();


        } else {
            $withdrawal_search = DB::table('balance_check')
                            ->join('customers', 'customers.id', 'balance_check.userid')
                            ->whereDate('balance_check.created_at', '=', $date)
                            ->orwhere('balance_check.id', '=', $bid)
                            ->orwhere('customers.phone','=',$customer)
                            ->orwhere('balance_check.status','=', $status)
                            ->orderBy('balance_check.created_at', 'desc')
                            ->select('customers.phone', 'balance_check.*')
                            ->get();

        }

        return response()->json([
            'withdrawal_search' => $withdrawal_search
        ]);

    }
}
