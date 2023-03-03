<!DOCTYPE html>
<html lang="zh">
<head>
    <title>查看订单状态</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    
</head>

<body>
    <div class="container-fluid">

        <nav aria-label="breadcrumb" style="margin-top: 1rem;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('log.index') }}">日志列表</a></li>
                <li class="breadcrumb-item active" aria-current="page">查看管理</li>
            </ol>
        </nav>

        <br />

        <ul class="list-group list-group-flush" style="margin-top:1rem;">
            <li class="list-group-item"><strong>ID:</strong> {{ $one->id }}</li>
            <li class="list-group-item"><strong>管理员:</strong> {{ $one->oneadmin->username }}</li>
            <li class="list-group-item"><strong>操作:</strong> {{ $one->action }}</li>
            <li class="list-group-item"><strong>IP:</strong> {{ $one->ip }}</li>
            <li class="list-group-item"><strong>路由:</strong> {{ $one->route }}</li>
            <li class="list-group-item"><strong>时间:</strong> {{ $one->created_at }}</li>
            <li class="list-group-item">
                <strong>请求数据:</strong>
                <br />
                <code>
                {{ $one->parameters }}
                </code>
            </li>
        </ul>

    </div>
</body>
</html>