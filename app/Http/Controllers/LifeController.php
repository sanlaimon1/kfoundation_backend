<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Life;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;
use Illuminate\Support\Facades\DB;

class LifeController extends Controller
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
    private $path_name = "/life";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $lifes = Life::orderBy('sort','asc')->paginate($perPage);
        return view( 'life.index', compact('lifes') );
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

        return view('life.create');
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
                'production_name'=> ['required', 'string', 'between:1,40'],
                'sort'=> ['required', 'integer', 'gt:0'],
                'picture'=> ['required','image','mimes:jpg,png,jpeg,bmp,webp'],
            ]);

            $production_name = trim( $request->get('production_name') );
            $sort = trim( $request->get('sort') );
            $extra = trim( $request->get('extra') );
            $inputs = trim( $request->get('inputs') );

            $sort = (int)$sort;

            if($request->hasFile('picture')){
                $picture = time().'.'.$request->picture->extension();
                $request->picture->move(public_path('/images/'),$picture);
                $image = '/images/'.$picture;
            }

            DB::beginTransaction();
            try {

                $newlife = new Life();
                $newlife->production_name = $production_name;
                $newlife->picture = $image;
                $newlife->sort = $sort;
                $newlife->extra = $extra;
                $newlife->inputs = $inputs;
                if(!$newlife->save())
                    throw new \Exception('事务中断1');

                DB::commit();
                LogFile::channel("store")->info("充值缴费 存儲成功");

            }  catch (\Exception $e) {
                DB::rollBack();
                $message = $e->getMessage();
                LogFile::channel("error")->error($message);
                //echo $e->getMessage();
                return '添加错误，事务回滚';
            }

            return redirect()->route('life.index');
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

        $life = Life::find($id);
        return view('life.edit', compact('life'));
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
                'production_name'=> ['required', 'string', 'between:1,40'],
                'sort'=> ['required', 'integer', 'gt:0'],
                'picture'=> ['image','mimes:jpg,png,jpeg,bmp,webp'],
            ]);

            $production_name = trim( $request->get('production_name') );
            $sort = trim( $request->get('sort') );
            $extra = trim( $request->get('extra') );
            $inputs = trim( $request->get('inputs') );

            $sort = (int)$sort;

            if($request->hasFile('picture')){
                $picture = time().'.'.$request->picture->extension();
                $request->picture->move(public_path('/images/'),$picture);
                $image = '/images/'.$picture;
            } else {
                $image = $request->old_picture;
            }
            DB::beginTransaction();
            try {
                $newlife = Life::find($id);
                $newlife->production_name = $production_name;
                $newlife->picture = $image;
                $newlife->sort = $sort;
                $newlife->extra = $extra;
                $newlife->inputs = $inputs;
                if(!$newlife->save())
                    throw new \Exception('事务中断1');
                
                DB::commit();
                LogFile::channel("update")->info("充值缴费 更新成功");

            } catch (\Exception $e) {
                DB::rollBack();
                $message = $e->getMessage();
                LogFile::channel("error")->error($message);
                //echo $e->getMessage();
                return '添加错误，事务回滚';
            }
            return redirect()->route('life.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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
                $life = Life::find($id);
                if(!$life->delete())
                    throw new \Exception('事务中断1');
                DB::commit();
                LogFile::channel("destroy")->info("充值缴费 刪除成功");

            } catch (\Exception $e) {
                DB::rollBack();
                $message = $e->getMessage();
                LogFile::channel("error")->error($message);
                //echo $e->getMessage();
                return '添加错误，事务回滚';
            }
            return redirect()->route('life.index');
    }
}
