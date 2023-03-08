<!DOCTYPE html>
<html lang="zh">
<head>
    <title>参数设置</title>
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

</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('category.index') }}">文章分类</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑文章分类 <strong style="color:red;">{{ $category->cate_name }}</strong></li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        
        <form action="{{ route('category.update',['category'=>$category->id]) }}" method="post">
            {{ csrf_field() }}
            @method('PATCH')
            <input type="hidden" name="id" value="{{ $category->id }}" />
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="cate_name" class="form-label">分类名称</label>
                        @error('cate_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="cate_name" name="cate_name" placeholder="分类名称" value="{{ $category->cate_name }}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="cate_name" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="sort" name="sort" placeholder="排序" value="{{ $category->sort }}">
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">修改</button>
            <a class="btn btn-secondary" href="{{ route('category.index') }}" style="margin-top:1rem; margin-right:1rem; float:right;">取消</a>
        </form>
        
    </div>
    @include('loading')
    @include('modal')

</body>
</html>