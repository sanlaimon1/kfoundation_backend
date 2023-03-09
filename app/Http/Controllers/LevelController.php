<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Level;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use DB;

class LevelController extends Controller
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
    private $path_name = "/level";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 会员等级
     */
    public function index()
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $levels = Level::orderBy('level_id', 'desc')->paginate(20);

        return view('level.index', compact('levels'));
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

        return view('level/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 4) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'level_name' => ['required', 'string', 'between:1,45'],
            'accumulative_amount' => ['required','integer','gt:0'],
            'interest' => ['required','numeric','between:0,99.99'],
            'personal_charge' => ['required','numeric','between:0,99.99'],
            'level1_award' => ['required','numeric','between:0,99.99'],
            'level2_award' => ['required','numeric','between:0,99.99'],
            'level3_award' => ['required','numeric','between:0,99.99'],
            'min_coin' => ['required', 'integer', 'gte:0'],
            'max_coin' => ['required', 'integer', 'gte:0'],
        ]);

        $level_name = trim($request->level_name);
        $accumulative_amount = trim($request->accumulative_amount);
        $interest = trim($request->interest);
        $personal_charge = trim($request->personal_charge);
        $level1_award = trim($request->level1_award);
        $level2_award = trim($request->level2_award);
        $level3_award = trim($request->level3_award);
        $min_coin = trim($request->min_coin);
        $max_coin = trim($request->max_coin);

        DB::beginTransaction();
        try {
            $newlevel = new Level();
            $newlevel->level_name = $level_name;
            $newlevel->accumulative_amount = $accumulative_amount;
            $newlevel->interest = $interest;
            $newlevel->personal_charge = $personal_charge;
            $newlevel->level1_award = $level1_award;
            $newlevel->level2_award = $level2_award;
            $newlevel->level3_award = $level3_award;
            $newlevel->min_coin = $min_coin;
            $newlevel->max_coin = $max_coin;

            if(!$newlevel->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. ' 添加站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'level.store';
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

        return redirect()->route('level.index');
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

        $level = Level::find($id);
        return view('level.edit', compact('level'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'level_name' => ['required', 'string', 'between:1,45'],
            'accumulative_amount' => ['required','integer','gt:0'],
            'interest' => ['required','numeric','between:0,99.99'],
            'personal_charge' => ['required','numeric','between:0,99.99'],
            'level1_award' => ['required','numeric','between:0,99.99'],
            'level2_award' => ['required','numeric','between:0,99.99'],
            'level3_award' => ['required','numeric','between:0,99.99'],
            'min_coin' => ['required', 'integer', 'gte:0'],
            'max_coin' => ['required', 'integer', 'gte:0'],
        ]);

        $level_name = trim($request->level_name);
        $accumulative_amount = trim($request->accumulative_amount);
        $interest = trim($request->interest);
        $personal_charge = trim($request->personal_charge);
        $level1_award = trim($request->level1_award);
        $level2_award = trim($request->level2_award);
        $level3_award = trim($request->level3_award);
        $min_coin = trim($request->min_coin);
        $max_coin = trim($request->max_coin);

        DB::beginTransaction();
        try {
            $newlevel = Level::find($id);
            $newlevel->level_name = $level_name;
            $newlevel->accumulative_amount = $accumulative_amount;
            $newlevel->interest = $interest;
            $newlevel->personal_charge = $personal_charge;
            $newlevel->level1_award = $level1_award;
            $newlevel->level2_award = $level2_award;
            $newlevel->level3_award = $level3_award;
            $newlevel->min_coin = $min_coin;
            $newlevel->max_coin = $max_coin;

            if(!$newlevel->save())
                throw new \Exception('事务中断3');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. ' 修改站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'level.update';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断4');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            return '添加错误，事务回滚';
        }

        

        return redirect()->route('level.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }
        DB::beginTransaction();
        try {
            //code...
            $level = Level::find($id);
            if(!$level->delete())
                throw new \Exception('事务中断5');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $newlog->action = '管理员'. $username. ' 删除站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'level.destroy';
            $input = $request->all();
            $input_json = json_encode( $input );
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if(!$newlog->save())
                throw new \Exception('事务中断6');

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            //echo $e->getMessage();
            return '添加错误，事务回滚';
        }
        return redirect()->route('level.index');
    }
}
