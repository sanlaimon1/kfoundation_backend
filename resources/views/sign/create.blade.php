<!DOCTYPE html>
<html lang="zh">
<head>
    <title>创建签到</title>
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
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">奖励管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('sign.index') }}">签到日期列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">创建</li>
            </ol>
        </nav>

        <form action="{{ route('sign.store') }}" method="post">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="signdate" class="form-label">日期</label>
                        @error('signdate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="signdate" 
                            name="signdate" placeholder="日期" value="{{ old('signdate') }}" required />
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