<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Models\Log;
use App\Models\Blockip;
use App\Models\Permission;
use Illuminate\Support\Facades\Log as LogFile;

class BlockipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    private $path_name = "/blockip";

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
        $blockips = Blockip::paginate($perPage);
        return view('blockip.index',compact('blockips'));
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
        if (Redis::exists("permission:".Auth::id()))
            return "10秒内不能重复提交";

        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 4) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'block_ip' => 'required|ip|unique:blockip,ipaddress',
            'subnet' =>  ['sometimes', 'integer', 'between:1,32']
        ],[
            'block_ip.required' => '请填写:block_ip',
            'block_ip.ip' => '无效的IP地址',
            'block_ip.unique' =>'该IP地址已被占用',
            'subnet.integer' =>'必须为整数!',
            'subnet.between' =>'子网掩码需在1到32之间！',

        ]);
        DB::beginTransaction();
        try {

            $long_ip = ip2long($request->block_ip);
            $request_subnet = (int) $request->subnet;
            if($request->subnet <= 32){
                $max_ip = ip2long('255.255.255.255');
                $subnet = long2ip($max_ip << (32 - $request_subnet));
            }else{
                return back()->withErrors(['subnet' => ['子网掩码的范围小于等于32']]);
            }

            $blockip = new Blockip();
            $blockip->ipaddress = $request->block_ip;
            $blockip->longip = $long_ip;
            $blockip->subnet = $subnet;

            if(!$blockip->save())
                throw new \Exception('事务中断1');

            $myself = Auth::user();
            $log = new Log();
            $log->adminid = $myself->id;
            $store_action = ['username' => $myself->username, 'type' => 'log.store_blockip_action'];
            $action = json_encode($store_action);
            $log->action = $action;
            $log->ip = $request->ip();     // IP地址
            $log->route = 'blockip.store';
            $input = $request->all();
            $input_json = json_encode( $input );
            $log->parameters = $input_json;  // 请求参数
            $log->created_at = date('Y-m-d H:i:s');

            if(! $log->save())
            throw new \Exception('事务中断2');
            $blockip = array(
                'id' => $blockip->id,
                'ipaddress' => $blockip->ipaddress,
                'longip' => $blockip->longip,
                'subnet' => $blockip->subnet,
            );
            $blockip_json = json_encode($blockip);
            DB::commit();
            LogFile::channel("blockip_store")->info($blockip_json);

        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("blockip_store_error")->error($message);
            return 'error';
        }

        return redirect()->route('blockip.index');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        if (Redis::exists("permission:".Auth::id()))
            return "10秒内不能重复提交";

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
            $blockip = Blockip::find($id);
            $ipaddress = $blockip->ipaddress;
            $blockip_data = array(
                'id' => $blockip->id,
                'ipaddress' => $blockip->ipaddress,
                'longip' => $blockip->longip,
                'subnet' => $blockip->subnet,
            );
            $blockip_json = json_encode($blockip_data);
            if(!$blockip->delete())
                throw new \Exception('事务中断2');
            Redis::del('login:blockip');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $delete_action = ['username' => $username, 'ipaddress' => $ipaddress, 'type' => 'log.delete_blockip_action'];
            $action = json_encode($delete_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'blockip.destroy';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("blockip_destroy")->info($blockip_json);


        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            $message = $e->getMessage();
            LogFile::channel("blockip_destroy_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('blockip.index');

    }
}
