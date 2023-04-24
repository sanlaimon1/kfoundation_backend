<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Admin;
use App\Models\Permission;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;
use App\Models\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log as LogFile;

class ArticleController extends Controller
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
    private $path_name = "/article";

    public function __construct()
    {
        $this->middleware('auth');  //用户权限
        $this->middleware('injection');
        //$this->middleware('injection')->only('login');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();
        $static_url = config("app.static_url");

        if (!(($permission->auth2 ?? 0) & 1)) {
            return "您没有权限访问这个路径";
        }

        $perPage = $request->input('perPage', 10);
        $articles = Article::select('id', 'title', 'content', 'categoryid', 'adminid', 'litpic', 'sort')
                    ->orderBy('sort', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);

        if (!Redis::exists("article:homepage:md5")) {
            $article = Article::select('id', 'title', 'litpic')
                        ->orderBy('sort', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->get();

            $array_article = [];
            foreach ($article as $one) {
                $data['id'] = $one->id;
                $data['title'] = $one->title;
                if($one->litpic == null){
                    $litpic = $static_url . "/images/default.png" ;
                }else {
                    $litpic = $static_url . $one->litpic;
                }
                $data['litpic'] = $litpic;
                array_push($array_article, $data);
            };
            $redis_article = json_encode($array_article);
            Redis::set( "article:homepage:string", $redis_article );
            Redis::set( "article:homepage:md5", md5($redis_article) );
        }
        return view('article.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 2)) {
            return "您没有权限访问这个路径";
        }

        $admins = Admin::select('id', 'username')->get();
        $categories = Category::select('id', 'cate_name')->where('enable', 1)->orderBy('sort', 'asc')->get();

        return view('article.create', compact('admins', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $static_url = config("app.static_url");
        if (Redis::exists("permission:" . Auth::id())) {
            $arr = ['code' => -1, 'message' => config('app.redis_second') . '秒内不能重复提交'];
            return json_encode($arr);
        }
        Redis::set("permission:" . Auth::id(), time());
        Redis::expire("permission:" . Auth::id(), config('app.redis_second'));
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 4)) {
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'title' => ['required', 'string'],
            'content' => ['required'],
            'categoryid' => ['required', 'integer', 'exists:categories,id'],
            'litpic.*' => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            'sort' => ['required', 'integer', 'gt:0'],
            'shown' => 'required',
            'lang' => 'required'
        ]);

        $litpic = '/images/default.png';
        if ($request->hasFile('litpic')) {
            $litpic = time() . '.' . $request->litpic->extension();
            $request->litpic->move(public_path('/images/articleImg/'), $litpic);
            $litpic = '/images/articleImg/' . $litpic;
        }

        $title = trim($request->get('title'));
        $content = trim(htmlspecialchars($request->get('content')));
        $categoryid = trim($request->get('categoryid'));
        $sort = trim($request->sort);
        $lang = trim($request->get('lang'));
        DB::beginTransaction();
        try {
            //code...
            $newarticle = new Article;
            $newarticle->title = $title;
            $newarticle->content = $content;
            $newarticle->categoryid = $categoryid;
            $newarticle->sort  = $sort;
            $newarticle->litpic  = $litpic;
            $newarticle->shown = $request->shown;
            $newarticle->lang = $lang;
            $newarticle->adminid = Auth::id();

            if (!$newarticle->save())
                throw new \Exception('事务中断1');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $store_action = ['username' => $username, 'type' => 'log.store_action'];
            $action = json_encode($store_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'article.store';
            $input = $request->all();
            $input_json = json_encode($input);
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if (!$newlog->save())
                throw new \Exception('事务中断2');

            $articles = array(
                'id' => $newarticle->id,
                'title' => $newarticle->title,
                'content' => $newarticle->content,
                'categoryid' => $newarticle->categoryid,
                'adminid' => $newarticle->adminid,
                'litpic' => $newarticle->litpic,
                'sort' => $newarticle->sort,
                'shown' => $newarticle->shown,
                'lang' => $newarticle->lang,
                'created_at' => $newarticle->created_at,
                'updated_at' => $newarticle->updated_at
            );
            $article_json = json_encode($articles);
            DB::commit();
            LogFile::channel("article_store")->info($article_json);

            $old_redis_article = Redis::get("article:homepage:md5");
            $article = Article::select('id', 'title', 'litpic')
                        ->orderBy('sort', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->get();

            $array_article = [];
            foreach ($article as $one) {
                $data['id'] = $one->id;
                $data['title'] = $one->title;
                if($one->litpic == null){
                    $litpic = $static_url . "/images/default.png" ;
                }else {
                    $litpic = $static_url . $one->litpic;
                }
                $data['litpic'] = $litpic;
                array_push($array_article, $data);
            };
            $redis_article = json_encode($array_article);

            if (md5($redis_article) != $old_redis_article) {
                Redis::set( "article:homepage:string", $redis_article );
                Redis::set( "article:homepage:md5", md5($redis_article) );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("article_store_error")->error($message);
            return '添加错误，事务回滚' . $e->getMessage();
        }
        return redirect()->route('article.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 8)) {
            return "您没有权限访问这个路径";
        }

        $article = Article::find($id);
        return view('article.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 16)) {
            return "您没有权限访问这个路径";
        }

        $article = Article::find($id);
        $categories = Category::select('id', 'cate_name')->where('enable', 1)->orderBy('sort', 'asc')->get();
        $admins = Admin::select('id', 'username')->get();
        return view('article.edit', compact('article', 'categories', 'admins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $static_url = config("app.static_url");
        if (Redis::exists("permission:" . Auth::id())) {
            $arr = ['code' => -1, 'message' => config('app.redis_second') . '秒内不能重复提交'];
            return json_encode($arr);
        }
        Redis::set("permission:" . Auth::id(), time());
        Redis::expire("permission:" . Auth::id(), config('app.redis_second'));
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 32)) {
            return "您没有权限访问这个路径";
        }

        $request->validate([
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'categoryid' => ['required', 'integer', 'exists:categories,id'],
            'litpic.*' => 'required|sometimes|image|mimes:jpg,png,jpeg,bmp,webp',
            'sort' => ['required', 'integer', 'gt:0'],
            'shown' => 'required',
            'lang' => 'required'
        ]);

        if ($request->hasFile('litpic')) {
            $litpic = time() . '.' . $request->litpic->extension();
            $request->litpic->move(public_path('/images/articleImg/'), $litpic);
            $litpic = '/images/articleImg/' . $litpic;
        } else {
            $litpic = $request->litpic;
        }

        $title = trim($request->get('title'));
        $content = trim(htmlspecialchars($request->get('content')));
        $categoryid = trim($request->get('categoryid'));
        $sort = trim($request->sort);
        $lang = trim($request->get('lang'));

        DB::beginTransaction();
        try {
            //code...
            $newarticle = Article::find($id);
            $newarticle->title = $title;
            $newarticle->content = $content;
            $newarticle->sort  = $sort;
            $newarticle->litpic  = $litpic;
            $newarticle->categoryid = $categoryid;
            $newarticle->shown = $request->shown;
            $newarticle->lang = $lang;

            if (!$newarticle->save())
                throw new \Exception('事务中断3');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $update_action = ['username' => $username, 'type' => 'log.update_action'];
            $action = json_encode($update_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'article.update';
            $input = $request->all();
            $input_json = json_encode($input);
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if (!$newlog->save())
                throw new \Exception('事务中断4');


            $articles = array(
                'id' => $newarticle->id,
                'title' => $newarticle->title,
                'content' => $newarticle->content,
                'categoryid' => $newarticle->categoryid,
                'adminid' => $newarticle->adminid,
                'litpic' => $newarticle->litpic,
                'sort' => $newarticle->sort,
                'shown' => $newarticle->shown,
                'lang' => $newarticle->lang,
                'created_at' => $newarticle->created_at,
                'updated_at' => $newarticle->updated_at
            );
            $article_json = json_encode($articles);

            DB::commit();

            LogFile::channel("article_update")->info($article_json);

            $old_redis_article = Redis::get("article:homepage:md5");
            $article = Article::select('id', 'title', 'litpic')
                        ->orderBy('sort', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->get();

            $array_article = [];
            foreach ($article as $one) {
                $data['id'] = $one->id;
                $data['title'] = $one->title;
                if($one->litpic == null){
                    $litpic = $static_url . "/images/default.png" ;
                }else {
                    $litpic = $static_url . $one->litpic;
                }
                $data['litpic'] = $litpic;
                array_push($array_article, $data);
            };
            $redis_article = json_encode($array_article);

            if (md5($redis_article) != $old_redis_article) {
                // Redis::set("article:homepage", md5($redis_article));
                Redis::set( "article:homepage:string", $redis_article );
                Redis::set( "article:homepage:md5", md5($redis_article) );
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel("article_update_error")->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('article.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id, Request $request)
    {
        $static_url = config("app.static_url");
        if (Redis::exists("permission:" . Auth::id())) {
            $arr = ['code' => -1, 'message' => config('app.redis_second') . '秒内不能重复提交'];
            return json_encode($arr);
        }

        Redis::set("permission:" . Auth::id(), time());
        Redis::expire("permission:" . Auth::id(), config('app.redis_second'));
        $role_id = Auth::user()->rid;
        $permission = Permission::where("path_name", "=", $this->path_name)->where("role_id", "=", $role_id)->first();

        if (!(($permission->auth2 ?? 0) & 64)) {
            return "您没有权限访问这个路径";
        }

        DB::beginTransaction();
        try {
            //code...
            $article = Article::find($id);
            $articles = array(
                'id' => $article->id,
                'title' => $article->title,
                'content' => $article->content,
                'categoryid' => $article->categoryid,
                'adminid' => $article->adminid,
                'litpic' => $article->litpic,
                'sort' => $article->sort,
                'shown' => $article->shown,
                'lang' => $article->lang,
                'created_at' => $article->created_at,
                'updated_at' => $article->updated_at
            );
            $article_json = json_encode($articles);
            if (!$article->delete())
                throw new \Exception('事务中断5');

            $username = Auth::user()->username;
            $newlog = new Log();
            $newlog->adminid = Auth::id();
            $delete_action = ['username' => $username, 'type' => 'log.delete_action'];
            $action = json_encode($delete_action);
            $newlog->action = $action;
            $newlog->ip = $request->ip();
            $newlog->route = 'article.destroy';
            $input = $request->all();
            $input_json = json_encode($input);
            $newlog->parameters = $input_json;  // 请求参数
            $newlog->created_at = date('Y-m-d H:i:s');

            if (!$newlog->save())
                throw new \Exception('事务中断6');


            DB::commit();
            LogFile::channel('article_destroy')->info($article_json);

            $old_redis_article = Redis::get("article:homepage:md5");
            $article = Article::select('id', 'title', 'litpic')
                        ->orderBy('sort', 'asc')
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->get();

            $array_article = [];
            foreach ($article as $one) {
                $data['id'] = $one->id;
                $data['title'] = $one->title;
                if($one->litpic == null){
                    $litpic = $static_url . "/images/default.png" ;
                }else {
                    $litpic = $static_url . $one->litpic;
                }
                $data['litpic'] = $litpic;
                array_push($array_article, $data);
            };
            $redis_article = json_encode($array_article);
            if (md5($redis_article) != $old_redis_article) {
                Redis::set( "article:homepage:string", $redis_article );
                Redis::set( "article:homepage:md5", md5($redis_article) );
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = $e->getMessage();
            LogFile::channel('article_destroy_error')->error($message);
            return '添加错误，事务回滚';
        }

        return redirect()->route('article.index');
    }

    public function article_notice(Request $request)
    {
        if (!Redis::exists("notice:homepage:md5")) {

            $notice = Article::select('id', 'title')
                        ->where('categoryid', '=', 4)
                        ->orderBy('sort','asc')
                        ->orderBy('created_at', 'desc')
                        ->limit(6)
                        ->get();

            $array_notice = [];
            foreach ($notice as $one) {
                $data['id'] = $one->id;
                $data['title'] = $one->title;
                $array_notice[] = $data;
            };
            $redis_notice = json_encode($array_notice);

            Redis::set("notice:homepage:string", $redis_notice);
            Redis::set("notice:homepage:md5", md5( $redis_notice ));
        }
    }
}
