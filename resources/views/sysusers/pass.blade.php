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
    
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统用户管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('sysusers.index') }}">用户列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">修改密码</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{ route('sysusers.updatepass') }}" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $id }}" />
            <section class="row frame">
                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label for="password" class="form-label">密码</label>
                        @error('password')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="password" class="form-control" id="password" name="password" value="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-sm-12 mb-3">
                        <label for="cpassword" class="form-label">确认密码</label>
                        @error('cpassword')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="password" class="form-control" id="cpassword" name="cpassword" value="">
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">修改</button>
        </form>
        
    </div>
    @include('loading')
    @include('modal')

</body>
</html>