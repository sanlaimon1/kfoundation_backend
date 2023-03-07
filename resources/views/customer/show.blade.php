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

        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">用户中心 </a></li>
                <li class="breadcrumb-item active" aria-current="page">查看详情</li>
            </ol>
        </nav>
        <br />
        <h3 class="text-center text-primary">客户详情</h3>

        <ul class="list-group list-group-flush" style="margin-top:1rem;">
            <li class="list-group-item"><strong>ID:</strong> {{ $customer->id }}</li>
            <li class="list-group-item"><strong>手机号:</strong> {{ $customer->phone }}</li>
            <li class="list-group-item"><strong>姓名:</strong> {{ $customer->realname }}</li>
            <li class="list-group-item"><strong>余额:</strong> {{ $customer->asset }}</li>
            <li class="list-group-item"><strong>资产:</strong> {{ $customer->balance }}</li>
            <li class="list-group-item"><strong>积分:</strong> {{ $customer->integration }}</li>
            <li class="list-group-item"><strong>平台币:</strong> {{ $customer->platform_coin }}</li>
            <li class="list-group-item"><strong>注册时间:</strong> {{ $customer->created_at }}</li>
        </ul>

    </div>
</body>
</html>
