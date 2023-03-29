<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Log;
use App\Rules\ForbidUserRule;
use Illuminate\Support\Facades\DB;
use App\Jobs\LogFile;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('injection')->only('login');

        //$this->middleware('subscribed')->except('store');
    }

    /**
     * Get the login username to be used by the controller.
     * 指定用户名字段
     * @return string
     */
    public function username()
    {
        return 'username';
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $request->validate([
            $this->username() => ['required', 'string', 'between:5,10','exists:admins', new ForbidUserRule] ,
            'password' => 'required|string|between:6,10',
        ]);

        $username = trim( $request->get('username') );
        $password = trim( $request->get('password') );
        //查询一个用户
        $oneuser = User::where( $this->username(), $username )->first();
        if(empty($oneuser)) {
            return '用户不存在';
        }

        //构建加盐密码
        $salt = $oneuser->salt;
        //$saltpassword = md5( md5( $salt . $password ) . $salt );
        $auth = Auth::attempt( [$this->username()=>$username, 'password' => md5( $salt . $password ) . $salt ] );

        if($auth) {
            DB::beginTransaction();
            try {
                if( $oneuser->tick !== 5 )
                {
                    $oneuser->tick = 5;
                }
                
                $oneuser->login_at = date('Y-m-d H:i:s');
                if(!$oneuser->save())
                    throw new \Exception('登录事务中断1');

                $newlog = new Log;
                $newlog->adminid = $oneuser->id;
                $newlog->action = '管理员' . $username . '后台登录';
                $newlog->ip = $request->ip();
                $newlog->route = 'login';
                $newlog->parameters = json_encode( ['username'=>$username] );
                $newlog->created_at = date('Y-m-d H:i:s');
                if(!$newlog->save())
                    throw new \Exception('登录事务中断2');

                DB::commit();

                $method = "login";
                $message = $username . " 登录成功";
                dispatch(new LogFile($method, $message));

            } catch (\Exception $e) {
                DB::rollback();
                /**
                 * $errorMessage = $e->getMessage();
                 * $errorCode = $e->getCode();
                 * $stackTrace = $e->getTraceAsString();
                 */
                $method = "error";
                $message = $e->getMessage();
                dispatch(new LogFile($method, $message));

                $this->guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                //$errorMessage = $e->getMessage();
                //return $errorMessage;
                return redirect('/login')->with('pass_error', '登录错误，事务回滚');
            }

            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        } else {
            $error_message = '密码错误';
            if( $oneuser->tick<=0 )
            {
                $error_message = '您的账号已锁定，请联系管理员';
                $oneuser->tick = 0;
                $oneuser->status = 0;
                $oneuser->save();
            } else if($oneuser->tick>0 && $oneuser->tick<=5) {
                $oneuser->tick = $oneuser->tick - 1;    //尝试次数减1
                $oneuser->save();
                $error_message = '密码错误,您还有' . $oneuser->tick . '次机会';
                if($oneuser->tick===0)
                    $error_message = '您的账号已锁定，请联系管理员';
            } else {
                $error_message = '密码错误';
                $oneuser->tick = 5;
                $oneuser->save();
            }

            return redirect('/login')->with('pass_error', $error_message);
        }
    }

    /**
     * 用户登出
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        $method = "logout";
        $message = "註銷成功";
        dispatch(new LogFile($method, $message));

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/login');
    }

}
