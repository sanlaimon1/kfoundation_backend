<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Models\AssetCheck;
// use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/satistatics', [App\Http\Controllers\HomeController::class, 'satistatics'])->name('satistatics');
Route::get('/subitem/{keyid}', [App\Http\Controllers\HomeController::class, 'subitem'])->name('subitem');   //子菜单
//网站信息
Route::resource('website', 'App\Http\Controllers\WebsiteController');
//首页弹窗提示
Route::resource('windowhomepage', 'App\Http\Controllers\WindowhomepageController');
//支付设置
Route::resource('payment', 'App\Http\Controllers\PaymentController');
//app版本设置
Route::resource('version', 'App\Http\Controllers\VersionController');
//短信版本设置
Route::resource('sms', 'App\Http\Controllers\SmsController');
//合同模板设置
Route::resource('agreement', 'App\Http\Controllers\AgreementController');
//管理员日志
Route::resource('log', 'App\Http\Controllers\LogController');
//系统日志
Route::resource('syslog', 'App\Http\Controllers\SyslogController');
Route::post('log_search', 'App\Http\Controllers\LogController@log_search')->name('log_search');
//系统用户列表
Route::resource('sysusers', 'App\Http\Controllers\SysUsersController');
Route::get('/sysusers/modifypass/{id}', 'App\Http\Controllers\SysUsersController@modify_pass')->name('sysusers.modifypass'); //修改系统用户的密码
Route::post('/sysusers/updatepass', 'App\Http\Controllers\SysUsersController@update_pass')->name('sysusers.updatepass');
//系统角色
Route::resource('role', 'App\Http\Controllers\RoleController');
// 文章分类
Route::resource('category', 'App\Http\Controllers\CategoryController');
//系统奖励管理
Route::resource('award', 'App\Http\Controllers\AwardController');
//签到管理
Route::resource('sign', 'App\Http\Controllers\SignController');
//文章列表
Route::resource('article', 'App\Http\Controllers\ArticleController');
Route::post('article_notice', 'App\Http\Controllers\ArticleController@article_notice')->name('article_notice');
//系统图片设置
//Route::get('/slide', 'App\Http\Controllers\HomeController@slide')->name('slide');
Route::post('/save_image', 'App\Http\Controllers\HomeController@save_image')->name('save_image');
Route::post('/delete_image', 'App\Http\Controllers\HomeController@delete_image')->name('delete_image');
//生活缴费
Route::resource('life', 'App\Http\Controllers\LifeController');
//会员等级
Route::resource('level', 'App\Http\Controllers\LevelController');
//团队等级
Route::resource('teamlevel', 'App\Http\Controllers\TeamlevelController');
//项目分类
Route::resource('projectcate', 'App\Http\Controllers\ProjectCateController');
//商品管理
Route::resource('goods', 'App\Http\Controllers\GoodsController');
//站内信
Route::resource('inbox', 'App\Http\Controllers\InboxController');
Route::post('inbox_search', 'App\Http\Controllers\InboxController@inbox_search')->name('inbox_search');
//项目管理
Route::resource('project', 'App\Http\Controllers\ProjectController');
//项目搜索
Route::post('project_search', [ProjectController::class, 'project_search'])->name('project_search');
//编辑绑定项目
Route::get('/project/editbind/{id}', 'App\Http\Controllers\ProjectController@editBindProject')->name('bind.project');
//编辑绑定项目提交逻辑
Route::post('/project/updatebind/{id}', 'App\Http\Controllers\ProjectController@updateBindProject')->name('bind.update');
//角色管理列出URI
Route::get('/roles/geturi/{key}', 'App\Http\Controllers\RoleController@listuri')->name('roles.listuri');
//权限表
Route::resource('permission', 'App\Http\Controllers\PermissionController');
//项目投资订单
Route::resource('order1', 'App\Http\Controllers\Order1Controller');
//商城订单
Route::resource('order2', 'App\Http\Controllers\Order2Controller');
//生活缴费订单
Route::resource('order3', 'App\Http\Controllers\Order3Controller');
//用户资产流水账
Route::resource('asset', 'App\Http\Controllers\FinancialAssetController');
Route::post('asset_search', 'App\Http\Controllers\FinancialAssetController@asset_search')->name('asset_search');
//用户余额流水账
Route::resource('balance', 'App\Http\Controllers\FinancialBalanceController');
Route::post('balance_search', 'App\Http\Controllers\FinancialBalanceController@balance_search')->name('balance_search');
//用户积分流水账
Route::resource('integration', 'App\Http\Controllers\FinancialIntegrationController');
Route::post('integration_search', 'App\Http\Controllers\FinancialIntegrationController@integration_search')->name('integration_search');
//平台币流水账
Route::resource('platformcoin', 'App\Http\Controllers\FinancialPlatformCoinController');
Route::post('platformcoin_search', 'App\Http\Controllers\FinancialPlatformCoinController@platformcoin_search')->name('platformcoin_search');
//资产充值审核
Route::resource('charge', 'App\Http\Controllers\AssetCheckController');
Route::post('charge_search','App\Http\Controllers\AssetCheckController@charge_search')->name('charge_search');
//余额提现审核
Route::resource('withdrawal', 'App\Http\Controllers\BalanceCheckController');
Route::post('withdrawal_search','App\Http\Controllers\BalanceCheckController@withdrawal_search')->name('withdrawal_search');
//用户钱包列表
Route::resource('wallet', 'App\Http\Controllers\WalletController');
Route::post('wallet_search', 'App\Http\Controllers\WalletController@wallet_search')->name('wallet_search');
//用户列表
Route::resource('customer', 'App\Http\Controllers\CustomerController');
Route::post('customer_search','App\Http\Controllers\CustomerController@customer_search')->name('customer_search');
Route::get('kickout/{id}', 'App\Http\Controllers\CustomerController@kickout')->name('customer.kickout'); //踢出会员界面
Route::get('kick/{id}', 'App\Http\Controllers\CustomerController@kick')->name('customer.kick'); //踢出会员
Route::get('customer_charge/{id}', 'App\Http\Controllers\CustomerController@charge')->name('customer.charge'); //上分
Route::get('customer_withdrawal/{id}', 'App\Http\Controllers\CustomerController@withdrawal')->name('customer.withdrawal'); //下分
Route::get('customer_password/{id}', 'App\Http\Controllers\CustomerController@modify_pass')->name('customer.modify_pass'); //下分
Route::post('customer_password1', [CustomerController::class, 'customer_password1'])->name('customer.password1'); //存儲密碼1
Route::post('customer_password2', [CustomerController::class, 'customer_password2'])->name('customer.password2'); //存儲密碼2
Route::post('charge_financial_balance', [CustomerController::class, 'charge_financial_balance'])->name('charge.financial_balance'); //存儲財務餘額
Route::post('charge_financial_asset', [CustomerController::class, 'charge_financial_asset'])->name('charge.financial_asset'); //儲存金融資產
Route::post('charge_financial_integration', [CustomerController::class, 'charge_financial_integration'])->name('charge.financial_integration'); //門店財務整合
Route::post('charge_financial_platform_coin', [CustomerController::class, 'charge_financial_platform_coin'])->name('charge.financial_platform_coin'); //存儲金融平台幣
Route::post('withdraw_financial_balance', [CustomerController::class, 'withdraw_financial_balance'])->name('withdraw.financial_balance'); //門店提款餘額
Route::post('withdraw_financial_asset', [CustomerController::class, 'withdraw_financial_asset'])->name('withdraw.financial_asset'); //存入和提取金融資產
Route::post('withdraw_financial_integration', [CustomerController::class, 'withdraw_financial_integration'])->name('withdraw.financial_integration'); //商店撤回財務整合
Route::post('withdraw_financial_platform_coin', [CustomerController::class, 'withdraw_financial_platform_coin'])->name('withdraw.financial_platform_coin'); //商店取款金融平台幣
Route::get('team/{id}','App\Http\Controllers\CustomerController@team')->name('customer.team');
Route::get('list_children/{id}','App\Http\Controllers\CustomerController@list_children')->name('customer.list_children');
Route::post('team_search', [CustomerController::class,'team_search'])->name('team_search');



