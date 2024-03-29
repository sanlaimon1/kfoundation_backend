<!DOCTYPE html>
<html lang="zh">
<head>
    <title>文章列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app
        {
            padding-top: 1rem;
        }
        #app td
        {
            height: 20px;
            line-height: 20px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">

        <nav aria-label="breadcrumb" style="margin-top: 1rem;">
        <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('article.index') }}">文章列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">細節文章列表</li>
            </ol>
        </nav>        
        <br />

        <ul class="list-group list-group-flush" style="margin-top:1rem;">
            <li class="list-group-item"><strong>标题:</strong> {{$article->title}} </li>
            <li class="list-group-item"><strong>分类:</strong> {{$article->category->cate_name}} </li>
            <li class="list-group-item"><strong>管理员id:</strong> {{$article->admin->username}} </li>
            @php
                $content = html_entity_decode($article->content)
            @endphp
            <li class="list-group-item"><strong>内容:</strong> <br>{!! $content !!}</li>
            <li class="list-group-item"><strong>排序:</strong> {{$article->sort}} </li>
            <li class="list-group-item"><strong>缩略图:</strong> <img src="{{ $article->litpic }}" width="120" height="120" /> </li>
        </ul>

    </div>
</body>
</html>