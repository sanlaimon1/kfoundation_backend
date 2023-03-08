<!DOCTYPE html>
<html lang="zh">
<head>
    <title>上分</title>
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
                <li class="breadcrumb-item active" aria-current="page">上分</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary" style="margin-bottom: 0px;">上分</h3>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">给余额上分</h5>
                <label>金额:</label>
                <input type="text" name="amount" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger" style="float:right;">上分</button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">给资产上分</h5>
                <label>金额:</label>
                <input type="text" name="amount" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger" style="float:right;">上分</button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">给积分上分</h5>
                <label>金额:</label>
                <input type="text" name="amount" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger" style="float:right;">上分</button>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">给平台币上分</h5>
                <label>金额:</label>
                <input type="text" name="amount" />
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-danger" style="float:right;">上分</button>
            </div>
        </div>
    </div>
</body>
</html>
