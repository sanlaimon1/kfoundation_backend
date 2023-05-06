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
        <!-- 引入 flatpickr 的 CSS 和 JS -->
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
        #app td
        {
            padding: 0;
        }
        #app td img
        {
            width: 50px;
        }
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
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary">{{ $title }}</h3>
        <nav class="row">
            <div class="col-3">
                <label class="form-label">编号：</label>
                <input type="text" name="fid" id="fid" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">用户名：</label>
                <input type="text" name="phone" id="phone" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">钱包类型：</label>
                <select name="payid" id="payid"  class="form-select">
                    <option value="0">--请选择--</option>
                    @foreach($types as $type_val=>$one_type)
                    <option value="{{ $type_val }}">{{ $one_type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="text" name="created_at" id="created_at" class="form-control" lang="zh-CN" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="wallet_search">查询</button>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">编号</th>
                    <th scope="col">会员ID</th>
                    <th scope="col">收款人姓名</th>
                    <th scope="col">用户名</th>
                    <th scope="col">钱包类型</th>
                    <th scope="col">钱包地址/银行卡号</th>
                    <th scope="col">绑定时间</th>
                    <th scope="col">二维码</th>
                    <th scope="col">来源</th>
                    <th scope="col" style="width: 80px;">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($records as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->customer->id }}</td>
                    <td>{{ $one->realname }}</td>
                    <td>{{ $one->customer->phone }}</td>
                    <td>{{ $types[ $one->payid ] }}</td>
                    <td>{{ $one->address }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td>
                        <a href="{{ $one->qrcode }}" target="_blank">
                            <img src="<?= config('app.static_url') ?>{{ $one->qrcode }}" width="100%"/>
                        </a>
                    </td>
                    <td>
                        {{ $one->origin }}
                    </td>
                    <td>
                        <form action="{{ route('wallet.destroy', ['wallet'=>$one->id]) }}"
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
        <div class="container-fluid">
            <div class="box1 p-2">
                <nav aria-label="page">
                    <strong>总数: {{ $records->total() }}</strong>  <br /> {{ $records->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('wallet.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $records->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $records->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $records->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $records->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

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
            //datepicker
            flatpickr("#created_at",
            {
                mode: "range",
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });

            $("#wallet_search").click(function(){
            var fid = $("#fid").val();
            var phone = $("#phone").val();
            var payid = $("#payid").val();
            var created_at = $("#created_at").val();
            var data = {
                "fid": fid,
                "phone": phone,
                "payid" : payid,
                "created_at" : created_at
            };

            $.ajax({
                url : "/wallet_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    console.log(response);
                    $.each(response.search_wallet,function(i,v){
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.customerid}</td>
                                <td>${v.realname}</td>
                                <td>${v.phone}</td>
                                <td>${response.types[v.payid]}</td>
                                <td>${v.address}</td>
                                <td>${v.created_at}</td>
                                <td>
                                    <a href="${v.qrcode}" target="_blank">
                                        <img src="${v.qrcode}" />
                                    </a>
                                </td>
                                <td>
                                    ${v.origin}
                                </td>
                                <td>
                                    <form action="{{ url('/wallet/${v.id}') }}"
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



