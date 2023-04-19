<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TradeGoods;
use App\Models\Permission;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log as LogFile;

class TradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $path_name = "/trade";

    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $trade_goods = TradeGoods::orderBy('created_at', 'desc')->paginate($perPage);
        return view('trade/index',compact('trade_goods'));
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

        $trade_goods = TradeGoods::select('id','goods_name')
                        ->where('next_id',0)->get();
        return view('trade/create',compact('trade_goods'));
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
            'goods_name' => ['required', 'string', 'max:45'],
            'days' => ['required', 'integer', 'gt:0'],
            'price' => ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{0,2})?$/'],
            'fee' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            "images.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            'next_id' => ['required', 'integer', 'gte:0'],
            'content' => ['required'],
            'selling_price' => ['required','numeric'],
            'step' => ['required', 'integer'],
            'show' => ['required', 'integer'],
            'is_over' => ['required', 'integer']
        ]);

        $goods_name = trim( $request->get('goods_name') );
        $days = trim( $request->get('days') );
        $price = trim( $request->get('price') );
        $fee = trim( $request->get('fee') );
        $next_id = trim( $request->get('next_id') );
        $description = trim(htmlspecialchars($request->get('content')));
        $selling_price = trim($request->get('selling_price'));
        $step = trim($request->get('step'));
        $show = trim($request->get('show'));
        $is_over = trim($request->get('is_over'));

        $days = (int)$days;
        $next_id = (int)$next_id;
        $step = (int)$step;
        $show = (int)$show;
        $is_over = (int)$is_over;

        if($request->hasFile('images'))
        {
            $get_images = time().'.'.$request->images->extension();
            $request->images->move(public_path('/trade_images/'),$get_images);
            $res_images = '/trade_images/'.$get_images;

        }

        if($is_over == 1){
            $next_id = 0;
        }

        DB::beginTransaction();
        try {
            //code...
            $newtrade_goods = new TradeGoods;
            $newtrade_goods->goods_name = $goods_name;
            $newtrade_goods->days = $days;
            $newtrade_goods->price = $price;
            $newtrade_goods->fee = $fee;
            $newtrade_goods->images = $res_images;
            $newtrade_goods->next_id = $next_id;
            $newtrade_goods->description = $description;
            $newtrade_goods->selling_price = $selling_price;
            $newtrade_goods->step = $step;
            $newtrade_goods->show = $show;
            $newtrade_goods->is_over = $is_over;
            $newtrade_goods->created_at = date('Y-m-d H:i:s');

            if(!$newtrade_goods->save())
                throw new \Exception('事务中断1');

            $trade = array(
                'id' => $newtrade_goods->id,
                'goods_name' => $goods_name,
                'days' => $days,
                'price' => $price,
                'fee' => $fee,
                'images' => $res_images,
                'next_id' => $next_id,
                'description' => $description,
                'selling_price' => $selling_price,
                'step' => $step,
                'show'=> $show,
                'is_over' => $is_over,
                'created_at' => $newtrade_goods->created_at,
            );

            $trade_json = json_encode($trade);

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '创建交易所';
            $newlog->ip = $request->ip();
            $newlog->route = 'trade_goods.store';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("trade_store")->info($trade_json);

        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("trade_store_error")->error($message);
            //echo $e->getMessage();
            return '添加错误，事务回滚';
        }

        return redirect()->route('trade.index');

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

        $trade_goods = TradeGoods::find($id);
        $nextid_data = TradeGoods::select('id')
                        ->where('next_id',0)
                        ->where('id','!=',$id)
                        ->get();
        return view('trade.edit',compact('trade_goods','nextid_data'));
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
            'goods_name' => ['required', 'string', 'max:45'],
            'days' => ['required', 'integer', 'gt:0'],
            'price' => ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{0,2})?$/'],
            'fee' => ['required', 'numeric', 'regex:/^\d{1,5}(\.\d{0,2})?$/'],
            "images.*" => 'required|image|mimes:jpg,png,jpeg,bmp,webp',
            'next_id' => ['required', 'integer', 'gte:0'],
            'content' => ['required'],
            'selling_price' => ['required','numeric'],
            'step' => ['required', 'integer'],
            'show' => ['required', 'integer'],
            'is_over' => ['required', 'integer']
        ]);

        $goods_name = trim( $request->get('goods_name') );
        $days = trim( $request->get('days') );
        $price = trim( $request->get('price') );
        $fee = trim( $request->get('fee') );
        $next_id = trim( $request->get('next_id') );
        $description = trim(htmlspecialchars($request->get('content')));
        $selling_price = trim($request->get('selling_price'));
        $step = trim($request->get('step'));
        $show = trim($request->get('show'));
        $is_over = trim($request->get('is_over'));

        $days = (int)$days;
        $next_id = (int)$next_id;
        $step = (int)$step;
        $show = (int)$show;
        $is_over = (int)$is_over;

        $newtrade_goods = TradeGoods::find($id);

        if($request->hasFile('images'))
        {
            $get_images = time().'.'.$request->images->extension();
            $request->images->move(public_path('/trade_images/'),$get_images);
            $res_images = '/trade_images/'.$get_images;
        }else{
            $res_images = $newtrade_goods->litpic;
        }

        if($is_over == 1){
            $next_id = 0;
        }

        DB::beginTransaction();
        try {
            //code...
            $newtrade_goods->goods_name = $goods_name;
            $newtrade_goods->days = $days;
            $newtrade_goods->price = $price;
            $newtrade_goods->fee = $fee;
            $newtrade_goods->images = $res_images;
            $newtrade_goods->next_id = $next_id;
            $newtrade_goods->description = $description;
            $newtrade_goods->selling_price = $selling_price;
            $newtrade_goods->step = $step;
            $newtrade_goods->show = $show;
            $newtrade_goods->is_over = $is_over;
            $newtrade_goods->created_at = date('Y-m-d H:i:s');

            if(!$newtrade_goods->save())
                throw new \Exception('事务中断1');

            $trade = array(
                'id' => $id,
                'goods_name' => $goods_name,
                'days' => $days,
                'price' => $price,
                'fee' => $fee,
                'images' => $res_images,
                'next_id' => $next_id,
                'description' => $description,
                'selling_price' => $selling_price,
                'step' => $step,
                'show'=> $show,
                'is_over' => $is_over,
                'created_at' => $newtrade_goods->created_at,
            );

            $trade_json = json_encode($trade);

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '修改交易所';
            $newlog->ip = $request->ip();
            $newlog->route = 'trade_goods.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("trade_update")->info($trade_json);

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("trade_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('trade.index');

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
            $trade_goods = TradeGoods::find($id);

            $trade = array(
                'id' => $id,
                'goods_name' => $trade_goods->goods_name,
                'days' => $trade_goods->days,
                'price' => $trade_goods->price,
                'fee' => $trade_goods->fee,
                'images' => $trade_goods->res_images,
                'next_id' => $trade_goods->next_id,
                'description' => $trade_goods->description,
                'selling_price' => $trade_goods->selling_price,
                'step' => $trade_goods->step,
                'show'=> $trade_goods->show,
                'is_over' => $trade_goods->is_over,
                'created_at' => $trade_goods->created_at,
            );

            $trade_json = json_encode($trade);

            if(!$trade_goods->delete())
                throw new \Exception('事务中断2');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. '删除交易所';
            $newlog->ip = $request->ip();
            $newlog->route = 'trade_goods.delete';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("trade_destroy")->info($trade_json);

        }catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("trade_destroy_error")->error($message);
            //echo $e->getMessage();
            return '添加错误，事务回滚';
        }

        return redirect()->route('trade.index');
    }

    public function product_search(Request $request)
    {

        $goods_name = $request->goods_name;
        $is_over = $request->is_over;
        $show = $request->show;
        $date_string = $request->created_at;
        $date_parts = explode('至', $date_string);
        if (count($date_parts) == 2) {
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            // Handle the case where $date_string doesn't include the '至' separator
            $start_date = $end_date = null;
        }


        if($goods_name != null  && $is_over != null &&  $show !=null && $date_string!=null)
        {
            $product_search = DB::table('trade_goods')
                                ->whereBetween('created_at', [$start_date, $end_date])
                                ->where('goods_name', '=', $goods_name)
                                ->where('is_over', '=', $is_over)
                                ->where('show', '=', $show)
                                ->orderBy('created_at', 'desc')
                                ->get();
        }else{
            $product_search = DB::table('trade_goods')
                                ->whereBetween('created_at', [$start_date, $end_date])
                                ->orwhere('goods_name', '=', $goods_name)
                                ->orwhere('is_over', '=', $is_over)
                                ->orwhere('show', '=', $show)
                                ->orderBy('created_at', 'desc')
                                ->get();
        }

        return response()->json([
            "product_search" => $product_search,
        ]);
    }
}
