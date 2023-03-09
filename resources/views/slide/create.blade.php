<!DOCTYPE html>
<html lang="zh">
<head>
    <title>幻灯片</title>
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
        .choose_picture_path{
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
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show_picture_path').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#picture_path").change(function(){
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
                    <a href="{{ route('slide.index') }}">幻灯片</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">创建幻灯片</li>
            </ol>
        </nav>
        
        <form action="{{ route('slide.store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="title" class="form-label">标题</label>
                        @error('title')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="title" name="title" placeholder="标题" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="link" class="form-label">超链接</label>
                        @error('link')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="link" name="link" placeholder="超链接" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="type" class="form-label">类型</label>
                        @error('type')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="type" name="type"  class="form-select" >
                            <option value="1">轮播</option>
                            <option value="0">图库</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">状态</label>
                        @error('status')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="status" name="status"  class="form-select" >
                            <option value="1">显示</option>
                            <option value="0">隐藏</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" name="sort" id="sort" placeholder="排序" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3">
                        <label for="picture_path" class="form-label">图片路径</label>
                        @error('picture_path')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <img id="show_picture_path" src="#" width="120" height="120" />
                        
                        <input type="file" id="picture_path" name="picture_path" hidden/>
                        <label class="choose_picture_path" for="picture_path">选择</label>
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">添加</button>
        </form>
        
    </div>
    @include('loading')
    @include('modal')
</body>
</html>