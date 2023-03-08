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
        #app .form-label
        {
            color: green;
            font-size: 14px;
        }
        #app .alert
        {
            font-size: 14px;
        }
        #app .frame
        {
            border: 1px solid black;
            border-radius:5px;
            margin-top: .5rem;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function(){
            $('button.btn[data]').click(function(){
                $('.loading').show();
                var dataid = $(this).attr('data');
                var config_value_string = $('#item-' + dataid).val();
                
                $.ajax({
                    type: "patch",
                    url: '/website/' + dataid,
                    dataType: "json",
                    data: { config_value:  config_value_string },
                    success: function(msg){
                        $('.modal-body').html(msg.message);
                        $('#myModal').show();
                        $('.loading').hide();   //关闭动画  close the loading animation
                        //window.reload();
                    },
                    'error': function (jqXHR, textStatus, errorThrown) {
                        if(jqXHR.status==419) {
                            $('.modal-body').html('网页已过期, 请刷新后再修改数据');
                            $('#myModal').show();
                        } else if(jqXHR.status==500) {
                            $('.modal-body').html('服务器内部错误 500');
                            $('#myModal').show();
                        } else {
                            $('.modal-body').html(errorThrown);
                            $('#myModal').show();
                        }
                        $('.loading').hide();
                    }
                });
            });

            $('button.btn-close, #btn-close').click(function(){
                $('#myModal').hide();
                location.reload();
            });
        });
    </script>
    <!-- include summernote css/js -->
    <link href="/static/adminlte/plugins/summernote/summernote.min.css" rel="stylesheet">
    <script src="/static/adminlte/plugins/summernote/summernote.min.js"></script>

</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('article.index') }}">文章列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑文章列表</li>
            </ol>
        </nav>
        
        <form action="{{ route('article.update',['article'=>$article->id]) }}" method="post">
            {{ csrf_field() }}
            @method('PATCH')
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="title" class="form-label">标题</label>
                        @error('title')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="title" name="title" placeholder="标题" value="{{$article->title}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="content" class="form-label">内容</label>
                        @error('content')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <textarea class="form-control" id="summernote" name="content">{{ html_entity_decode( $article->content ) }}</textarea>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="categoryid" class="form-label">分类</label>
                        @error('categoryid')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="categoryid" name="categoryid" class="form-select" >
                            @foreach( $categories as $category )
                            <option value="{{ $category->id }}" @if($category->id == $article->categoryid) selected @endif> {{ $category->cate_name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">编辑</button>
            <button class="btn btn-secondary" action="action" onclick="window.history.go(-1); return false;" style="margin-top:1rem; margin-right:1rem; float:right;">返回</button>
            <!-- <a class="btn btn-secondary" href="{{ route('article.index') }}" style="margin-top:1rem; margin-right:1rem; float:right;">返回</a> -->
        </form>
        
    </div>
    @include('loading')
    @include('modal')

    <script>
        $(document).ready(function() {
            $('#summernote').summernote();
        });
    </script>

</body>
</html>