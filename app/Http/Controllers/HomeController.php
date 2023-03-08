<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
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

        return view('home',  compact('menu_items'));
    }

    /**
     * 后台首页 数据统计
     */
    public function satistatics()
    {

        return view('satistatics');
    }

    /**
     * 后台主菜单。
     * 点击主菜单上面的选项后，列出子菜单
     */
    public function subitem($keyid)
    {
        $datas = config('data');
        if (!array_key_exists($keyid, $datas))
            return 'key不存在 key is not existed';

        if (!array_key_exists($keyid, $datas['main_menu']))
            return '菜单的key不存在 key of main menu is not existed';

        $subitems = $datas[$keyid];

        $arr = [
            'item_name' => $datas['main_menu'][$keyid],
            'subitems' => $subitems
        ];

        return json_encode($arr);
    }

    public function slide()
    {
        $get_files = public_path('images/webpimg/');
        // $get_images = glob($get_files . "*.webp");
        $get_images = \File::allFiles($get_files);

        return view('config.system_picture', compact('get_images'));
    }

    public function save_image(Request $request)
    {
        if ($request->upload_new_file) {
            if ($request->hasFile('upload_new_file')) {
                $get_upload_new_file = time() . '.' . $request->upload_new_file->extension();
                $request->upload_new_file->move(public_path('/images/'), $get_upload_new_file);
                $upload_new_file_value = '/images/' . $get_upload_new_file;
            }

            $ext = pathinfo($upload_new_file_value, PATHINFO_EXTENSION);

            if ($ext == "jpg") {
                $get_image = imagecreatefromjpeg(public_path($upload_new_file_value));
            } else if ($ext == "png") {
                $get_image = imagecreatefrompng(public_path($upload_new_file_value));
            } else if ($ext == "jpeg") {
                $get_image = imagecreatefromjpeg(public_path($upload_new_file_value));
            }

            // Create a blank WebP image with the same dimensions
            $webp_image = imagecreatetruecolor(imagesx($get_image), imagesy($get_image));

            // Convert the PNG image to WebP
            imagepalettetotruecolor($webp_image);
            imagealphablending($webp_image, true);
            imagesavealpha($webp_image, true);
            $quality = 80; // Quality of the WebP image (0-100)
            $get_webp_name = time() . '.webp';
            $webp_path = 'images/webpimg/' . $get_webp_name;
            imagewebp($get_image, $webp_path, $quality);

            // Free up memory
            imagedestroy($get_image);
            imagedestroy($webp_image);

            $get_files = public_path('/images/webpimg/');
            $get_images = glob($get_files . "*.webp");

            return redirect()->route('slide');
        }
    }

    public function delete_image(Request $request)
    {

        if (\File::exists(public_path($request->data))) {
            $delete_file = \File::delete(public_path($request->data));
        }
        if ($delete_file) {
            return response()->json("success");
        }
    }

    public function check_count()
    {

        $asset_check_status = Redis::get('asset_check_status');
        $balance_check_status = Redis::get('balance_check_status');

        return response()->json([
            'code' => 1,
            'asset_check_status' => $asset_check_status,
            'balance_check_status' => $balance_check_status,
        ]);
    }
}
