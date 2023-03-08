<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $payments = Payment::select('pid', 'sort','show','payment_name','ptype', 'logo')->orderBy('sort','asc')->paginate(10);

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
            'level' => ['required', 'integer', 'gte:0'],
            'ptype' => ['required', 'integer', 'in:' . $ptypes_string],
            'give' => ['required', 'numeric', 'gte:0'],
            'description' => ['required', 'string', 'max:200'],
            "upload_logo.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            "crypto_qrcode.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
        ]);

        $payment_name = trim( $request->get('payment_name') );
        $sort = trim( $request->get('sort') );
        $level = trim( $request->get('level') );
        $ptype = trim( $request->get('ptype') );
        $give = trim( $request->get('give') );
        $description = trim( $request->get('description') );

        $sort = (int)$sort;
        $level = (int)$level;
        $ptype = (int)$ptype;

        $id = (int)$id;

        if($ptype == 1) {
            $request->validate([
                'crypto_link' => ['required', 'string', 'max:50'],
            ]);

            $crypto_link = trim( $request->get('crypto_link') );

            $one = Payment::find($id);
            $one->payment_name = $payment_name;
            $one->sort = $sort;
            $one->level = $level;
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
            $one->save();
            
        } else if($ptype == 4) {
            $request->validate([
                'bank' => ['required', 'string', 'max:30'],
                'bank_name' => ['required', 'string', 'max:10'],
                'bank_account' => ['required', 'string', 'between:16,19'],
            ]);

            $bank = trim( $request->get('bank') );
            $bank_name = trim( $request->get('bank_name') );
            $bank_account = trim( $request->get('bank_account') );

            $one = Payment::find($id);
            $one->payment_name = $payment_name;
            $one->sort = $sort;
            $one->level = $level;
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
            
            $one->save();

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
