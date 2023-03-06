<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Http\File;
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

   public function save_image(Request $request)
   {
        if($request->data){
            $name = time().'.'.$request->data;

            $path = Storage::path($name);

            // 指定原始图片路径和保存的webp图片路径
            $source_image = $path;
            $webp_image = '/images/image.webp';

            // 读取原始图片
            $image = imagecreatefromjpg($source_image);

            // 转换为WebP格式并保存
            imagewebp($image, $webp_image);

            // 释放内存
            imagedestroy($image);

            // $result = $this->webpConvert2($name, 80);

            dd($result);
        }
        // return response()->json("success");
   }

    // function webpConvert2($file, $compression_quality = 80)
    // {
    //     // // check if file exists
    //     // if (!file_exists($file)) {
    //     //     return false;
    //     // }
    //     $file_type = exif_imagetype($file);
    //     // dd("reach",$file_type);
    //     //https://www.php.net/manual/en/function.exif-imagetype.php
    //     //exif_imagetype($file);
    //     // 1    IMAGETYPE_GIF
    //     // 2    IMAGETYPE_JPEG
    //     // 3    IMAGETYPE_PNG
    //     // 6    IMAGETYPE_BMP
    //     // 15   IMAGETYPE_WBMP
    //     // 16   IMAGETYPE_XBM
    //     $output_file =  $file . '.webp';
    //     if (file_exists($output_file)) {
    //         return $output_file;
    //     }
    //     if (function_exists('imagewebp')) {
    //         switch ($file_type) {
    //             case '1': //IMAGETYPE_GIF
    //                 $image = imagecreatefromgif($file);
    //                 break;
    //             case '2': //IMAGETYPE_JPEG
    //                 $image = imagecreatefromjpeg($file);
    //                 break;
    //             case '3': //IMAGETYPE_PNG
    //                     $image = imagecreatefrompng($file);
    //                     imagepalettetotruecolor($image);
    //                     imagealphablending($image, true);
    //                     imagesavealpha($image, true);
    //                     break;
    //             case '6': // IMAGETYPE_BMP
    //                 $image = imagecreatefrombmp($file);
    //                 break;
    //             case '15': //IMAGETYPE_Webp
    //             return false;
    //                 break;
    //             case '16': //IMAGETYPE_XBM
    //                 $image = imagecreatefromxbm($file);
    //                 break;
    //             default:
    //                 return false;
    //         }
    //         // Save the image
    //         $result = imagewebp($image, $output_file, $compression_quality);
    //         if (false === $result) {
    //             return false;
    //         }
    //         // Free up memory
    //         imagedestroy($image);
    //         return $output_file;
    //     } elseif (class_exists('Imagick')) {
    //         $image = new Imagick();
    //         $image->readImage($file);
    //         if ($file_type === "3") {
    //             $image->setImageFormat('webp');
    //             $image->setImageCompressionQuality($compression_quality);
    //             $image->setOption('webp:lossless', 'true');
    //         }
    //         $image->writeImage($output_file);
    //         return $output_file;
    //     }
    //     return false;
    // }

   
}
