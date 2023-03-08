<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Award;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;

class AwardController extends Controller
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
    private $path_name = "/award";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name" , "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if( !(($permission->auth2 ?? 0) & 1) ){
            return "您没有权限访问这个路径";
        }

        $items = Award::all();

        $item_cate = [];

        foreach($items as $one) {
            $item_cate[ $one->award_name ] = $one;
        }

        $registration_award = $item_cate['registration_award'];  
        $realname_award = $item_cate['realname_award'];  
        $everyday_award = $item_cate['everyday_award'];  
        $first_invest = $item_cate['first_invest'];  
        $balance_benefit = $item_cate['balance_benefit'];  
        $balance_min = $item_cate['balance_min'];  
        $balance_max = $item_cate['balance_max'];  

        return view('award.index', compact('registration_award', 'realname_award', 'everyday_award', 'first_invest',
                                            'balance_benefit','balance_min','balance_max'    ) );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // disable to create
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // disable to store
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // disable to show
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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

        //修改数据
        if(!is_numeric($id)) {
            $arr = ['code'=>-1, 'message'=>'id必须是整数'];
            return json_encode( $arr );
        }

        //收到值
        $award_value = trim( htmlspecialchars( $request->get('award_value') ));

        $id = (int)$id;
        //查询一条数据
        $one = Award::find($id);
        $one->award_value = $award_value;
        $one->save();

        $arr = ['code'=>1, 'message'=>'保存成功'];
        return response()->json( $arr );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // disable to delete
    }
}
