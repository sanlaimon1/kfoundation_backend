<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>项目返息明细</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <style>
        .box1, .box2 {
                display: inline-block;
            }
    </style>
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item active" aria-current="page">返息明细</li>
            </ol>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">项目id</th>
                    <th scope="col">客户/投资人id</th>
                    <th scope="col">投资金额</th>
                    <th scope="col">周期</th>
                    <th scope="col">费率</th>
                    <th scope="col">创建时间</th>
                    <th scope="col">修改时间</th>
                </tr>
            </thead>
            <tbody id="">
                @foreach ($order1 as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->pid }}</td>
                    <td>{{ $one->cid }}</td>
                    <td>{{ $one->amount }}</td>
                    <td>{{ $one->days }}天</td>
                    <td>{{ $one->rate }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td>{{ $one->updated_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p2">
                <nav aria-label="page">
                    <strong>总数: {{ $order1->total() }}</strong>  <br /> {{ $order1->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('order1.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $order1->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $order1->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $order1->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $order1->perPage() == 200 ? 'selected' : '' }}>200</option>
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

