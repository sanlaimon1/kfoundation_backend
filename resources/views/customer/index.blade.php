<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <style>
        #app td
        {
            padding: 0;
        }
        #app td img
        {
            width: 50px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary">{{ $title }}</h3>
        <nav class="row">
            <div class="col-3">
                <label class="form-label">ID</label>
                <input type="text" name="fid" id="fid" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">手机号：</label>
                <input type="text" name="phone" id="phone" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="date" name="created_at" id="created_at" class="form-control" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="customer_search">查询</button>
            </div>
            <div class="col-2">
                <br />
                <a href="{{route('customer.create')}}" class="btn btn-primary">创建客户</a>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">手机号</th>
                    <th scope="col">姓名</th>
                    <th scope="col">余额</th>
                    <th scope="col">资产</th>
                    <th scope="col">积分</th>
                    <th scope="col">平台币</th>
                    <th scope="col">注册时间</th>
                    <th scope="col">最后登录地址</th>
                    <th scope="col" style="width: 240px;">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($records as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->phone }}</td>
                    <td>{{ $one->realname }}</td>
                    <td>{{ $one->asset }}</td>
                    <td>{{ $one->balance }}</td>
                    <td>{{ $one->integration }}</td>
                    <td>{{ $one->platform_coin }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td></td>
                    <td>
                        <a class="btn btn-primary btn-sm" href="{{ route('customer.show', ['customer'=>$one->id]) }}">查看团队</a>
                        <a class="btn btn-warning btn-sm" href="{{ route('customer.edit', ['customer'=>$one->id]) }}">编辑</a>
                        <a class="btn btn-info btn-sm" href="{{ route('customer.show', ['customer'=>$one->id]) }}">充值</a>
                        <a class="btn btn-success btn-sm" href="{{ route('customer.edit', ['customer'=>$one->id]) }}">提现</a>
                        <form action="{{ route('customer.destroy', ['customer'=>$one->id]) }}"
                         method="post"
                         style="float:right;" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">删除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <nav aria-label="page">
              <strong>总数: {{ $records->total() }}</strong>  <br /> {{ $records->links() }}
        </nav>
    </div>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function(){
            $("#customer_search").click(function(){
                var fid = $("#fid").val();
                var phone = $("#phone").val();
                var created_at = $("#created_at").val();
                var data = {
                    "fid": fid,
                    "phone": phone,
                    "created_at" : created_at
                };

            $.ajax({
                url : "/customer_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    console.log(response);
                    $.each(response.search_customer,function(i,v){
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.phone}</td>
                                <td>${v.realname}</td>
                                <td>${v.asset}</td>
                                <td>${v.balance}</td>
                                <td>${v.integration}</td>
                                <td>${v.platform_coin}</td>
                                <td>${v.created_at}</td>
                                <td>
                                    <a href="{{ url('customer/${v.id}') }}" class="btn btn-primary">查看团队</a>
                                    <a href="{{url('customer/${v.id}/edit')}}" class="btn btn-warning">编辑</a>
                                    <form action="{{ url('/customer/${v.id}') }}"
                                    method="post"
                                    style="float:right;" onsubmit="javascript:return del()">
                                        {{ csrf_field() }}
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">删除</button>
                                    </form>
                                </td>
                            </tr>`;
                    })
                    $("#search_data").html(html);
                }
            });
        })
        })
    </script>
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
