<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetCheck;
use App\Models\BalanceCheck;
use App\Models\Customer;
use App\Models\CustomerExtra;
use App\Models\FinancialAsset;
use App\Models\FinancialBalance;
use App\Models\Level;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\TeamExtra;
use App\Models\Teamlevel;
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
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));
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
                if($one->status!=0)
                    throw new \Exception('订单无需审核');
                $one->status = 1;
                $one->adminid = Auth::id();
                $is_help = $request->get('is_help');
                if($is_help==='1')
                {
                    $is_help = (int)$is_help;
                }
                $one->is_help = 1;
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

                //10, 维护团队总充值字段的数据 就是本人总提现
            $one_extra = CustomerExtra::where('userid', $userid)->first();
           
            $one_extra->charge = $one_extra->charge + $one->amount;
            if (!$one_extra->save())
                throw new \Exception('事务中断5');
            
            //5，检测会员等级是否升级
            $total_recharge = $one_extra->charge;  //个人总充值
            $level_id = $one_user->level_id;       //当前会员等级
            $max_level_id = Level::where('accumulative_amount', '>=',0)->max('level_id');
            //若是该会员已经是最大等级了, 无需升级
            if($level_id!=$max_level_id) {
                //查询下一个等级
                $next_level = Level::where('level_id', '>', $level_id)->orderBy('level_id', 'asc')->first();
                $next_accumulative_amount = $next_level->accumulative_amount;  //查询高一个等级的 累计充值金额的阈值
                if($total_recharge >= $next_accumulative_amount) {
                    //升级会员个人等级
                    $one_user->level_id = $next_level->level_id;
                    if (!$one_user->save())
                        throw new \Exception('事务中断6');
                }
            }

            //11, 维护团队总充值字段的数据
            $team_extra = TeamExtra::where('userid', $userid)->first();
            $team_extra->charge_total = $team_extra->charge_total + $one->amount;
            if (!$team_extra->save())
                throw new \Exception('事务中断7');

            //6，检测本级团队等级是否升级
            //得到用户总数   下级团队长总数   团队总充值数
            $team_members = $team_extra->team_members;
            $sub_leaders = $team_extra->leaders;
            $charge_total = $team_extra->charge_total;
            //查询下一个团队级别
            //获得高一级的团队信息
            $higher_team = Teamlevel::where( 'tid', '>=', $one_user->team_id )->orderBy('tid', 'asc')->first();
            //直推会员得达到15个，直推的会员里得有2个以上的会员跟自己团队级别相同，累计充值金额得达到对应的等级
            $spread_members_num = $higher_team->spread_members_num;  //直推会员数量限制
            $spread_leaders_num = $higher_team->spread_leaders_num;  //直推会员里跟自己同级的
            $accumulative_amount = $higher_team->accumulative_amount;   //累计奖金界限
           
            if( ($team_members>$spread_members_num) && ($sub_leaders>$spread_leaders_num) && ($charge_total>$accumulative_amount) )
            {
                $one_user->team_id = $higher_team->tid;
                if( !$one_user->save() )
                    throw new \Exception('事务中断8');
            }
            //7，检测本级团队是否获得团队奖   奖励到余额
            $current_team = Teamlevel::find( $one_user->team_id );

            if($current_team->is_given===1) {
                $team_award = $current_team->team_award;
                $award_amount = round($one->amount * $team_award / 100, 2);
                $after_balance = $one_user->balance + $award_amount;
                
                //添加余额流水记录  系统团队奖励
                $one_financial_balance = new FinancialBalance();
                $one_financial_balance->userid = $one_user->id;
                $one_financial_balance->amount = $award_amount;
                $one_financial_balance->balance = $one_user->balance;
                $one_financial_balance->direction = 1;
                $one_financial_balance->financial_type = 9;
                $one_financial_balance->created_at = date('Y-m-d H:i:s');
                $one_financial_balance->details = '得到团队充值奖励:' . $award_amount;
                $one_financial_balance->after_balance = $after_balance;
                if( !$one_financial_balance->save() )
                    throw new \Exception('事务中断9');
                
                $one_user->balance = $after_balance;
                if( !$one_user->save() )
                    throw new \Exception('事务中断10');
            }
            
            
            //先获得上级的id
            $parent_id = $one_user->parent_id;
            //8，检测上级团队等级是否升级
            $parent_user = Customer::find( $parent_id );
            $parent_team_extra = TeamExtra::where('userid', $parent_id)->first();
            $parent_team_extra->charge = $parent_team_extra->charge_total + $one->amount;
            if (!$team_extra->save())
                throw new \Exception('事务中断11');
            //得到用户总数   下级团队长总数   团队总充值数
            $team_members2 = $parent_team_extra->team_members;
            $sub_leaders2 = $parent_team_extra->leaders;
            $charge_total2 = $parent_team_extra->charge_total;
            //获得高一级的团队信息
            $higher_team2 = Teamlevel::where( 'tid', '>=', $parent_user->team_id )->orderBy('tid', 'asc')->first();
           
            //直推会员得达到15个，直推的会员里得有2个以上的会员跟自己团队级别相同，累计充值金额得达到对应的等级
            $spread_members_num2 = $higher_team2->spread_members_num;  //直推会员数量限制
            $spread_leaders_num2 = $higher_team2->spread_leaders_num;  //直推会员里跟自己同级的
            $accumulative_amount2 = $higher_team2->accumulative_amount;   //累计奖金界限
            if( ($team_members2>$spread_members_num2) && ($sub_leaders2>$spread_leaders_num2) && ($charge_total2>$accumulative_amount2) )
            {
                $parent_user->team_id = $higher_team2->tid;
                if( !$parent_user->save() )
                    throw new \Exception('事务中断12');
            }

            //9，检测直属上级用户的团队是否获得团队奖  奖励到余额
            $parent_team = Teamlevel::find( $parent_user->team_id );
            if($parent_team->is_given===1) {
                $team_award = $parent_team->team_award;
                $award_amount2 = round($one->amount * $team_award / 100, 2);
                $after_balance2 = $parent_user->balance + $award_amount2;

                //添加余额流水记录  系统团队奖励
                $one_financial_balance2 = new FinancialBalance;
                $one_financial_balance2->userid = $parent_user->id;
                $one_financial_balance2->amount = $award_amount2;
                $one_financial_balance2->balance = $parent_user->balance;
                $one_financial_balance2->direction = 1;
                $one_financial_balance2->financial_type = 9;
                $one_financial_balance2->created_at = date('Y-m-d H:i:s');
                $one_financial_balance2->details = '得到团队充值奖励:' . $award_amount2;
                $one_financial_balance2->after_balance = $after_balance2;
                if( !$one_financial_balance2->save() )
                    throw new \Exception('事务中断13');
                
                $parent_user->balance = $after_balance2;
                if( !$parent_user->save() )
                    throw new \Exception('事务中断14');
            }

            //10, 上级所有的总充值字段都要更新
            $parent_user_extra = CustomerExtra::where('userid', $parent_team_extra->userid)->first();

            $level_ids = $parent_user_extra->level_ids;
            if($level_ids !== '0')
            {
                $level_ids = ltrim ($level_ids,'0');    //取消开头的 0
                $level_ids = ltrim ($level_ids,',');    //取消开头的 , 
                //更新上级附加表  执行sql
                $affected_rows = DB::update('update team_extra set charge_total=charge_total+' . $one->amount . ' where userid in ( ' . $level_ids . ' )');
                if($affected_rows<0) {
                    throw new \Exception('事务中断15');
                }
            }

            //11, 异步检测所有上级团队的升级状态
                
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
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));
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
