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
        .box1, .box2
        {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item active" aria-current="page">文章列表</li>
            </ol>
        </nav>
        <a href="{{ route('article.create') }}" class="btn btn-primary">创建文章</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>标题</th>
                    <th>内容</th>
                    <!-- <th>分类</th> -->
                    <th>管理员id</th>
                    <th>排序</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($articles as $article)
                <tr>
                    <td>{{ $article->id }}</td>
                    <td>{{ $article->title }}</td>
                    <td>{{ $article->category->cate_name }}</td>
                    <td>{{ $article->admin->username }}</td>
                    <td>{{ $article->sort }}</td>
                    <td>
                        <a href="{{ route('article.show', ['article'=>$article->id]) }}" class="btn btn-primary mx-2">查看</a>

                        <a href="{{ route('article.edit', ['article'=>$article->id]) }}" class="btn btn-warning">编辑</a>

                        <form action="{{ route('article.destroy', ['article'=>$article->id]) }}"
                         method="post"
                         style="float:right;" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <footer style="display:flex;">
        <div class="container-fluid">
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $articles->count() }}</strong>条数据
                </aside>
                {{ $articles->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('article.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $articles->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $articles->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $articles->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $articles->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $articles->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

        </footer>


    </div>
    <script>
    function del() {
        var msg = "您真的确定要删除吗？\n\n请确认！";
        if (confirm(msg)==true){
            return true;
        }else{
            return false;
        }
    }
    </script>
</body>
</html>
