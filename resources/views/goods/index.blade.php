<!DOCTYPE html>
<html lang="zh">
<head>
    <title>商品管理</title>
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
        #app td, #app th
        {
            height: 54px;
            line-height: 54px;
            font-size: 14px;
            padding: 0;
        }
        #app td img
        {
            height: 50px;
            width: 50px;
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
                <li class="breadcrumb-item active" aria-current="page">商品列表</li>
            </ol>
        </nav>
        <a href="{{ route('goods.create') }}" class="btn btn-primary">创建商品</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>商品ID</th>
                    <th>排序</th>
                    <th style="width:500px;">商品名称</th>
                    <th>图片</th>
                    <th>兑换数量</th>
                    <th>库存</th>
                    <th style="width:160px;">VIP等级</th>
                    <th style="width:140px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($goods as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->sort }}</td>
                    <td style="text-align:left; padding-left: 1rem;">{{ $one->goods_name }}</td>
                    <td>
                        <img src="{{ $one->litpic }}" />
                    </td>
                    <td>{{ $one->count_exchange }}</td>
                    <td>{{ $one->store_num }}</td>
                    <td>{{ $one->level->level_name }}</td>
                    <td>
                        <a href="{{ route('goods.edit', ['good'=>$one->id]) }}" class="btn btn-warning">编辑</a>
                        <form action="{{ route('goods.destroy', ['good'=>$one->id]) }}"
                         method="post"
                         style="float:right;" onsubmit="javascript:return del()">
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
            <div class="box1 p2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $goods->count() }}</strong>条数据
                </aside>
                {{ $goods->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('goods.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $goods->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $goods->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $goods->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $goods->perPage() == 200 ? 'selected' : '' }}>200</option>
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
