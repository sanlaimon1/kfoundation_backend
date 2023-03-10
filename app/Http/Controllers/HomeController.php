<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use App\Models\Order1;
Use App\Models\Customer;
use App\Models\AssetCheck;
use App\Models\BalanceCheck;
use App\Models\Interest;
use App\Models\FinancialPlatformCoin;
use Carbon\Carbon;
use DB;

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
        $order1 = Order1::count();
        $customer = Customer::where('status', '!=', 0)->where('identity',0)->count();
        $assetCheck = AssetCheck::where('customers.identity', 0)
                    ->where('asset_check.status', 1)
                    ->join('customers', 'customers.id', 'asset_check.userid')
                    ->sum('amount');

        $balanceCheck = BalanceCheck::where('customers.identity', 0)
                        ->where('balance_check.status', 1)
                        ->join('customers','customers.id', 'balance_check.userid')->sum('amount');
        
        $todayCustomer = Customer::whereDate('created_at', Carbon::now()->today())
                        ->where('identity', 0)
                        ->count();
        $todayOrder = Order1::groupby('cid')
                        ->whereDate('created_at', Carbon::now()->today())
                        ->count();

        $todayAssetCheck = AssetCheck::where('asset_check.status', 1)
                        ->whereDate('asset_check.created_at',  Carbon::now()->today())
                        ->where('customers.id', 'assetcheck.userid')
                        ->where('customers.identity',  0)
                        ->join('customers','customers.id', 'asset_check.userid')
                        ->sum('amount');

        $interest = Interest::where('status', 1)
                    ->whereDate('refund_time', Carbon::now()->today())
                    ->sum('real_refund_amount');

        $yesterdayOrder1 = Order1::groupby('cid')
                            ->whereDate('created_at',Carbon::now()->yesterday())
                            ->count();

        $tomorrowInterest = Interest::where('status', 0)
                            ->whereDate('refund_time',Carbon::now()->tomorrow())
                            ->sum('refund_amount');

        $tomorrowInterestFlag = Interest::where('status', 0)
                                ->whereDate('refund_time', Carbon::now()->tomorrow())
                                ->where('flag', 1)
                                ->sum('refund_amount');

        $team = Customer::where('identity', 0)
                ->where('team_id', '>', 1)
                ->count();

        $customerOrder1 = Order1::whereDate('order1.created_at', Carbon::now()->today())
                         ->where('customers.identity', 0)
                         ->join('customers', 'customers.id', 'order1.cid')
                         ->count();
        
        $yesterdayCustomerOrder1 = Order1::whereDate('order1.created_at', Carbon::now()->yesterday())
                         ->where('customers.identity', 0)
                         ->join('customers','customers.id', 'order1.cid')
                         ->count();
        
        $weekOrder1 = Order1::whereBetween('order1.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                         ->where('customers.identity', 0)
                         ->join('customers', 'customers.id', 'order1.cid')
                         ->count();

        $monthOrder1 = Order1::whereBetween('order1.created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                         ->where('customers.identity', 0)
                         ->join('customers', 'customers.id', 'order1.cid')
                         ->count();
                         
        $todayAssetCheckNotZero = AssetCheck::where('customers.identity', '!=', 0)
                                ->where('asset_check.status', 1)
                                ->whereDate('asset_check.created_at', Carbon::now()->today())
                                ->join('customers', 'customers.id', 'asset_check.userid')
                                ->sum('asset_check.amount');
                        
        $balanceCheckNotzero = BalanceCheck::where('customers.identity', '!=', 0)
                                ->where('balance_check.status', 1)
                                ->whereDate('balance_check.created_at', now()->today())
                                ->join('customers', 'customers.id', 'balance_check.userid')
                                ->sum('balance_check.amount');

        $todayOrderCustomer = Order1::whereDate('order1.created_at', now()->today())
                                ->where('customers.identity', 0)
                                ->join('customers', 'customers.id', 'order1.cid')
                                ->sum('order1.amount');

        $customerAssetCheck = AssetCheck::where('customers.identity', '!=', 0)
                                ->where('asset_check.status', 1)
                                ->join('customers', 'customers.id', 'asset_check.userid')
                                ->sum('asset_check.amount');

        $customerBalanceCheck = BalanceCheck::where('customers.identity', '!=', 0)
                                ->where('balance_check.status', 1)
                                ->join('customers', 'customers.id', 'balance_check.userid')
                                ->sum('balance_check.amount');

        $customerByPlatformCoin = Customer::where('identity', 0)
                                  ->sum('platform_coin');

        $financialPlatformCoin = FinancialPlatformCoin::where('financial_platform_coin.financial_type',4)
                                  ->where('customers.identity', 0)
                                  ->join('customers', 'customers.id', 'financial_platform_coin.userid')
                                  ->sum('financial_platform_coin.amount');

        return view('satistatics', compact( 'order1', 'customer', 'assetCheck', 'balanceCheck', 'todayCustomer', 'todayOrder', 'todayAssetCheck', 'interest', 'yesterdayOrder1', 'tomorrowInterest','tomorrowInterestFlag', 'team', 'customerOrder1', 'yesterdayCustomerOrder1', 'weekOrder1', 'monthOrder1', 'todayAssetCheckNotZero', 'balanceCheckNotzero', 'todayOrderCustomer', 'customerAssetCheck', 'customerBalanceCheck', 'customerByPlatformCoin', 'financialPlatformCoin'));
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
        // $asset_check_status = Redis::get('asset_check_status');
        // $balance_check_status = Redis::get('balance_check_status');

        $asset_check_status = 0;
        $balance_check_status = 0;

        return response()->json([
            'code' => 1,
            'asset_check_status' => $asset_check_status,
            'balance_check_status' => $balance_check_status,
        ]);
    }
}
