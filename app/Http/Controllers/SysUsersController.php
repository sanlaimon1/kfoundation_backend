<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin;
use App\Models\Role;

class SysUsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 系统管理员 System Users
     */
    public function index()
    {
        //列出管理员
        $sysusers = Admin::select('id','username','status','create_at','desc','rid','login_at')
            ->where('is_deleted', 0)
            ->orderBy('create_at', 'desc')
            ->paginate(10);

        return view('sysusers.index', compact('sysusers') );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //查询所有角色
        $role_items = Role::select('rid','title')->where('status', 1)->orderBy('sort','asc')->get();
        $roles = [];
        foreach( $role_items as $one_role) {
            $roles[ $one_role->rid ] = $one_role->title;
        }

        return view('sysusers.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'username' => ['required', 'string', 'between:6,10', 'unique:admins'],
            'desc' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'between:8,15', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
            'cpassword' => ['required', 'string', 'between:8,15', 'same:password'],
            'status' => ['required', 'integer', 'in:0,1'],
            'rid' => ['required', 'integer', 'exists:roles,rid'],
        ];
        
        $messages = [
            'password.regex' => '密码必须必须是字母和数字的组合。',
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $username = trim($request->get('username'));
        $desc = trim($request->get('desc'));
        $password = trim($request->get('password'));
        $status = trim($request->get('status'));
        $rid = trim($request->get('rid'));

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $salt = substr(str_shuffle($chars), 0, 6);      //6位随机盐
        //构建密码
        $saltpassword = md5( md5( $salt . $password ) . $salt );

        $newadmin = new Admin;
        $newadmin->username = $username;
        $newadmin->desc = $desc;
        $newadmin->password = $saltpassword;
        $newadmin->create_at = date('Y-m-d H:i:s');
        $newadmin->salt = $salt;
        $newadmin->save();

        return redirect()->route('sysusers.index');
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
     * edit a manager  编辑一个管理员
     */
    public function edit(string $id)
    {
        $id = (int)$id;
        $one = Admin::find($id);
        //查询所有角色
        $role_items = Role::select('rid','title')->where('status', 1)->orderBy('sort','asc')->get();
        $roles = [];
        foreach( $role_items as $one_role) {
            $roles[ $one_role->rid ] = $one_role->title;
        }
        return view( 'sysusers.edit', compact('one','id','roles') );
    }

    /**
     * Show the form for editing the specified resource.
     * modify the password ,  修改密码
     */
    public function modify_pass(string $id)
    {
        $id = (int)$id;
        $one = Admin::find($id);

        return view( 'sysusers.pass', compact('one','id') );
    }

    /**
     * 修改逻辑  modify a manager
     */
    public function update(Request $request, string $id)
    {
        $rules = [
            'desc' => ['required', 'string', 'max:100'],
            'status' => ['required', 'integer', 'in:0,1'],
            'rid' => ['required', 'integer', 'exists:roles,rid'],
        ];
        
        $messages = [
            'status.in' => '状态的数值错误',
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $desc = trim( $request->get('desc') );
        $status = trim( $request->get('status') );
        $rid = trim( $request->get('rid') );

        $id = (int)$id;
        
        $one = Admin::find($id);
        if(empty($one))
            return '用户不存在';

        $one->desc = $desc;
        $one->status = $status;
        $one->rid = $rid;
        $one->save();

        return redirect()->route('sysusers.edit',['sysuser'=>$id])->with('message', '用户 ' . $one->username . ' 修改成功！');
    }

    /**
     * 修改逻辑  modify a manager
     */
    public function update_pass(Request $request)
    {
        $rules = [
            'password' => ['required', 'string', 'between:8,15', 'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'],
            'cpassword' => ['required', 'string', 'between:8,15', 'same:password'],
        ];
        
        $messages = [
            'password.regex' => '密码必须必须是字母和数字的组合。',
        ];
        
        $validator = Validator::make($request->all(), $rules, $messages);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            return redirect()->back()->withErrors($errors);
        }

        $password = trim( $request->get('password') );
        $id = trim( $request->get('id') );

        $id = (int)$id;
        $one = Admin::find($id);
        if(!empty($one))
        {
            $salt = $one->salt;
            $saltpassword = md5( md5( $salt . $password ) . $salt );
            $one->password = $saltpassword;
            $one->save();
        } else {
            return '用户不存在';
        }
        
        return redirect()->route('sysusers.modifypass',['id'=>$id])->with('message', '用户 ' . $one->username . ' 的密码修改成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id = (int)$id;
        $one = Admin::find($id);
        $one->is_deleted = 1;
        $one->save();

        return redirect()->route('sysusers.index');
    }
}
