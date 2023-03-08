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
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">登录密码</h5>
                <label>密码:</label>
                <input type="password" name="password" class="form-control" />
                <label>确认密码:</label>
                <input type="password" name="confirm_password" class="form-control" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger" style="float:right;">修改</button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">取款密码</h5>
                <label>密码:</label>
                <input type="password" name="password2" class="form-control" />
                <label>确认密码:</label>
                <input type="password" name="confirm_password2" class="form-control" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger" style="float:right;">修改</button>
            </div>
        </div>
        
    </div>
</body>
</html>
