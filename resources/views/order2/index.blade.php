<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>站内信列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item active" aria-current="page">项目列表</li>
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
        <nav aria-label="page">
              <strong>总数: {{ $order2->total() }}</strong>  <br /> {{ $order2->links() }}
        </nav>
    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
</body>
</html>

