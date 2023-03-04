<?php

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
//系统日志
Route::resource('log', 'App\Http\Controllers\LogController');
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
//系统图片设置
Route::get('/slide', 'App\Http\Controllers\HomeController@slide')->name('slide');
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
//项目管理
Route::resource('project', 'App\Http\Controllers\ProjectController');