<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inbox;
use App\Models\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InboxController extends Controller
{
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
        return view( 'inbox.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'inbox.store';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断2');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            return '添加错误，事务回滚';
        }

        return redirect()->route('inbox.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $mail = Inbox::find($id);
        return view( 'inbox.show', compact('mail') );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $mail = Inbox::find($id);
        return view( 'inbox.edit', compact('mail') );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
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
            /**
             * $errorMessage = $e->getMessage();
             * $errorCode = $e->getCode();
             * $stackTrace = $e->getTraceAsString();
             */
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            return '修改错误，事务回滚';
        }
        
        return redirect()->route('inbox.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $id = (int)$id;
        DB::beginTransaction();
        try {
            $one = Inbox::find($id);
            if(!$one->delete())
                throw new \Exception('事务中断5');

            $username = Auth::user()->username;
            $newlog = new Log;
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 删除站内信';
            $newlog->ip = $request->ip();
            $newlog->route = 'inbox.destroy';
            $newlog->parameters = json_encode( $request->all() );
            $newlog->created_at = date('Y-m-d H:i:s');
            if(!$newlog->save())
                throw new \Exception('事务中断6');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            //$errorMessage = $e->getMessage();
            //return $errorMessage;
            return '修改错误，事务回滚';
        }
        return redirect()->route('inbox.index');
    }
}
