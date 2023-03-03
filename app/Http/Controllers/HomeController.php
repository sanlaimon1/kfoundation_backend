<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //查询主菜单列表
        $menu_items = config('data.main_menu');

        return view('home',  compact('menu_items') );
    }

    /**
     * 后台首页 数据统计
     */
    public function satistatics() {

        return view('satistatics');
    }

    /**
     * 后台主菜单。
     * 点击主菜单上面的选项后，列出子菜单
     */
   public function subitem($keyid) {
        $datas = config('data');
        if(!array_key_exists($keyid, $datas))
            return 'key不存在 key is not existed';

        if(!array_key_exists($keyid, $datas['main_menu']))
            return '菜单的key不存在 key of main menu is not existed';

        $subitems = $datas[ $keyid ];

        $arr = [
            'item_name'=>$datas['main_menu'][$keyid],
            'subitems'=>$subitems
        ];

        return json_encode( $arr );
   }

   public function slide()
   {
        return view('config.system_picture');
   }

   
}
