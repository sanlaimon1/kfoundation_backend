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
        .choose_logo{
            display: inline-block;
            background-color: #ffc107;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.3rem;
            cursor: pointer;
            margin-top: 1rem;
            color: #000
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

            $("#picture").change(function(){
                readURL(this);
            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-logo').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }
        });
    </script>

</head>

<body>
    <div id="app" class="container-fluid">
       <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">生活服务</li>
                <li class="breadcrumb-item active" aria-current="page">编辑商品</li>
            </ol>
        </nav>
        
        <form action="{{ route('life.update',['life'=>$life->id]) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('PATCH')
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="production_name" class="form-label">商品名称</label>
                        @error('production_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="production_name" name="production_name" placeholder="商品名称" value="{{$life->production_name}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="sort" name="sort" placeholder="排序" value="{{$life->sort}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">项目图片</label>
                        @error('picture')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <img id="show-logo" src="{{$life->picture}}" width="120" height="120" />
                        <input type="hidden" name="old_picture" value="{{$life->picture}}">

                        <input type="file" class="form-control" id="picture" name="picture" placeholder="项目图片" hidden>
                        <label class="choose_logo" for="picture">选择</label>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3">
                        <label for="extra" class="form-label">其他选项</label>
                        @error('extra')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="extra" name="extra" placeholder="其他选项" value="{{$life->extra}}">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3">
                        <label for="inputs" class="form-label">输入选项</label>
                        @error('inputs')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="inputs" name="inputs" placeholder="输入选项" value="{{$life->inputs}}">
                    </div>
                </div>
                
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">添加</button>
            <a class="btn btn-secondary" href="{{ route('life.index') }}" style="margin-top:1rem; margin-right:1rem; float:right;">取消</a>
        </form>
        
    </div>
    @include('loading')
    @include('modal')
</body>
</html>