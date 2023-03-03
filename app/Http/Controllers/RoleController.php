<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * 列出所有角色 list all of roles
     */
    public function index()
    {
        $roles = Role::where('status', 1)->orderBy('sort', 'asc')->paginate(10);

        return view('role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'status' => ['required', 'integer'],
            'soft' => ['required', 'integer', 'gt:0'],
            // // 'description' => ['required', 'string'],
            'auth' => ['required', 'integer', 'gt:0'],
            'auth2' => ['required', 'integer', 'gt:0'],
        ]);

        $role = new Role();
        $role->title = $request->title;
        $role->status = $request->status;
        $role->sort = $request->soft;
        $role->desc = $request->description;
        $role->auth = $request->auth;
        $role->auth2 = $request->auth2;
        $role->save();

        return redirect(route("role.index"));
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
        $role = Role::findOrFail($id);
        return view('role.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $id = (int)$id;
        $one = Role::find($id);
        $one->status = 0;
        $one->save();

        return redirect()->route('role.index');
    }
}
