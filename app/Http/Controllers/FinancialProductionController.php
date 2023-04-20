<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Models\Permission;
use App\Models\Log;
use App\Models\Customer;
use App\Models\FinancialProductions;
use Illuminate\Support\Facades\Log as LogFile;

class FinancialProductionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $path_name = "/financial_productions";

    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $records = FinancialProductions::where('status',1)
                    ->orderBy('created_at', 'desc')->paginate($perPage);
        return view('financial_productions.index',compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 2) ){
            return "您没有权限访问这个路径";
        }

        $userid_data = Customer::where('identity', 1)
                            ->orWhere('identity', 2)
                            ->select('customers.id')
                            ->get();
        return view('financial_productions.create',compact('userid_data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Redis::exists("permission:".Auth::id())){
            $arr = ['code'=>-1, 'message'=> config('app.redis_second'). '秒内不能重复提交'];
            return json_encode( $arr );
        }


        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 4) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'production_name' => ['required','string','max:45'],
            'userid' => ['required','integer'],
            'buy_price' => ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{0,2})?$/'],
            'sell_price' => ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{0,2})?$/'],
            'days' => ['required', 'integer', 'gt:0'],
            'status' => ['required', 'integer'],
            'description' => ['required','string'],
            'fee' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            'max_times' => ['required', 'integer'],
            'fake_process' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            'increment_process' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            'lang' => ['required'],
            'picture.*' => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
        ]);

        $production_name = trim( $request->get('production_name'));
        $userid = trim( $request->get('userid') );
        $buy_price = trim( $request->get('buy_price') );
        $sell_price = trim( $request->get('sell_price') );
        $days = trim( $request->get('days') );
        $status =trim( $request->get('status'));
        $description = trim( $request->get('description'));
        $fee = trim( $request->get('fee') );
        $max_times = trim( $request->get('max_times'));
        $fake_process = trim($request->get('fake_process'));
        $increment_process = trim($request->get('increment_process'));
        $lang = trim($request->get('lang'));

        $days = (int)$days;
        $userid = (int)$userid;
        $max_times = (int)$max_times;

        if($request->hasFile('picture'))
        {
            $get_images = time().'.'.$request->picture->extension();
            $request->picture->move(public_path('/financialProduction_img/'),$get_images);
            $res_images = '/financialProduction_img/'.$get_images;

        }

        DB::beginTransaction();
        try{
            $newFinancialProduct = new FinancialProductions;
            $newFinancialProduct->production_name = $production_name;
            $newFinancialProduct->userid = $userid;
            $newFinancialProduct->buy_price = $buy_price;
            $newFinancialProduct->sell_price = $sell_price;
            $newFinancialProduct->days = $days;
            $newFinancialProduct->status = $status;
            $newFinancialProduct->description = $description;
            $newFinancialProduct->fee = $fee;
            $newFinancialProduct->max_times = $max_times;
            $newFinancialProduct->fake_process = $fake_process;
            $newFinancialProduct->increment_process = $increment_process;
            $newFinancialProduct->lang = $lang;
            $newFinancialProduct->picture = $res_images;
            $newFinancialProduct->created_at = date('Y-m-d H:i:s');

            if(!$newFinancialProduct->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '创建交易所商品';
            $newlog->ip = $request->ip();
            $newlog->route = 'financial_productions.store';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');
            $financial_production = array(
                'id' => $newFinancialProduct->id,
                'production_name' => $newFinancialProduct->production_name,
                'userid' => $newFinancialProduct->userid,
                'buy_price' => $newFinancialProduct->buy_price,
                'sell_price' => $newFinancialProduct->sell_price,
                'days' => $newFinancialProduct->days,
                'status' => $newFinancialProduct->status,
                'description' => $newFinancialProduct->description,
                'fee' => $newFinancialProduct->fee,
                'max_times' => $newFinancialProduct->max_times,
                'fake_process' => $newFinancialProduct->fake_process,
                'increment_process' => $newFinancialProduct->increment_process,
                'lang' => $newFinancialProduct->lang,
                'picture' => $newFinancialProduct->picture,
                'created_at' => $newFinancialProduct->created_at,
                'updated_at' => $newFinancialProduct->updated_at,
            );
            $financial_production_json = json_encode($financial_production);
            DB::commit();
            LogFile::channel('financial_produstion_store')->info($financial_production_json);
        }catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("financial_produstion_store_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('financial_productions.index');

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 16) ){
            return "您没有权限访问这个路径";
        }

        $userid_data = Customer::where('identity', 1)
                        ->orWhere('identity', 2)
                        ->select('customers.id')
                        ->get();
        $financial_product = FinancialProductions::find($id);
        return view('financial_productions.edit',compact('userid_data','financial_product'));
    }

    /**
     * Update the specified resource in storage.
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
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'production_name' => ['required','string','max:45'],
            'userid' => ['required','integer'],
            'buy_price' => ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{0,2})?$/'],
            'sell_price' => ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{0,2})?$/'],
            'days' => ['required', 'integer', 'gt:0'],
            'status' => ['required', 'integer'],
            'description' => ['required','string'],
            'fee' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            'max_times' => ['required', 'integer'],
            'fake_process' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            'increment_process' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            'lang' => ['required'],
            'picture.*' => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
        ]);

        $production_name = trim( $request->get('production_name'));
        $userid = trim( $request->get('userid') );
        $buy_price = trim( $request->get('buy_price') );
        $sell_price = trim( $request->get('sell_price') );
        $days = trim( $request->get('days') );
        $status =trim( $request->get('status'));
        $description = trim( $request->get('description'));
        $fee = trim( $request->get('fee') );
        $max_times = trim( $request->get('max_times'));
        $fake_process = trim($request->get('fake_process'));
        $increment_process = trim($request->get('increment_process'));
        $lang = trim($request->get('lang'));

        $days = (int)$days;
        $userid = (int)$userid;
        $max_times = (int)$max_times;

        $onefinancial_product = FinancialProductions::find($id);

        if($request->hasFile('picture'))
        {
            $get_images = time().'.'.$request->picture->extension();
            $request->picture->move(public_path('/financialProduction_img/'),$get_images);
            $res_images = '/financialProduction_img/'.$get_images;
        }else{
            $res_images = $onefinancial_product->picture;
        }

        DB::beginTransaction();
        try{
            $onefinancial_product->production_name = $production_name;
            $onefinancial_product->userid = $userid;
            $onefinancial_product->buy_price = $buy_price;
            $onefinancial_product->sell_price = $sell_price;
            $onefinancial_product->days = $days;
            $onefinancial_product->status = $status;
            $onefinancial_product->description = $description;
            $onefinancial_product->fee = $fee;
            $onefinancial_product->max_times = $max_times;
            $onefinancial_product->fake_process = $fake_process;
            $onefinancial_product->increment_process = $increment_process;
            $onefinancial_product->lang = $lang;
            $onefinancial_product->picture = $res_images;
            $onefinancial_product->updated_at = date('Y-m-d H:i:s');

            if(!$onefinancial_product->update())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '修改交易所商品';
            $newlog->ip = $request->ip();
            $newlog->route = 'financial_productions.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');
            
            $financial_production = array(
                'id' => $onefinancial_product->id,
                'production_name' => $onefinancial_product->production_name,
                'userid' => $onefinancial_product->userid,
                'buy_price' => $onefinancial_product->buy_price,
                'sell_price' => $onefinancial_product->sell_price,
                'days' => $onefinancial_product->days,
                'status' => $onefinancial_product->status,
                'description' => $onefinancial_product->description,
                'fee' => $onefinancial_product->fee,
                'max_times' => $onefinancial_product->max_times,
                'fake_process' => $onefinancial_product->fake_process,
                'increment_process' => $onefinancial_product->increment_process,
                'lang' => $onefinancial_product->lang,
                'picture' => $onefinancial_product->picture,
                'created_at' => $onefinancial_product->created_at,
                'updated_at' => $onefinancial_product->updated_at,
            );
            $financial_production_json = json_encode($financial_production);
            DB::commit();
            LogFile::channel('financial_produstion_update')->info($financial_production_json);
        }catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("financial_produstion_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('financial_productions.index');
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
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }

        DB::beginTransaction();
        try
        {
            $financial_production = FinancialProductions::find($id);
            $financial_production->status = 0;
            if(!$financial_production->update())
                throw new \Exception('事务中断2');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '删除交易所商品';
            $newlog->ip = $request->ip();
            $newlog->route = 'financial_production.delete';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');
            $financial_production = array(
                'id' => $financial_production->id,
                'production_name' => $financial_production->production_name,
                'status' => $financial_production->status
            ); 
            $financial_production_json = json_encode($financial_production);
            DB::commit();
            LogFile::channel('financial_produstion_destroy')->info($financial_production_json);
        }catch (\Exception $e) {
            DB::rollBack();
            $errorMessage = $e->getMessage();
            LogFile::channel('financial_produstion_destroy_error')->error($errorMessage);
            return '添加错误，事务回滚';
        }

        return redirect()->route('financial_productions.index');
    }
}
