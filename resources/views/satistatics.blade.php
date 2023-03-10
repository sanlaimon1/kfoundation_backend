<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        .row
        {
            display: flex;
        }
        .card
        {
            margin: .5rem;
            flex: 1;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <hr />
        <div class="row">
            <div class="card bg-primary text-white">
                <div class="card-header">已投项目</div>
                <div class="card-body">
                    {{ $order1 }}
                </div>
            </div>
            <div class="card bg-success text-white">
                <div class="card-header">会员总数</div>
                <div class="card-body">
                    {{$customer}}
                </div>
            </div>
            <div class="card bg-danger text-white">
                <div class="card-header">充值总额</div>
                <div class="card-body">
                    {{$assetCheck}}
                </div>
            </div>
            <div class="card bg-info text-white">
                <div class="card-header">提现总额</div>
                <div class="card-body">
                    {{$balanceCheck}}
                </div>
            </div>
            <div class="card bg-warning text-white">
                <div class="card-header">今日注册用户</div>
                <div class="card-body">
                    {{$todayCustomer}}
                </div>
            </div>
            <div class="card bg-secondary text-white">
                <div class="card-header">今日投资用户</div>
                <div class="card-body">
                    {{$todayOrder}}
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="card bg-primary text-white">
                <div class="card-header">今日真实账户充值</div>
                <div class="card-body">
                    {{$todayAssetCheck}}
                </div>
            </div>
            <div class="card bg-success text-white">
                <div class="card-header">今日回款</div>
                <div class="card-body">
                    {{$interest}}
                </div>
            </div>
            <div class="card bg-danger text-white">
                <div class="card-header">昨日投资用户</div>
                <div class="card-body">
                    {{$yesterdayOrder1}}
                </div>
            </div>
            <div class="card bg-info text-white">
                <div class="card-header">明日预计发放收益</div>
                <div class="card-body">
                    {{$tomorrowInterest}}
                </div>
            </div>
            <div class="card bg-warning text-white">
                <div class="card-header">明日预计返还本金</div>
                <div class="card-body">
                    {{$tomorrowInterestFlag}}
                </div>
            </div>
            <div class="card bg-secondary text-white">
                <div class="card-header">团队数量</div>
                <div class="card-body">
                    {{$team}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card bg-primary text-white">
                <div class="card-header">今日项目交易量</div>
                <div class="card-body">
                    {{$customerOrder1}}
                </div>
            </div>
            <div class="card bg-success text-white">
                <div class="card-header">昨日项目交易量</div>
                <div class="card-body">
                    {{$yesterdayCustomerOrder1}}
                </div>
            </div>
            <div class="card bg-danger text-white">
                <div class="card-header">本周项目交易量</div>
                <div class="card-body">
                    {{$weekOrder1}}
                </div>
            </div>
            <div class="card bg-info text-white">
                <div class="card-header">当月项目交易量</div>
                <div class="card-body">
                    {{$monthOrder1}}
                </div>
            </div>
            <div class="card bg-warning text-white">
                <div class="card-header">今日模拟账户充值</div>
                <div class="card-body">
                    {{$todayAssetCheckNotZero}}
                </div>
            </div>
            <div class="card bg-secondary text-white">
                <div class="card-header">今日模拟账户提现</div>
                <div class="card-body">
                    {{$balanceCheckNotzero}}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card bg-primary text-white">
                <div class="card-header">今日项目投资额</div>
                <div class="card-body">
                    {{$todayOrderCustomer}}
                </div>
            </div>
            <div class="card bg-success text-white">
                <div class="card-header">模拟账户总提现</div>
                <div class="card-body">
                    {{$customerAssetCheck}}
                </div>
            </div>
            <div class="card bg-danger text-white">
                <div class="card-header">模拟账户总充值</div>
                <div class="card-body">
                    {{$customerBalanceCheck}}
                </div>
            </div>
            <div class="card bg-info text-white">
                <div class="card-header">持币总数</div>
                <div class="card-body">
                    {{$customerByPlatformCoin}}
                </div>
            </div>
            <div class="card bg-warning text-white">
                <div class="card-header">提币总数</div>
                <div class="card-body">
                    {{$financialPlatformCoin}}
                </div>
            </div>
            <div class="card bg-secondary text-white">
                <div class="card-header">&nbsp;</div>
                <div class="card-body">
                    &nbsp;
                </div>
            </div>
        </div>

    </div>

</body>

</html>
