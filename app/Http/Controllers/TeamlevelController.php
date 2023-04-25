<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Teamlevel;
use App\Models\Level;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class TeamlevelController extends Controller
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
    private $path_name = "/teamlevel";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.  显示团队等级
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 20);
        $teamlevels = Teamlevel::orderBy('tid', 'asc')->paginate($perPage);

        return view('teamlevel.index', compact('teamlevels'));
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

        $levels = Level::all();
        return view('teamlevel.create', compact('levels'));
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
            'level_name' => ['required', 'string', 'between:1,45'],
            'icon' => ['required','mimes:jpg,png,jpeg,bmp,webp'],
            'spread_members_num' => ['required','integer','gte:0'],
            'spread_leaders_num' => ['required','integer','gte:0'],
            'accumulative_amount' => ['required','integer','gte:0'],
            'team_award' => ['required','numeric','between:0,99.99'],
            'is_given' => ['required','integer','gte:0'],
            'award_amount' => ['required', 'numeric', 'between:0,99.99'],
            'default_level' => ['required', 'integer', 'gt:0', 'exists:levels,level_id'],
            'status' => ['required', 'integer', 'gte:0'],
        ]);

        if($request->hasFile('icon')){
            $icon = time().'.'.$request->icon->extension();
            $request->icon->move(public_path('/images/'),$icon);
            $icon = '/images/'.$icon;
        }

        $level_name = trim($request->level_name);
        $spread_members_num = trim($request->spread_members_num);
        $spread_leaders_num = trim($request->spread_leaders_num);
        $accumulative_amount = trim($request->accumulative_amount);
        $team_award = trim($request->team_award);
        $is_given = trim($request->is_given);
        $award_amount = trim($request->award_amount);
        $default_level = trim($request->default_level);
        $status = trim($request->status);

        $spread_members_num = (int)$spread_members_num;
        $spread_leaders_num = (int)$spread_leaders_num;
        $accumulative_amount = (int)$accumulative_amount;
        $is_given = (int)$is_given;
        $default_level = (int)$default_level;
        $status = (int)$status;

        DB::beginTransaction();
        try {
            $newteamlevel = new Teamlevel();
            $newteamlevel->level_name = $level_name;
            $newteamlevel->icon = $icon;
            $newteamlevel->spread_members_num = $spread_members_num;
            $newteamlevel->spread_leaders_num = $spread_leaders_num;
            $newteamlevel->accumulative_amount = $accumulative_amount;
            $newteamlevel->team_award = $team_award;
            $newteamlevel->is_given = $is_given;
            $newteamlevel->award_amount = $award_amount;
            $newteamlevel->default_level = $default_level;
            $newteamlevel->status = $status;

            if(!$newteamlevel->save())
                throw new \Exception('事务中断1');

            $teamlevels = array(
                'id' => $newteamlevel->id,
                'level_name' => $level_name,
                'icon' => $icon,
                'spread_members_num' => $spread_members_num,
                'spread_leaders_num' => $spread_leaders_num,
                'accumulative_amount' => $accumulative_amount,
                'team_award' => $team_award,
                'is_given' => $is_given,
                'award_amount' => $award_amount,
                'default_level' => $default_level,
                'status' => $status,
            );

            $teamlevel_json = json_encode($teamlevels);

            $username = Auth::user()->username;
            $log_action = ['username' => $username, 'type' => 'log.teamlevel_store'];

            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = json_encode($log_action);//'管理员' . $username . ' 添加站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'teamlevel.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
            LogFile::channel("teamlevel_store")->info($teamlevel_json);
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("teamlevel_store_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('teamlevel.index');
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

        $teamlevel = Teamlevel::find($id);
        $levels = Level::all();
        return view('teamlevel.edit', compact('teamlevel', 'levels'));
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
        Redis::expire("permission:".Auth::id(),config('app.redis_second'));

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 32) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'level_name' => ['required', 'string', 'between:1,45'],
            'icon' => ['image','mimes:jpg,png,jpeg,bmp,webp'],
            'spread_members_num' => ['required','integer','gte:0'],
            'spread_leaders_num' => ['required','integer','gte:0'],
            'accumulative_amount' => ['required','integer','gte:0'],
            'team_award' => ['required','numeric','between:0,99.99'],
            'is_given' => ['required','integer','gte:0'],
            'award_amount' => ['required', 'numeric', 'between:0,99.99'],
            'default_level' => ['required', 'integer', 'gt:0', 'exists:levels,level_id'],
            'status' => ['required', 'integer', 'gte:0'],
        ]);

        if($request->hasFile('icon')){
            $icon = time().'.'.$request->icon->extension();
            $request->icon->move(public_path('/images/'),$icon);
            $icon = '/images/'.$icon;
        } else {
            $icon = $request->old_icon;
        }

        $level_name = trim($request->level_name);
        $spread_members_num = trim($request->spread_members_num);
        $spread_leaders_num = trim($request->spread_leaders_num);
        $accumulative_amount = trim($request->accumulative_amount);
        $team_award = trim($request->team_award);
        $is_given = trim($request->is_given);
        $award_amount = trim($request->award_amount);
        $default_level = trim($request->default_level);
        $status = trim($request->status);

        $spread_members_num = (int)$spread_members_num;
        $spread_leaders_num = (int)$spread_leaders_num;
        $accumulative_amount = (int)$accumulative_amount;
        $is_given = (int)$is_given;
        $default_level = (int)$default_level;
        $status = (int)$status;

        DB::beginTransaction();
        try {
            $newteamlevel = Teamlevel::find($id);
            $newteamlevel->level_name = $level_name;
            $newteamlevel->icon = $icon;
            $newteamlevel->spread_members_num = $spread_members_num;
            $newteamlevel->spread_leaders_num = $spread_leaders_num;
            $newteamlevel->accumulative_amount = $accumulative_amount;
            $newteamlevel->team_award = $team_award;
            $newteamlevel->is_given = $is_given;
            $newteamlevel->award_amount = $award_amount;
            $newteamlevel->default_level = $default_level;
            $newteamlevel->status = $status;

            if(!$newteamlevel->save())
                throw new \Exception('事务中断3');
            
            $teamlevels = array(
                'id' => $id,
                'level_name' => $level_name,
                'icon' => $icon,
                'spread_members_num' => $spread_members_num,
                'spread_leaders_num' => $spread_leaders_num,
                'accumulative_amount' => $accumulative_amount,
                'team_award' => $team_award,
                'is_given' => $is_given,
                'award_amount' => $award_amount,
                'default_level' => $default_level,
                'status' => $status,
            );

            $teamlevel_json = json_encode($teamlevels);

            $username = Auth::user()->username;
            $log_action = ['username' => $username, 'type' => 'log.teamlevel_update'];
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = json_encode($log_action); //'管理员' . $username . ' 修改站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'teamlevel.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断4');

            DB::commit();
            LogFile::channel("teamlevel_update")->info($teamlevel_json);
        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("teamlevel_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('teamlevel.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
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
        try {
            $teamlevel = Teamlevel::find($id);
            $teamlevels = array(
                'id' => $id,
                'level_name' => $teamlevel->level_name,
                'icon' => $teamlevel->icon,
                'spread_members_num' => $teamlevel->spread_members_num,
                'spread_leaders_num' => $teamlevel->spread_leaders_num,
                'accumulative_amount' => $teamlevel->accumulative_amount,
                'team_award' => $teamlevel->team_award,
                'is_given' => $teamlevel->is_given,
                'award_amount' => $teamlevel->award_amount,
                'default_level' => $teamlevel->default_level,
                'status' => $teamlevel->status,
            );

            $teamlevel_json = json_encode($teamlevels);

            if(!$teamlevel->delete())
                throw new \Exception('事务中断5');

            $username = Auth::user()->username;
            $log_action = ['username' => $username, 'type' => 'log.teamlevel_delete'];
            $newlog = new Log;
            $newlog->adminid = Auth::id();
            $newlog->action = json_encode($log_action); //'管理员' . $username . ' 删除站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'teamlevel.destroy';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断6');

            DB::commit();
            LogFile::channel("teamlevel_destroy")->info($teamlevel_json);

        } catch (\Exception $e) {
            DB::rollback();
            $message = $e->getMessage();
            LogFile::channel("teamlevel_destroy_error")->error($message);
            return '添加错误，事务回滚';
        }
        return redirect()->route('teamlevel.index');
    }
}
