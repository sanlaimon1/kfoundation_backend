<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Sign;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class SignController extends Controller
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
    private $path_name = "/sign";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 签到
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $signs = Sign::orderBy('signdate','desc')->paginate($perPage);

        return view('sign.index', compact('signs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('sign.create');
    }

    /**
     * Store a new record
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

        $rules = [
            'signdate' => ['required', 'date' ,'date_format:Y-m-d', 'after:today', 'unique:signs'],
        ];

        $messages = [
            'signdate.date_format' => '必须是 年-月-日 格式',
            'signdate.after' => '必须大于今天',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $signdate = trim($request->get('signdate'));

        // to do something
        DB::beginTransaction();
        try {
            $one = new Sign;
            $one->signdate = $signdate;
            if(!$one->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'sign.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("store")->info("签到奖励 存儲成功");
        } catch (\Exception $e) {
            DB::rollback();
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            $message = $e->getMessage();
            LogFile::channel("error")->error($message);
            return '修改错误，事务回滚';
        }
        return redirect()->route('sign.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // no need to show
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // disable to edit
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // disable to update
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        if (Redis::exists("permission:".Auth::id())) 
        return "10秒内不能重复提交";

        Redis::set("permission:".Auth::id(), time());
        Redis::expire("permission:".Auth::id(), config('app.redis_second'));

        // need to do
        DB::beginTransaction();
        try {
            $sign = Sign::find($id);
            if(!$sign->delete())
                throw new \Exception('事务中断3');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'sign.destroy';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断4');

            DB::commit();
            LogFile::channel("destroy")->info("签到奖励 刪除成功");

        } catch (\Exception $e) {
            DB::rollback();
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            $message = $e->getMessage();
            LogFile::channel("error")->error($message);
            return '修改错误，事务回滚';
        }
        return redirect()->route('sign.index');
    }
}
