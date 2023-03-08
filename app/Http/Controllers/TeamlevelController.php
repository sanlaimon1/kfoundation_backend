<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Teamlevel;
use App\Models\Level;
use Illuminate\Support\Facades\Auth;

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
    public function index()
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $teamlevels = Teamlevel::orderBy('tid', 'asc')->paginate(20);

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
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 4) ){
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'level_name' => ['required', 'string', 'between:1,45'],
            'icon' => ['required','image','mimes:jpg,png,jpeg,bmp,webp'],
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
        $newteamlevel->save();

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
        $newteamlevel->save();

        return redirect()->route('teamlevel.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }
        
        $role_id = Auth::user()->rid;        
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 64) ){
            return "您没有权限访问这个路径";
        }

        $teamlevel = Teamlevel::find($id);
        $teamlevel->delete();
        return redirect()->route('teamlevel.index');
    }
}
