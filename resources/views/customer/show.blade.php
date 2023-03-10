<!DOCTYPE html>
<html lang="zh">
<head>
    <title>查看订单状态</title>
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
                <li class="breadcrumb-item active" aria-current="page">查看详情</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary" style="margin-bottom: 0px;">客户详情</h3>
        <!--
            用户名：15191062026
            实名：已认证
            邀请码：BUjhqcy1
            
            会员等级：普通会员
            真实姓名：赵敏
            推荐人：18800660181
            身份：真实客户
            IP：223.104.190.176

            账户余额：0.00
            提现总额：0
            充值总额：0
            投资总额：0
            DB余额：2.00

            待收利息：0
            待收本金：0
            余额宝：0.00
            资产：8.00

            积分：0
            用户状态：正常
            最近操作：2023年02月28日 14:35:42
            注册时间：2023-02-28 14:23:02
            ISP：中国山东省济南市移动
            最近登录IP：--
            最近登录时间：--
        -->
        <ul class="list-group list-group-flush">
            
            <li class="list-group-item"><strong class="title">用户信息</strong></li>
            <ul class="row">
                <li class="list-group-item col-4"><strong>ID:</strong> {{ $customer->id }}</li>
                <li class="list-group-item col-4"><strong>手机号:</strong> {{ $customer->phone }}</li>
                <li class="list-group-item col-4"><strong>真实姓名:</strong> {{ $customer->realname }}</li>
            </ul>
            <ul class="row">
                <li class="list-group-item col-3"><strong>邀请码:</strong> {{ $customer->invited_code }}</li>
                <li class="list-group-item col-3"><strong>推荐人:</strong>
                    {{ $customer->getParentName(); }}
                </li>
                <li class="list-group-item col-3"><strong>会员等级:</strong> {{ $customer->level->level_name }}</li>
                <li class="list-group-item col-3"><strong>身份:</strong> {{ $customer_identity[ $customer->identity ] }}</li>
            </ul>
           
            <li class="list-group-item"><strong class="title">用户资金</strong></li>
            <ul class="row">
                <li class="list-group-item col-2"><strong>余额:</strong> {{ $customer->asset }}</li>
                <li class="list-group-item col-2"><strong>资产:</strong> {{ $customer->balance }}</li>
                <li class="list-group-item col-2"><strong>积分:</strong> {{ $customer->integration }}</li>
                <li class="list-group-item col-3"><strong>平台币:</strong> {{ $customer->platform_coin }}</li>
                <li class="list-group-item col-3"><strong>余额宝余额:</strong> {{ $customer->yuebao_balance }}</li>
            </ul>
            <ul class="row">
                <li class="list-group-item col-2"><strong>提现总额:</strong> {{ $customer_extra->withdrawal }}</li>
                <li class="list-group-item col-2"><strong>充值总额:</strong> {{ $customer_extra->charge }}</li>
                <li class="list-group-item col-2"><strong>待收利息:</strong> {{ $customer_extra->predict_interest }}</li>
                <li class="list-group-item col-2"><strong>待收本金:</strong> {{ $customer_extra->predict_cost }}</li>
                <li class="list-group-item col-2"><strong>投资总额:</strong> {{ $customer_extra->investion}}</li>
                <li class="list-group-item col-2"><strong>余额转资产总额:</strong> {{ $customer_extra->transfer2asset}}</li>
            </ul>

            <li class="list-group-item"><strong class="title">其他详情</strong></li>
            <ul class="row">
                <li class="list-group-item col-6"><strong>用户状态:</strong> {{ $customer->status==1 ? '正常' : '锁定' }}</li>
                <li class="list-group-item col-6"><strong>最后登录的IP:</strong> {{ $customer->getLastLog()['ip'] }}</li>
            </ul>
            <ul class="row">
                <li class="list-group-item col-3"><strong>最后登录的国家:</strong> {{ $customer->getLastLog()['state'] }}</li>
                <li class="list-group-item col-3"><strong>省:</strong> {{ $customer->getLastLog()['province'] }}</li>
                <li class="list-group-item col-3"><strong>市:</strong> {{ $customer->getLastLog()['city'] }}</li>
                <li class="list-group-item col-3"><strong>ISP运营商:</strong> {{ $customer->getLastLog()['isp'] }}</li>
            </ul>
            <ul class="row">
                <li class="list-group-item col-4"><strong>最后登录的时间:</strong> {{ $customer->getLastLog()['created_at'] }}</li>
                <li class="list-group-item col-4"><strong>注册时间:</strong> {{ $customer->created_at }}</li>
                <li class="list-group-item col-4"><strong>最后修改时间:</strong> {{ $customer->updated_at }}</li>
            </ul>
            
        </ul>

    </div>
</body>
</html>
