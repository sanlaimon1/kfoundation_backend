<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sign;
use Illuminate\Support\Facades\Validator;

class SignController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 签到
     */
    public function index()
    {
        $signs = Sign::orderBy('signdate','desc')->paginate(10);

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
        $rules = [
            'signdate' => ['required', 'date' ,'date_format:Y-m-d', 'after:today'],
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
        $one = new Sign;
        $one->signdate = $signdate;
        $one->save();

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
    public function destroy(string $id)
    {
        // need to do
    }
}
