<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Slide;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Log;
use Illuminate\Support\Facades\Redis;

class SlideController extends Controller
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
    private $path_name = "/slide";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
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

        $records = Slide::orderBy('sort', 'asc')->paginate($perPage);

        return view('slide.index', compact('records'));
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

        return view('slide.create');
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
            'title' => ['required', 'string', 'max:45'],
            'picture_path.*' => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            'link' => ['required', 'string'],
            'type' => ['required', 'integer', 'in:0,1'],
            'status' => ['required', 'integer', 'in:0,1'],
            'sort' => ['required', 'integer', 'gt:0'],
        ]);

        if($request->hasFile('picture_path')){
            $picture_path = time().'.'.$request->picture_path->extension();
            $request->picture_path->move(public_path('/images/webpimg/'),$picture_path);
            $picture_path = '/images/webpimg/'.$picture_path;
        }

        // $webp_path = $this->convertImgToWebp($picture_path);

        DB::beginTransaction();
        try {
            $slide = new Slide();
            $slide->title  = $request->title;
            $slide->picture_path = $picture_path;
            $slide->link  = $request->link;
            $slide->type  = $request->type;
            $slide->status  = $request->status;
            $slide->sort  = $request->sort;
            if(!$slide->save())
            throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'slide.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = $e->getMessage();
            return $errorMessage;
        }
        return redirect()->route('slide.index');
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
        
        $slide = Slide::find($id);

        return view('slide.edit', compact('slide'));
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
            'title' => ['required', 'string', 'max:45'],
            'picture_path.*' => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            'link' => ['required', 'string'],
            'type' => ['required', 'integer', 'in:0,1'],
            'status' => ['required', 'integer', 'in:0,1'],
            'sort' => ['required', 'integer', 'gt:0'],
        ]);
        
        if($request->hasFile('picture_path')){
            $picture_path = time().'.'.$request->picture_path->extension();
            $request->picture_path->move(public_path('/images/webpimg/'),$picture_path);
            $picture_path = '/images/webpimg/'.$picture_path;

            // $webp_path = $this->convertImgToWebp($picture_path);

        }else{
            $picture_path = $request->picture_path;
        }

        DB::beginTransaction();
        try {
            $slide = Slide::find($id);
            $slide->title  = $request->title;
            $slide->picture_path  = $picture_path;
            $slide->link  = $request->link;
            $slide->type  = $request->type;
            $slide->status  = $request->status;
            $slide->sort  = $request->sort;
            if(!$slide->save())
                throw new \Exception('事务中断3');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 修改站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'inbox.update';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断4');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = $e->getMessage();
            return $errorMessage;
        }
        return redirect()->route('slide.index');
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

        $id = (int)$id;

        DB::beginTransaction();
        try {
            $one = Slide::find($id);
            if(!$one->delete())
                throw new \Exception('事务中断5');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 修改站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'inbox.destroy';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断6');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $errorMessage = $e->getMessage();
            return $errorMessage;
        }

        return redirect()->route('slide.index');
    }

    // function convertImgToWebp($file){
    //     $ext = pathinfo($file, PATHINFO_EXTENSION);

    //     if ($ext == "jpg") {
    //         $get_image = imagecreatefromjpeg(public_path($file));
    //     } else if ($ext == "png") {
    //         $get_image = imagecreatefrompng(public_path($file));
    //     } else if ($ext == "jpeg") {
    //         $get_image = imagecreatefromjpeg(public_path($file));
    //     }

    //     // Create a blank WebP image with the same dimensions
    //     $webp_image = imagecreatetruecolor(imagesx($get_image), imagesy($get_image));

    //     // Convert the PNG image to WebP
    //     imagepalettetotruecolor($webp_image);
    //     imagealphablending($webp_image, true);
    //     imagesavealpha($webp_image, true);
    //     $quality = 80; // Quality of the WebP image (0-100)
    //     $get_webp_name = time() . '.webp';
    //     $webp_path = 'images/webpimg/' . $get_webp_name;
    //     imagewebp($get_image, $webp_path, $quality);

    //     // Free up memory
    //     imagedestroy($get_image);
    //     imagedestroy($webp_image);

    //     return '/'.$webp_path;
    //     // $get_files = public_path('/images/webpimg/');
    //     // $get_images = glob($get_files . "*.webp");
    // }
}
