<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class PaymentController extends Controller
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
    private $path_name = "/payment";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 支付方式列表
     */
    public function index(Request $request)
    {
        $static_url = config("app.static_url"); // 静态访问的url
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $payments = Payment::select('pid', 'sort','show','payment_name','ptype', 'logo')->orderBy('sort','asc')->paginate($perPage);

        if(Redis::exists('payments')) {
            $payment_string = Redis::get('payments');
        } else {
            $list_payment = Payment::select(['pid','payment_name','min_pay','rate','logo'])
                    ->where('show', 1)
                    ->orderBy('sort', 'asc')
                    ->get();

            $payments = [];
            foreach($list_payment as $one_payment) {
                $payments[$one_payment->pid] = [
                                'pname'=>$one_payment->payment_name,
                                'min'=>$one_payment->min_pay,
                                'rate'=>$one_payment->rate,
                                'logo'=>$static_url . $one_payment->logo,
                            ];
            }

            $payment_string = json_encode($payments);
            Redis::set('payments', $payment_string);
        }
        return view('config.payment', compact('payments'));
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
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * 编辑支付方式
     */
    public function edit(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 16) ){
            return "您没有权限访问这个路径";
        }

        if(!is_numeric($id)) {
            $arr = ['code'=>-1, 'message'=>'id必须是整数'];
            return response()->json( $arr );
        }

        $id = (int) $id;
        //查询一条记录
        $one = Payment::find($id);
        //得到分类数组
        $ptypes = config('data.payment_ways');
        //分类相应的属性
        $extra = json_decode( $one->extra, true);

        //dd( json_encode( ['crypto_qrcode'=>'/upload/qrcode.png', 'crypto_link'=>'TBjHhyfdNU2r6QSSiT5CnToXGcrPDQdUtF'] ) );
        //dd( json_encode( ['bank'=>'中国农业银行', 'bank_name'=>'操你妈', 'bank_account'=>'6228480098055446']) );

        return view('config.edit_payment', compact('one', 'id', 'ptypes', 'extra') );
    }

    /**
     * 编辑一条记录
     */
    public function update(Request $request, string $id)
    {
        $static_url = config("app.static_url"); // 静态访问的url
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }

        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        //获得支付分类的字符串
        $ptypes_array  = config('data.payment_ways');
        $arr = [];
        foreach($ptypes_array as $key=>$val) {
            $arr[] = $key;
        }
        $ptypes_string = implode(',', $arr);

        //表单验证
        $request->validate([
            'payment_name' => ['required', 'string', 'between:4,20'],
            'sort' => ['required', 'integer', 'gt:0'],
            // 'level' => ['required', 'integer', 'gte:0'],
            'ptype' => ['required', 'integer', 'in:' . $ptypes_string],
            'give' => ['required', 'numeric', 'gte:0'],
            'rate' => ['required', 'numeric', 'gte:0'],
            'description' => ['required', 'string', 'max:200'],
            "upload_logo.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            "crypto_qrcode.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
        ]);

        $payment_name = trim( $request->get('payment_name') );
        $sort = trim( $request->get('sort') );
        // $level = trim( $request->get('level') );
        $ptype = trim( $request->get('ptype') );
        $give = trim( $request->get('give') );
        $description = trim( $request->get('description') );
        $rate = trim($request->get('rate'));

        $sort = (int)$sort;
        // $level = (int)$level;
        $ptype = (int)$ptype;

        $id = (int)$id;

        if($ptype == 1) {
            $request->validate([
                'crypto_link' => ['required', 'string', 'max:50'],
            ]);

            $crypto_link = trim( $request->get('crypto_link') );

            DB::beginTransaction();
            try {
                //code...
                $one = Payment::find($id);
                $one->payment_name = $payment_name;
                $one->sort = $sort;
                // $one->level = $level;
                $one->ptype = $ptype;
                if($request->hasFile('upload_logo')){
                    $upload_logo = time().'.'.$request->upload_logo->extension();
                    $request->upload_logo->move(public_path('/images/'),$upload_logo);
                    $logo_image = '/images/'.$upload_logo;
                }else{
                    $logo_image =  $one->logo;
                }
                $one->logo = $logo_image;
                $one->give = $give;
                $one->description = $description;
                $extra_array = json_decode( $one->extra, true );
                $extra_array['crypto_link'] = $crypto_link;
                if($request->hasFile('upload_crypto_qrcode')){
                    $crypto_qrcode = time().'QR.'.$request->upload_crypto_qrcode->extension();
                    $request->upload_crypto_qrcode->move(public_path('/images/'),$crypto_qrcode);
                    $extra_array['crypto_qrcode'] = '/images/'.$crypto_qrcode;
                }else{
                    $extra_array['crypto_qrcode'] = $extra_array['crypto_qrcode'];
                }
                $one->extra = json_encode($extra_array) ;
                $one->rate = $rate;
                if(!$one->save())
                    throw new \Exception('事务中断1');

                $username = Auth::user()->username;
                $log = new Log();
                $log->adminid = Auth::id();
                $log->action = '管理员'. $username. ' 修改站内信';
                $log->ip =  $request->ip();
                $log->route = 'payment.update';
                $input = $request->all();
                $input_json = json_encode( $input );
                $log->parameters = $input_json;  // 请求参数
                $log->created_at = date('Y-m-d H:i:s');

                if(!$log->save())
                    throw new \Exception('事务中断2');

                $payment_datas = array([
                    'id' => $one->pid,
                    'show' => $one->show,
                    'sort' => $one->sort,
                    'ptype' => $one->ptype,
                    'rate' => $one->rate,
                    'mark' => $one->mark,
                    'logo' => $one->logo,
                    'give' => $one->give,
                    'description' => $one->description,
                    'extra' => $one->extra,
                    'payment_name' => $one->payment_name,
                    'min_pay' => $one->min_pay,
                ]);
                $payment_datas_json = json_encode($payment_datas);
                DB::commit();
                LogFile::channel("payment_update")->info($payment_datas_json);

                // $old_payment_redis = Redis::get("payments");
                // $list_payment = Payment::select(['pid','payment_name','min_pay','rate','logo'])
                //         ->where('show', 1)
                //         ->orderBy('sort', 'asc')
                //         ->get();
                // $payments = [];
                // foreach($list_payment as $one_payment) {
                //     $payments[$one_payment->pid] = [
                //                     'pname'=>$one_payment->payment_name,
                //                     'min'=>$one_payment->min_pay,
                //                     'rate'=>$one_payment->rate,
                //                     'logo'=>$static_url . $one_payment->logo,
                //                 ];
                // }

                // $payment_string = json_encode($payments);
                // if($old_payment_redis != $payment_string){
                //     Redis::set('payments', $payment_string);
                // }

            } catch (\Exception $e) {
                DB::rollBack();
                echo $e->getMessage();
                $message = $e->getMessage();
                LogFile::channel("payment_update_error")->error($message);
                return '添加错误，事务回滚';
            }

        } else if($ptype == 4) {
            // $request->validate([
            //     'bank' => ['required', 'string', 'max:30'],
            //     'bank_name' => ['required', 'string', 'max:10'],
            //     'bank_account' => ['required', 'string', 'between:16,19'],
            // ]);

            $bank = trim( $request->get('bank') );
            $bank_name = trim( $request->get('bank_name') );
            $bank_account = trim( $request->get('bank_account') );

            DB::beginTransaction();
            try {
                $one = Payment::find($id);
                $one->payment_name = $payment_name;
                $one->sort = $sort;
                // $one->level = $level;
                $one->ptype = $ptype;
                if($request->hasFile('upload_logo')){
                    $upload_logo = time().'.'.$request->upload_logo->extension();
                    $request->upload_logo->move(public_path('/images/'),$upload_logo);
                    $logo_image = '/images/'.$upload_logo;
                }else{
                    $logo_image =  $one->logo;
                }
                $one->logo = $logo_image;
                $one->give = $give;
                $one->description = $description;
                $extra_array = ['bank'=>$bank, 'bank_name'=>$bank_name, 'bank_account'=>$bank_account];
                $one->extra = json_encode($extra_array) ;
                $one->rate = $rate;
                if(!$one->save())
                    throw new \Exception('事务中断3');

                $username = Auth::user()->username;
                $log = new Log();
                $log->adminid = Auth::id();
                $log->action = '管理员'. $username. ' 修改站内信';
                $log->ip =  $request->ip();
                $log->route = 'payment.update';
                $input = $request->all();
                $input_json = json_encode( $input );
                $log->parameters = $input_json;  // 请求参数
                $log->created_at = date('Y-m-d H:i:s');

                if(!$log->save())
                    throw new \Exception('事务中断4');

                $payment_datas = array([
                    'id' => $one->pid,
                    'show' => $one->show,
                    'sort' => $one->sort,
                    'ptype' => $one->ptype,
                    'rate' => $one->rate,
                    'mark' => $one->mark,
                    'logo' => $one->logo,
                    'give' => $one->give,
                    'description' => $one->description,
                    'extra' => $one->extra,
                    'payment_name' => $one->payment_name,
                    'min_pay' => $one->min_pay,
                ]);
                $payment_datas_json = json_encode($payment_datas);
                DB::commit();
                LogFile::channel("payment_update")->info($payment_datas_json);

                $old_payment_redis = Redis::get("payments");
                $list_payment = Payment::select(['pid','payment_name','min_pay','rate','logo'])
                        ->where('show', 1)
                        ->orderBy('sort', 'asc')
                        ->get();
                $payments = [];
                foreach($list_payment as $one_payment) {
                    $payments[$one_payment->pid] = [
                                    'pname'=>$one_payment->payment_name,
                                    'min'=>$one_payment->min_pay,
                                    'rate'=>$one_payment->rate,
                                    'logo'=>$static_url . $one_payment->logo,
                                ];
                }

                $payment_string = json_encode($payments);
                if($old_payment_redis != $payment_string){
                    Redis::set('payments', $payment_string);
                }

            } catch (\Exception $e) {
                DB::rollBack();
                $message = $e->getMessage();
                LogFile::channel("payment_update_error")->error($message);
                //echo $e->getMessage();
                return '添加错误，事务回滚';
            }

        } else {
            return '未知类型';
        }

        return redirect()->route('payment.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
