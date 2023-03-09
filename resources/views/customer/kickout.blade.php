<!DOCTYPE html>
<html lang="zh">
<head>
    <title>踢出</title>
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
                <li class="breadcrumb-item active" aria-current="page">踢出</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary" style="margin-bottom: 0px;">踢出</h3>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">踢下线</h5>
                @if($one->access_token=='null')
                <p class="card-text"><strong class="title">{{ $one->phone }}</strong>的在线状态已被清除</p>
                @else
                <p class="card-text">您确定将<strong class="title">{{ $one->phone }}</strong>踢下线吗？</p>
                @endif
            </div>
            <div class="card-footer">
                <a class="btn btn-primary" href="{{ route('customer.index') }}">返回用户列表</a>
                @if($one->access_token!='null')
                <a class="btn btn-danger" style="float:right;" href="{{ route('customer.kick', ['id'=>$one->id]) }}">确定</a>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
