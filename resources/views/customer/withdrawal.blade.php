<!DOCTYPE html>
<html lang="zh">
<head>
    <title>下分</title>
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
                <li class="breadcrumb-item active" aria-current="page">下分</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary" style="margin-bottom: 0px;">下分</h3>
        <div class="card my-3">
            <form action="{{route('withdraw.financial_balance')}}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="card-body">
                    <h5 class="card-title">给余额下分</h5>
                    <label for="withdraw_balance_amount" class="form-label my-3">金额:</label>
                    @error('withdraw_balance_amount')
                        <div class="alert alert-danger">{{$message}}</div>
                    @enderror
                    <input type="text" name="withdraw_balance_amount" id="withdraw_balance_amount" />
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger" style="float:right;">下分</button>
                </div>
            </form>
        </div>
        <div class="card">
            <form action="{{route('withdraw.financial_asset')}}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="card-body">
                    <h5 class="card-title">给资产下分</h5>
                    <label for="withdraw_asset_amount" class="form-label my-3">金额:</label>
                    @error('withdraw_asset_amount')
                        <div class="alert alert-danger">{{$message}}</div>
                    @enderror
                    <input type="text" name="withdraw_asset_amount" id="withdraw_asset_amount" />
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger" style="float:right;">下分</button>
                </div>
            </form>
        </div>
        <div class="card my-3">
            <form action="{{route('withdraw.financial_integration')}}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="card-body">
                    <h5 class="card-title">给积分下分</h5>
                    <label for="withdraw_integration_amount" class="form-label my-3">金额:</label>
                    @error('withdraw_integration_amount')
                        <div class="alert alert-danger">{{$message}}</div>
                    @enderror
                    <input type="text" name="withdraw_integration_amount" id="withdraw_integration_amount" />
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger" style="float:right;">下分</button>
                </div>
            </form>
        </div>
        <div class="card">
            <form action="{{route('withdraw.financial_platform_coin')}}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                <div class="card-body">
                    <h5 class="card-title">给平台币下分</h5>
                    <label for="withdraw_platform_coin_amount" class="form-label my-3">金额:</label>
                    @error('withdraw_platform_coin_amount')
                        <div class="alert alert-danger">{{$message}}</div>                        
                    @enderror
                    <input type="text" name="withdraw_platform_coin_amount" id="withdraw_platform_coin_amount" />
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-danger" style="float:right;">下分</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
