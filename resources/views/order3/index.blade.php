<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>生活服务订单管理</title>
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
                <li class="breadcrumb-item">生活服务</li>
                <li class="breadcrumb-item active" aria-current="page">生活服务订单管理</li>
            </ol>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">商品id</th>
                    <th scope="col">附加的信息</th>
                    <th scope="col">状态</th>
                    <th scope="col">创建时间</th>
                    <th scope="col">修改时间</th>
                    <th scope="col" style="width:200px;">操作</th>


                </tr>
            </thead>
            <tbody id="">
                @foreach ($order3 as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->pid }}</td>
                    <td>{{ $one->extra }}</td>
                    @if ($one->status == 0)
                    <td class="text-warning">待审核</td>
                    @elseif ($one->status == 1)
                    <td class="text-success">拒绝</td>
                    @else ($one->status == 2)
                    <td class="text-danger">通过</td>
                    @endif
                    <td>{{ $one->created_at }}</td>
                    <td>{{ $one->updated_at }}</td>
                    <td>
                        <a class="btn btn-success mr-2 my-2" id="pass_btn" href="{{ route('order3.edit', ['order3'=>$one->id]) }}"> 通过</a>
                        <a class="btn btn-danger mr-2 my-2" id="reject_btn" href="{{ route('order3.show', ['order3'=>$one->id]) }}" >拒绝</a>
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p2">
                <nav aria-label="page">
                    <strong>总数: {{ $order3->total() }}</strong>  <br /> {{ $order3->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('order3.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $order3->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $order3->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $order3->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $order3->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
    <script>
        $(document).ready(function(){
            //pass_btn function
            $("tbody").on('click',"#pass_btn",function(){
                var msg = "您想要审核这条订单吗？";
                if (confirm(msg)==true){
                    return true;
                }else{
                    return false;
                }
            })

            //reject_btn function
            $("tbody").on('click',"#reject_btn",function(){
                var msg = "您想要审核这条订单吗?";
                if (confirm(msg)==true){
                    return true;
                }else{
                    return false;
                }
            })
        })
    </script>
</body>
</html>

