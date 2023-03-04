<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inbox;

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
        if( !empty($title)){
            if(!empty($date)){
                $mails = Inbox::where("title", "LIKE", "%{$title}%")->whereDate('created_at', "=", $date )->orderBy('sort','desc')->orderBy('created_at','desc')->paginate(10);
                return view( 'inbox.index', compact('mails') );
            }
            else{ 
                $mails = Inbox::where("title", "LIKE", "%{$title}%")->orderBy('sort','desc')->orderBy('created_at','desc')->paginate(10);
                // dd($mails);
                return view( 'inbox.index', compact('mails') );
            }            
        }
        else{
            $mails = Inbox::orderBy('sort','desc')->orderBy('created_at','desc')->paginate(10);

            return view( 'inbox.index', compact('mails') );
            
        }

        
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
        //
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
            'title' => ['required', 'string'],
            // 'content' => ['required', 'string'],
            'read' => ['required', 'integer'],
            'sort' => ['required', 'integer'],
            // 'user_phone' => ['required', 'string'],
        ]);
        
        $mail = Inbox::find($id);
        $mail->title = $request->title;
        $mail->content = $request->content;
        $mail->read = $request->read;
        $mail->sort = $request->sort;
        $mail->user_phone = $request->user_phone;
        // $mail->created_at = now();
        $mail->save();
        return redirect()->route('inbox.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id = (int)$id;
        $one = Inbox::find($id);
        $one->delete();
        return redirect()->route('inbox.index');
    }
}
