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
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item active" aria-current="page">交易所商品管理</li>
            </ol>
        </nav>
        <a href="{{ route('financial_productions.create') }}" class="btn btn-primary">创建交易所商品</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>产品名称</th>
                    <th>买入价格</th>
                    <th>卖出价格</th>
                    <th>天数</th>
                    <th>创建时间</th>
                    <th>最大投资次数</th>
                    <th>进度百分比</th>
                    <th>自增进度</th>

                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($records as $one)
                <tr>
                    <td>{{ $one->production_name }}</td>
                    <td>{{ $one->buy_price }}</td>
                    <td>{{ $one->sell_price }}</td>
                    <td>{{ $one->days }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td>{{$one->max_times}}</td>
                    <td>{{$one->fake_process}}</td>
                    <td>{{$one->increment_process}}</td>
                    <td>
                        <a href="{{ route('financial_productions.edit',$one->id) }}"  class="btn btn-warning mx-2">编辑</a>

                        <form action="{{ route('financial_productions.destroy', $one->id) }}"
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
                    共计<strong>{{ $records->count() }}</strong>条数据
                </aside>
                {{ $records->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('financial_productions.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $records->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $records->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $records->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $records->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $records->perPage() == 200 ? 'selected' : '' }}>200</option>
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
