<!DOCTYPE html>
<html lang="zh">
<head>
    <title>修改密码</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <style>
        ul.row
        {
            padding-left:0;
        }
        strong.title
        {
            color:red;
        }
    </style>
</head>

<body>
    <div class="container-fluid">

        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">用户列表 </a></li>
                <li class="breadcrumb-item active" aria-current="page">修改密码</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary" style="margin-bottom: 0px;">修改密码</h3>
        <div class="card my-4">
            <form action="{{route('customer.password1')}}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="card-body">
                    <h5 class="card-title">登录密码</h5>
                    <label for="password" class="form-label">密码:</label>
                    @error('password')
                        <div class="alert alert-danger">密码必须包含大小写字母和数字的组合</div>
                    @enderror
                    <input type="password" name="password" id="password" class="form-control" />
                    <label for="password_confirmation" class="form-label my-3">确认密码:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" />
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger" style="float:right;">修改</button>
                </div>
            </form>
        </div>
        <div class="card">
            <form action="{{route('customer.password2')}}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="card-body">
                    <h5 class="card-title">取款密码</h5>
                    <label for="password2" class="from-label">密码:</label>
                    @error('password2')
                        <div class="alert alert-danger">必须是6位数字</div>
                    @enderror
                    <input type="password" name="password2" id="password2" class="form-control" />

                    <label for="password2_confirmation" class="from-label my-3">确认密码:</label>
                    <input type="password" name="password2_confirmation" id="password2_confirmation" class="form-control" />
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger" style="float:right;">修改</button>
                </div>
            </form>
        </div>
        
    </div>
</body>
</html>
