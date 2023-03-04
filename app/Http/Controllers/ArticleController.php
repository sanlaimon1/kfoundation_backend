<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Admin;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
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
        $articles = Article::select('id', 'title','content','categoryid','adminid')->orderBy('created_at', 'desc')->paginate(10);

        return view('article.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $admins = Admin::select('id','username')->get();
        $categories = Category::select('id','cate_name')->where('enable', 1)->orderBy('sort', 'asc')->get();
        
        return view('article.create', compact('admins' , 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'content' => ['required'],
            'categoryid' => ['required', 'string'],
        ]);

        $title = trim($request->get('title'));
        $content = trim( htmlspecialchars( $request->get('content') ));
        $categoryid = trim($request->get('categoryid'));

        $newarticle = new Article;
        $newarticle->title = $title;
        $newarticle->content = $content;
        $newarticle->categoryid = $categoryid;
        $newarticle->adminid = Auth::id();
        
        $newarticle->save();

        return redirect()->route('article.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $article = Article::find($id);
        $categories = Category::select('id','cate_name')->where('enable', 1)->orderBy('sort', 'asc')->get();
        $admins = Admin::select('id','username')->get();
        return view('article.edit', compact('article' ,'categories', 'admins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title' => ['required', 'string'],
            'content' => ['required', 'string'],
            'categoryid' => ['required', 'string'],
            'adminid' => ['required', 'integer']
        ]);

        $title = trim($request->get('title'));
        $content = trim($request->get('content'));
        $categoryid = trim($request->get('categoryid'));
        $adminid = trim($request->get('adminid'));

        $newarticle = Article::find($id);
        $newarticle->title = $title;
        $newarticle->content = $content;
        $newarticle->categoryid = $categoryid;
        $newarticle->adminid = $adminid;
        
        $newarticle->save();

        return redirect()->route('article.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = Article::find($id);
        $article->delete();
        return redirect()->route('article.index');
    }
}
