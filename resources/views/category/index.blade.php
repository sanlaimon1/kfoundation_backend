<!DOCTYPE html>
<html lang="zh">
<head>
    <title>信息管理</title>
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
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item active" aria-current="page">文章分类</li>
            </ol>
        </nav>
        <a href="{{ route('category.create') }}" class="btn btn-primary">创建文章分类</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>分类名称</th>
                    <th>排序</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->sort }}</td>
                    <td>{{ $category->cate_name }}</td>
                    <td>
                        <a href="{{ route('category.edit', ['category'=>$category->id]) }}" class="btn btn-warning">编辑</a>
                        |
                        <form action="{{ route('category.destroy', ['category'=>$category->id]) }}" 
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
            <aside style="line-height: 37px; margin-right: 2rem;">
            </aside>
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