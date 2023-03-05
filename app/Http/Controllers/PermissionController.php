<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::all();
        return view('permission.index', [
            "permissions" => $permissions
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $first_menus = config('data.main_menu');
        $roles = Role::all();
        return view('permission.create', [
            'roles' => $roles,
            'first_menus' => $first_menus
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'path_name' => ['required', 'string'],
            'role_id' => ['required', 'integer'],
        ]);

        // dd($request->all());

        $role_id = $request->role_id;

        $query = Permission::where('path_name', "=", $request->path_name)->where("role_id", "=", $role_id)->first();

        if (!empty($query)) {
            return back()->with("message", "that path and user already set!!!");
        }

        DB::beginTransaction();
        try {
            $permission = new Permission();
            $permission->path_name = $request->path_name;
            $permission->role_id = $request->role_id;
            $permission->auth2 = ($request->index ?? 0) + ($request->create ?? 0) + ($request->show ?? 0) + ($request->edit ?? 0) + ($request->update ?? 0) + ($request->destory ?? 0);

            if (!$permission->save())
                throw new \Exception('事务中断1');

            $one_role = Role::find($role_id);
            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . '为角色 ' . $one_role->title . ' 添加权限';
            $newlog->ip = "127.0.0.1";
            $newlog->route = 'permission.store';
            $newlog->parameters = json_encode($request->all());
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
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

        return redirect(route("role.index"));
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        $first_menus = config('data.main_menu');
        $roles = Role::all();
        return view('permission.edit', [
            "permission" => $permission,
            'roles' => $roles,
            'first_menus' => $first_menus
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'path_name' => ['required', 'string'],
            'role_id' => ['required', 'integer'],
        ]);

        $permission->path_name = $request->path_name;
        $permission->role_id = $request->role_id;
        $permission->auth2 = ($request->auth2_create ?? 0) + ($request->auth2_read ?? 0) + ($request->auth2_update ?? 0) + ($request->auth2_delete ?? 0);
        $permission->update();

        return redirect(route("permission.index"));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        DB::beginTransaction();
        try {
            $permission->delete();

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();;
            $newlog->action = '管理员' . $username . ' 添加站内信';
            $newlog->ip = "127.0.0.1";
            $newlog->route = 'permission.store';
            $newlog->parameters = "delete parameter";
            $newlog->created_at = date('Y-m-d H:i:s');
            if (!$newlog->save())
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



        return redirect(route("permission.index"));
    }
}
