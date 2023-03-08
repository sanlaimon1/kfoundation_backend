<!DOCTYPE html>
<html lang="zh">
<head>
    <title>用户登录日志列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    
</head>

<body>
    <div class="container-fluid">

        <nav aria-label="breadcrumb" style="margin-top: 1rem;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('loginlog.index') }}">用户登录日志列表</a></li>
                <li class="breadcrumb-item active" aria-current="page">查看管理</li>
            </ol>
        </nav>

        <br />

        <ul class="list-group list-group-flush" style="margin-top:1rem;">
            <li class="list-group-item"><strong>ID:</strong> {{ $one->id }}</li>
            <li class="list-group-item"><strong>手机号:</strong> {{ $one->customer->phone }}</li>
            <li class="list-group-item"><strong>操作:</strong> {{ $one->action }}</li>
            <li class="list-group-item"><strong>IP:</strong> {{ $one->ip }}</li>
            <li class="list-group-item"><strong>国家:</strong> {{ $one->state }}</li>
            <li class="list-group-item"><strong>省:</strong> {{ $one->province }}</li>
            <li class="list-group-item"><strong>市:</strong> {{ $one->city }}</li>
            <li class="list-group-item"><strong>ISP运营商:</strong> {{ $one->isp }}</li>
            <li class="list-group-item"><strong>登录时间:</strong> {{ $one->created_at }}</li>
        </ul>

    </div>
</body>
</html>