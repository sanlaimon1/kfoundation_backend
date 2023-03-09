<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>商品订单管理</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <style>
        .box1, .box2
        {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">商城管理</li>
                <li class="breadcrumb-item active" aria-current="page">商品订单管理</li>
            </ol>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">商品id</th>
                    <th scope="col">收货人姓名</th>
                    <th scope="col">收货人手机号</th>
                    <th scope="col">收货地址</th>
                    <th scope="col">兑换时间</th>
                    <th scope="col">状态</th>
                    <th scope="col">物流单号</th>
                    <th scope="col">备注</th>
                    <th scope="col">修改时间</th>
                    <th scope="col">客户id</th>

                </tr>
            </thead>
            <tbody id="">
                @foreach ($order2 as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->production_id }}</td>
                    <td>{{ $one->recieve_name }}</td>
                    <td>{{ $one->recieve_phone }}</td>
                    <td>{{ $one->recieve_address }}天</td>
                    <td>{{ $one->created_at }}</td>
                    @if ($one->status == 0)
                    <td class="text-warning">待审核</td>
                    @elseif ($one->status == 1)
                    <td class="text-danger">已拒绝</td>
                    @else ($one->status == 2)
                    <td class="text-primary">已发货</td>
                    @endif
                    <td>{{ $one->transfer_no }}</td>
                    <td>{{ $one->comment }}</td>
                    <td>{{ $one->updated_at }}</td>
                    <td>{{ $one->cid }}</td>

                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p2">
                <nav aria-label="page">
                    <strong>总数: {{ $order2->total() }}</strong>  <br /> {{ $order2->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('order2.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $order2->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $order2->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $order2->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $order2->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
</body>
</html>

