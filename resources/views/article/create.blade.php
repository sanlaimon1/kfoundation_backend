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
    <script src="/ckeditor/ckeditor.js"></script>
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
        .choose_litpic{
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

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show_litpic').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#litpic").change(function(){
                readURL(this);
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
                    <a href="{{ route('article.index') }}">文章列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">创建文章列表</li>
            </ol>
        </nav>

        <form action="{{ route('article.store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="mb-3 col-6">
                        <label for="title" class="form-label">标题</label>
                        @error('title')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="title" name="title" placeholder="标题" value="">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="lang" class="form-label">语言</label>
                        @error('lang')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="lang" name="lang" class="form-select" >
                            <option value="cn">简体中文</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="content" class="form-label">内容</label>
                        @error('content')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <textarea class="form-control" id="summernote" name="content"></textarea>
                        <script>
                           CKEDITOR.replace('summernote',{
                                language: 'zh'
                            });
                        </script>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="categoryid" class="form-label">分类</label>
                        @error('categoryid')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="categoryid" name="categoryid" class="form-select" >
                            @foreach( $categories as $category )
                            <option value="{{ $category->id }}"> {{ $category->cate_name }} </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" name="sort" id="sort" placeholder="排序" value="">
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="shown" class="form-label">标题</label>
                        @error('shown')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select name="shown"  class="form-select" id="">
                            <option value="1">显示</option>
                            <option value="0">隐藏</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="created_at" class="form-label">标题</label>
                        @error('created_at')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="datetime-local" class="form-control" id="created_at" name="created_at" placeholder="添加时间" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3">
                        <label for="litpic" class="form-label">缩略图</label>
                        @error('litpic')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <img id="show_litpic" src="{{asset('images/default.png')}}" width="120" height="120" />

                        <input type="file" id="litpic" name="litpic" hidden/>
                        <label class="choose_litpic" for="litpic">选择</label>
                    </div>
                </div>

            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">添加</button>
        </form>

    </div>
    @include('loading')
    @include('modal')
    <script>
        const dateInput = document.getElementById('add_time');
        $(document).ready(function() {
            dateInput.addEventListener('change',function()
            {
                const dateTimeString = dateInput.value;
                console.log(dateTimeString);
                const formattedDateTimeString = dateTimeString.replace("T", " ").replace(/\.\d{3}Z/, "");
                $("#add_time_formatted").val(formattedDateTimeString);

            })
        });
    </script>
</body>
</html>
