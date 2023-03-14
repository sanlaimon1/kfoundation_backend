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
                <li class="breadcrumb-item active" aria-current="page">币价管理</li>
            </ol>
        </nav>
        <a href="{{ route('currency.create') }}" class="btn btn-primary">创建新币价</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>最新价格</th>
                    <th>开盘价格</th>
                    <th>最低价格</th>
                    <th>最高价格</th>
                    <th>添加时间</th>
                    <th>排序</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
            @foreach($currencies as $currency)
                <tr>
                    <td>{{ $currency->new_price }}</td>
                    <td>{{ $currency->open_price }}</td>
                    <td>{{ $currency->min_price }}</td>
                    <td>{{ $currency->max_price }}</td>
                    <td>{{ $currency->add_time }}</td>
                    <td>{{ $currency->sort }}</td>
                    <td>
                        <a href="{{ route('currency.edit', ['currency'=>$currency->id]) }}"  class="btn btn-warning mx-2">编辑</a>

                        <form action="{{ route('currency.destroy', ['currency'=>$currency->id]) }}"
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
                    共计<strong>{{ $currencies->count() }}</strong>条数据
                </aside>
                {{ $currencies->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('currency.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $currencies->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $currencies->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $currencies->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $currencies->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $currencies->perPage() == 200 ? 'selected' : '' }}>200</option>
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
