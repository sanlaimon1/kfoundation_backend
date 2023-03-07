<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 客户列表
     */
    public function index()
    {
        $records = Customer::orderBy('created_at', 'desc')->paginate(20);

        $title = "会员列表";

        return view( 'customer.index', compact('records', 'title') );
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
        $customer = Customer::find($id);
        return view('customer.show',compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $onecustomer = Customer::find($id);
        return view('customer.edit', compact('onecustomer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'phone' => ['required', 'string', 'between:1,20'],
            'realname' => ['required','string','between:1,45'],
            'asset' => ['required'],
            'balance' => ['required'],
            'integration' => ['required', 'integer', 'gte:0'],
            'platform_coin' => ['required'],
        ]);

        DB::beginTransaction();
        try{
            $phone = trim($request->phone);
            $realname = trim($request->realname);
            $asset = trim($request->asset);
            $balance = trim($request->balance);
            $integration = trim($request->integration);
            $platform_coin = trim($request->platform_coin);

            $customer = Customer::find($id);
            $customer->phone = $phone;
            $customer->realname = $realname;
            $customer->asset = $asset;
            $customer->balance = $balance;
            $customer->integration = $integration;
            $customer->platform_coin = $platform_coin;
            $customer->updated_at = date('Y-m-d H:i:s');
            $customer->save();

            $myself = Auth::user();
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员' . $myself->username . ' 更新客户数据';
            $newlog->ip = $request->ip();
            $newlog->route = 'customer.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            $newlog->save();

            DB::commit();
        }catch (\Exception $e) {

            DB::rollback();

            return '修改错误，事务回滚';
        }

        return redirect()->route('customer.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function customer_search(Request $request)
    {
        $fid = $request->fid;
        $phone = $request->phone;
        $created_at = $request->created_at;
        if($fid !=null && $phone != null && $created_at!=null)
        {
            $search_customer = DB::table('customers')
                            ->whereDate('customers.created_at','=',$created_at)
                            ->where([['customers.id','=',$fid],['customers.phone','=',$phone]])
                            ->orderBy('customers.created_at','desc')
                            ->select('customers.*')
                            ->get();
        }else{
            $search_customer = DB::table('customers')
                            ->whereDate('customers.created_at','=',$created_at)
                            ->orwhere('customers.id','=',$fid)
                            ->orwhere('customers.phone','=',$phone)
                            ->orderBy('customers.created_at','desc')
                            ->select('customers.*')
                            ->get();
        }

        return response()->json([
            "search_customer" => $search_customer
        ]);
    }
}
