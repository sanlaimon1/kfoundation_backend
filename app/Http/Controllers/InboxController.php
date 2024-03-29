<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Inbox;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Illuminate\Support\Facades\Log as LogFile;

class InboxController extends Controller
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
    private $path_name = "/inbox";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of inbox.
     * 显示站内信
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $title = $request->title ;
        $date = $request->date;

        //验证

        if( !empty($title)){
            if(!empty($date)){
                $mails = Inbox::where("title", "LIKE", '%' . $title . '%')->whereDate('created_at', "=", $date )->orderBy('is_top','desc')->orderBy('sort','desc')->orderBy('created_at','desc')->paginate(10);
                return view( 'inbox.index', compact('mails') );
            }
            else{
                $mails = Inbox::where("title", "LIKE", '%' . $title . '%')->orderBy('sort','desc')->orderBy('is_top','desc')->orderBy('created_at','desc')->paginate(10);
                // dd($mails);
                return view( 'inbox.index', compact('mails') );
            }
        }
        else{
            $mails = Inbox::orderBy('is_top','desc')->orderBy('sort','desc')->orderBy('created_at','desc')->paginate(10);

            return view( 'inbox.index', compact('mails') );

        }


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

        return view( 'inbox.create');
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
            Redis::expire("permission:".Auth::id(),config('app.redis_second'));

            $role_id = Auth::user()->rid;
            $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

            if( !(($permission->auth2 ?? 0) & 4) ){
                return "您没有权限访问这个路径";
            }

            $request->validate([
                'title' => ['required', 'string', 'max:45'],
                'content' => ['required', 'string'],
                'is_top' => ['required', 'integer', 'in:0,1'],
                'sort' => ['required', 'integer','gte:0'],
                // 'user_phone' => ['required', 'string'],
            ]);

            DB::beginTransaction();
            try {
                $mail = new Inbox();
                $mail->title = $request->title;
                $mail->content = htmlspecialchars( $request->content );
                //$mail->read = $request->read;
                $mail->sort = $request->sort;
                $mail->user_phone = $request->user_phone;
                $mail->created_at = date('Y-m-d H:i:s');

                if(!$mail->save())
                    throw new \Exception('事务中断1');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $store_action = ['username' => $username, 'type' => 'log.store_action'];
                $action = json_encode($store_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'inbox.store';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断2');

                $mails = array([
                    'id' => $mail->id,
                    'title' => $mail->title,
                    'content' => $mail->content,
                    'created_at' => $mail->created_at,
                    'sort' => $mail->sort,
                    'user_phone' => $mail->user_phone,
                    'is_top' => $mail->is_top,
                ]);
                $mail_json = json_encode($mails);
                DB::commit();
                LogFile::channel("inbox_store")->info($mail_json);

            } catch (\Exception $e) {
                DB::rollback();
                $message = $e->getMessage();
                LogFile::channel("inbox_store_error")->error($message);
                return '添加错误，事务回滚';
            }

            return redirect()->route('inbox.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 8) ){
            return "您没有权限访问这个路径";
        }

        $mail = Inbox::find($id);
        return view( 'inbox.show', compact('mail') );
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

        $mail = Inbox::find($id);
        return view( 'inbox.edit', compact('mail') );
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
                'content' => ['required', 'string'],
                'is_top' => ['required', 'integer', 'in:0,1'],
                'sort' => ['required', 'integer','gte:0'],
                // 'user_phone' => ['required', 'string'],
            ]);

            DB::beginTransaction();
            try {
                $mail = Inbox::find($id);
                $mail->title = $request->title;
                $mail->content = htmlspecialchars( $request->content );
                $mail->is_top = $request->is_top;
                $mail->sort = $request->sort;
                $mail->user_phone = $request->user_phone;

                if(!$mail->save())
                    throw new \Exception('事务中断3');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $updated_action = ['username' => $username, 'type' => 'log.update_action'];
                $action = json_encode($updated_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'inbox.update';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断4');

                $mails = array([
                    'id' => $mail->id,
                    'title' => $mail->title,
                    'content' => $mail->content,
                    'created_at' => $mail->created_at,
                    'sort' => $mail->sort,
                    'user_phone' => $mail->user_phone,
                    'is_top' => $mail->is_top,
                ]);
                $mail_json = json_encode($mails);
                DB::commit();
                LogFile::channel("inbox_update")->info($mail_json);

            } catch (\Exception $e) {
                DB::rollback();
                $message = $e->getMessage();
                LogFile::channel("inbox_update_error")->error($message);
                return '修改错误，事务回滚';
            }

            return redirect()->route('inbox.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
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
                $one = Inbox::find($id);

                $mails = array([
                    'id' => $one->id,
                    'title' => $one->title,
                    'content' => $one->content,
                    'created_at' => $one->created_at,
                ]);
                $mail_json = json_encode($mails);

                if(!$one->delete())
                    throw new \Exception('事务中断5');

                $username = Auth::user()->username;
                $newlog = new Log;
                $newlog->adminid = Auth::id();
                $delete_action = ['username' => $username, 'type' => 'log.delete_action'];
                $action = json_encode($delete_action);
                $newlog->action = $action;
                $newlog->ip = $request->ip();
                $newlog->route = 'inbox.destroy';
                $newlog->parameters = json_encode( $request->all() );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('事务中断6');

                DB::commit();
                LogFile::channel("inbox_destroy")->info($mail_json);
            } catch (\Exception $e) {
                DB::rollback();
                $message = $e->getMessage();
                LogFile::channel("inbox_destroy_error")->error($message);
                return '修改错误，事务回滚';
            }
            return redirect()->route('inbox.index');
    }

    public function inbox_search(Request $request)
    {
        $title  = $request->title;
        $date_string = $request->date;
        if($date_string){
            $date_parts = explode('至', $date_string);
            $start_date = trim($date_parts[0]);
            $end_date = trim($date_parts[1]);
        } else {
            $start_date = '';
            $end_date = '';
        }

        if($title != null && $date_string != null)
        {
            $inbox_search = DB::table('inboxes')
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->where('title', '=', $title)
                            ->orderBy('is_top', 'desc')
                            ->orderBy('sort', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

        } else {
            $inbox_search = DB::table('inboxes')
                            ->whereBetween('created_at', [$start_date, $end_date])
                            ->orwhere('title', '=', $title)
                            ->orderBy('is_top', 'desc')
                            ->orderBy('sort', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->get();

        };

        return response()->json([
            "inbox_search" => $inbox_search,
        ]);
    }
}
