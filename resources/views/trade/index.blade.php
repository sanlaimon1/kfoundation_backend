<!DOCTYPE html>
<html lang="zh">
<head>
    <title>信息管理</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app
        {
            padding-top: 1rem;
        }
        #app td
        {
            height: 20px;
            line-height: 20px;
        }
        .box1, .box2
        {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">商城管理</li>
                <li class="breadcrumb-item active" aria-current="page">交易所管理</li>
            </ol>
        </nav>
        <a href="{{ route('trade.create') }}" class="btn btn-primary">创建交易所</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>商品名称</th>
                    <th>天数</th>
                    <th>价格</th>
                    <!-- <th>缩略图</th> -->
                    <th>创建时间</th>
                    <th>是否结束</th>
                    <th>手续费百分比</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
            @foreach($trade_goods as $one)
                <tr>
                    <td>{{ $one->goods_name }}</td>
                    <td>{{ $one->days }}</td>
                    <td>{{ $one->price }}</td>
                    <!-- <td>{{ $one->images }}</td> -->
                    <td>{{ $one->created_at }}</td>
                    <td>{{ $one->is_over }}</td>
                    <td>{{$one->fee}}</td>
                    <td>
                        <a href="{{ route('trade.edit', ['trade'=>$one->id]) }}"  class="btn btn-warning mx-2">编辑</a>

                        <form action="{{ route('trade.destroy', ['trade'=>$one->id]) }}"
                         method="post"
                         class="d-inline-block" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <footer style="display:flex;">
        <div class="container-fluid">
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $trade_goods->count() }}</strong>条数据
                </aside>
                {{ $trade_goods->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('trade.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $trade_goods->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $trade_goods->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $trade_goods->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $trade_goods->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $trade_goods->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

        </footer>


    </div>
    <script>
    function del() {
        var msg = "您真的确定要删除吗？\n\n请确认！";
        if (confirm(msg)==true){
            return true;
        }else{
            return false;
        }
    }
    </script>
</body>
</html>
