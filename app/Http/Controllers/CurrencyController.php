<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use App\Models\Log;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log as LogFile;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $path_name = "/currency";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $currencies = Currency::orderBy('sort', 'asc')->paginate($perPage);
        if (!Redis::exists("currency:homepage:md5")){
            $currency = Currency::select('id', 'new_price', 'open_price', 'min_price', 'max_price', 'add_time')
                        ->whereBetween("add_time", [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                        ->orderBy('sort', 'asc')->orderBy('add_time', 'desc')
                        ->get();
            $array_currency = [];
            foreach ($currency as $one) {
                $data['id'] = $one->id;
                $data['new_price'] = $one->new_price;
                $data['open_price'] = $one->open_price;
                $data['min_price'] = $one->min_price;
                $data['max_price'] = $one->max_price;
                $data['add_time'] = $one->add_time;
                array_push($array_currency, $data);
            };
            $redis_currency = json_encode($array_currency);
            Redis::set( "currency:homepage:string", $redis_currency );
            Redis::set( "currency:homepage:md5", md5($redis_currency) );
        }
        return view('currency/index', compact('currencies'));
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

        return view('currency/create');
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
            'new_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'open_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'min_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'max_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'add_time' => ['required', 'date' ,'date_format:Y-m-d'],
            'sort' => ['required', 'integer', 'gt:0'],
        ]);

        $new_price = trim($request->new_price);
        $open_price = trim($request->open_price);
        $min_price = trim($request->min_price);
        $max_price = trim($request->max_price);
        $add_time =trim($request->add_time);
        $sort = trim($request->sort);
        $sort = (int)$sort;

        DB::beginTransaction();
        try{

            $newcurrency = new Currency();
            $newcurrency->new_price = $new_price;
            $newcurrency->open_price = $open_price;
            $newcurrency->min_price = $min_price;
            $newcurrency->max_price = $max_price;
            $newcurrency->add_time = $add_time;
            $newcurrency->sort = $sort;

            if(!$newcurrency->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $store_action = ['username' => $username, 'type' => 'log.currency_store_action'];
            $action = json_encode($store_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'currency.store';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');
            $currency = array(
                'id' => $newcurrency->id,
                'new_price' => $newcurrency->new_price,
                'open_price' => $newcurrency->open_price,
                'min_price' => $newcurrency->min_price,
                'max_price' => $newcurrency->max_price,
                'add_time' => $newcurrency->add_time,
                'sort' => $newcurrency->sort
            );
            $currency_json = json_encode($currency);
            DB::commit();
            LogFile::channel("currency_store")->info($currency_json);

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("currency_store_error")->error($message);
            return '添加错误，事务回滚';
        }

        $old_redis_currency = Redis::get("currency:homepage:md5");

        $currency = Currency::select('id', 'new_price', 'open_price', 'min_price', 'max_price', 'add_time')
                    ->whereBetween("add_time", [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->orderBy('sort', 'asc')->orderBy('add_time', 'desc')
                    ->get();
        $array_currency = [];
        foreach ($currency as $one) {
            $data['id'] = $one->id;
            $data['new_price'] = $one->new_price;
            $data['open_price'] = $one->open_price;
            $data['min_price'] = $one->min_price;
            $data['max_price'] = $one->max_price;
            $data['add_time'] = $one->add_time;
            array_push($array_currency, $data);
        };
        $redis_currency = json_encode($array_currency);
        if (md5($redis_currency) != $old_redis_currency) {
            Redis::set( "currency:homepage:string", $redis_currency );
            Redis::set( "currency:homepage:md5", md5($redis_currency) );
        }

        return redirect()->route('currency.index');
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

        $currency = Currency::find($id);
        return view('currency.edit',compact('currency'));
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
            'new_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'open_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'min_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'max_price' => ['required', 'numeric', 'regex:/^\d{1,11}(\.\d{0,2})?$/'],
            'add_time' => ['required', 'date' ,'date_format:Y-m-d'],
            'sort' => ['required', 'integer', 'gt:0'],
        ]);

        $new_price = trim($request->new_price);
        $open_price = trim($request->open_price);
        $min_price = trim($request->min_price);
        $max_price = trim($request->max_price);
        $add_time =trim($request->add_time);
        $sort = trim($request->sort);
        $sort = (int)$sort;

        DB::beginTransaction();
        try
        {
            $newcurrency = Currency::find($id);
            $newcurrency->new_price = $new_price;
            $newcurrency->open_price = $open_price;
            $newcurrency->min_price = $min_price;
            $newcurrency->max_price = $max_price;
            $newcurrency->add_time = $add_time;
            $newcurrency->sort = $sort;

            if(!$newcurrency->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $update_action = ['username' => $username, 'type' => 'log.currency_update_action'];
            $action = json_encode($update_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'currency.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            $currency = array(
                'id' => $newcurrency->id,
                'new_price' => $newcurrency->new_price,
                'open_price' => $newcurrency->open_price,
                'min_price' => $newcurrency->min_price,
                'max_price' => $newcurrency->max_price,
                'add_time' => $newcurrency->add_time,
                'sort' => $newcurrency->sort
            );
            $currency_json = json_encode($currency);
            DB::commit();
            LogFile::channel("currency_update")->info($currency_json);

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("currency_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        $old_redis_currency = Redis::get("currency:homepage:md5");

        $currency = Currency::select('id', 'new_price', 'open_price', 'min_price', 'max_price', 'add_time')
                    ->whereBetween("add_time", [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->orderBy('sort', 'asc')->orderBy('add_time', 'desc')
                    ->get();

        $array_currency = [];
        foreach ($currency as $one) {
            $data['id'] = $one->id;
            $data['new_price'] = $one->new_price;
            $data['open_price'] = $one->open_price;
            $data['min_price'] = $one->min_price;
            $data['max_price'] = $one->max_price;
            $data['add_time'] = $one->add_time;
            array_push($array_currency, $data);
        };
        $redis_currency = json_encode($array_currency);
        if (md5($redis_currency) != $old_redis_currency) {
            Redis::set( "currency:homepage:string", $redis_currency );
            Redis::set( "currency:homepage:md5", md5($redis_currency) );
        }

        return redirect()->route('currency.index');

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
            $currency = Currency::find($id);
            $currency_data = array(
                'id' => $currency->id,
                'new_price' => $currency->new_price,
                'open_price' => $currency->open_price,
                'min_price' => $currency->min_price,
                'max_price' => $currency->max_price,
                'add_time' => $currency->add_time,
                'sort' => $currency->sort
            );
            $currency_json = json_encode($currency);
            $currency->delete();

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $delete_action = ['username' => $username, 'type' => 'log.currency_delete_action'];
            $action = json_encode($delete_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'currency.destroy';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("currency_destroy")->info($currency_json);

            $old_redis_currency = Redis::get("currency:homepage:md5");

            $currency = Currency::select('id', 'new_price', 'open_price', 'min_price', 'max_price', 'add_time')
                        ->whereBetween("add_time", [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                        ->orderBy('sort', 'asc')->orderBy('add_time', 'desc')
                        ->get();

            $array_currency = [];
            foreach ($currency as $one) {
                $data['id'] = $one->id;
                $data['new_price'] = $one->new_price;
                $data['open_price'] = $one->open_price;
                $data['min_price'] = $one->min_price;
                $data['max_price'] = $one->max_price;
                $data['add_time'] = $one->add_time;
                array_push($array_currency, $data);
            };
            $redis_currency = json_encode($array_currency);
            if (md5($redis_currency) != $old_redis_currency) {
                Redis::set( "currency:homepage:string", $redis_currency );
                Redis::set( "currency:homepage:md5", md5($redis_currency) );
            }

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("currency_destroy_error")->error($message);
            return '添加错误，事务回滚';
        }
        return redirect()->route('currency.index');
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