//返息明细
Route::resource('interest', 'App\Http\Controllers\InterestController');
Route::post('interest_search', 'App\Http\Controllers\InterestController@interest_search')->name('interest_search');

Route::get("/check_count",  [App\Http\Controllers\HomeController::class, 'check_count'])->name('check.count');
//用户登录日志
Route::resource('loginlog', 'App\Http\Controllers\LoginLogController');
Route::post('loginlog_search', 'App\Http\Controllers\LoginLogController@loginlog_search')->name('loginlog_search');

//幻灯片管理
Route::resource('slide', 'App\Http\Controllers\SlideController');

//币价管理
Route::resource('currency', 'App\Http\Controllers\CurrencyController');

//IP黑名单
Route::resource('blockip','App\Http\Controllers\BlockipController');

//is_sheep
Route::post('/change_sheep','App\Http\Controllers\CustomerController@change_sheep')->name('change_sheep');

//set_sheep
Route::post('/set_sheep','App\Http\Controllers\CustomerController@set_sheep')->name('set_sheep');

//unset_sheep
Route::post('/unset_sheep','App\Http\Controllers\CustomerController@unset_sheep')->name('unset_sheep');

//查询下级一级用户
Route::get('/level1/{parentid}', 'App\Http\Controllers\CustomerController@queryLevel1')->name('customer.level1');
//查询其他级别的下级用户
Route::get('/levelx/{id}', 'App\Http\Controllers\CustomerController@queryLevelx')->name('customer.levelx');
//交易所管理
Route::resource('trade', 'App\Http\Controllers\TradeController');
//trade_goods search
Route::post('product_search','App\Http\Controllers\TradeController@product_search')->name('product_search');
//交易所商品
Route::resource('/financial_productions','App\Http\Controllers\FinancialProductionController');
