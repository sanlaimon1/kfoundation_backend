<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Award;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class AwardController extends Controller
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
    private $path_name = "/award";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $items = Award::all();

        $item_cate = [];

        foreach($items as $one) {
            $item_cate[ $one->award_name ] = $one;
        }

        $registration_award = $item_cate['registration_award'];  
        $realname_award = $item_cate['realname_award'];  
        $everyday_award = $item_cate['everyday_award'];  
        $first_invest = $item_cate['first_invest'];  
        $balance_benefit = $item_cate['balance_benefit'];  
        $balance_min = $item_cate['balance_min'];  
        $balance_max = $item_cate['balance_max'];
        $reinvest = $item_cate['reinvest'];
        $machine_days = $item_cate['machine_days'];
        $machine_yield = $item_cate['machine_yield'];
        $machine_rate = $item_cate['machine_rate'];

        return view('award.index', compact('registration_award', 'realname_award', 'everyday_award', 'first_invest',
                                            'balance_benefit','balance_min','balance_max','reinvest','machine_days',
                                            'machine_yield', 'machine_rate'
                                        ) );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // disable to create
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // disable to store
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // disable to show
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
        if (Redis::exists("permission:".Auth::id())) 
            return "10秒内不能重复提交";

            Redis::set("permission:".Auth::id(), time());
            Redis::expire("permission:".Auth::id(), config('app.redis_second'));
            
            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 32) ){
                return "您没有权限访问这个路径";
            }

            //修改数据
            if(!is_numeric($id)) {
                $arr = ['code'=>-1, 'message'=>'id必须是整数'];
                return json_encode( $arr );
            }

            //收到值
            $award_value = trim( htmlspecialchars( $request->get('award_value') ));

            $id = (int)$id;

            DB::beginTransaction();
            try {
            //查询一条数据
                $one = Award::find($id);
                $one->award_value = $award_value;
                $one->save();
                
                if(!$one->save())
                    throw new \Exception('事务中断1');

                $username = Auth::user()->username;
                $newlog = new Log();
                $newlog->adminid = Auth::id();
                $newlog->action = '管理员'. $username. ' 修改站内信';
                $newlog->ip = $request->ip();
                $newlog->route = 'award.update';
                $input = $request->all();
                $input_json = json_encode( $input );
                $newlog->parameters = $input_json;  // 请求参数
                $newlog->created_at = date('Y-m-d H:i:s');

                if(!$newlog->save())
                    throw new \Exception('事务中断2');

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                //echo $e->getMessage();
                return '添加错误，事务回滚';
            }
            $arr = ['code'=>1, 'message'=>'保存成功'];
            return response()->json( $arr );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // disable to delete
    }
}
