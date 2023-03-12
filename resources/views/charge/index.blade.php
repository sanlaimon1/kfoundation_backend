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
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
        #app td,#app th
        {
            padding: 0;
            font-size: 12px;
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
                <label class="form-label">财务编号：</label>
                <input type="text" name="fid" id="fid" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">用户名：</label>
                <input type="text" name="customer" id="customer" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">状态：</label>
                <select name="financial_type" id="financial_type"  class="form-select">
                    <option value="">--请选择--</option>
                    @foreach($types as $type_val=>$one_type)
                    <option value="{{ $type_val }}">{{ $one_type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="text" name="date" id="date" class="form-control" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="charge_search">查询</button>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">财务编号</th>
                    <th scope="col">用户名</th>
                    <th scope="col">申请时间</th>
                    <th scope="col">状态</th>
                    <th scope="col">金额</th>
                    <th scope="col">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($records as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->customer->phone }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td>
                        @if($one->status==1)
                        <span style="color:green;">已通过</span>
                        @elseif($one->status==2)
                        <span style="color:red;">已拒绝</span>
                        @elseif($one->status==0)
                        <span style="color:blue;">待审核</span>
                        @endif
                    </td>
                    <td>{{ $one->amount }}</td>
                    <td>
                        @if($one->status==0)
                        <a href="{{ route('charge.show', ['charge'=>$one->id]) }}" class="btn btn-success">通过</a>
                        <a href="{{ route('charge.edit', ['charge'=>$one->id]) }}" class="btn btn-danger">拒绝</a>
                        @endif
                        @if($one->status==2)
                        拒绝理由: {{ $one->comment }}
                        @endif
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
            <form method="get" action="{{ route('charge.index') }}">
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
            flatpickr("#date",
            {
                mode : "range",
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });

            $("#charge_search").click(function(){
            var fid = $("#fid").val();
            var customer = $("#customer").val();
            var financial_type = $("#financial_type").val();
            var date = $("#date").val();
            var data = {
                "fid": fid,
                "customer": customer,
                "financial_type": financial_type,
                "date" : date,
            };

            $.ajax({
                url : "/charge_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    $.each(response.charge_search,function(i,v){
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.phone}</td>
                                <td>${v.created_at}</td>`;
                    if (v.status == 1){
                        html += `<td>
                                    <span style="color:green;">已通过</span>
                                </td>`;
                    }else if (v.status == 2){
                        html += `<td>
                                    <span style="color:red;">已拒绝</span>
                                </td>`;
                    }else{
                        html +=`<td>
                                    <span style="color:blue;">待审核</span>
                                </td>`;
                    }
                         html +=`<td>${v.amount}</td>`;
                    if (v.status == 0){
                        html += `<td>
                                        <a href="{{ url('charge/${v.id}') }}" class="btn btn-success">通过</a>
                                        <a href="{{ url('charge/${v.id}/edit') }}" class="btn btn-danger">拒绝</a>
                                </td>`;
                    }
                    if (v.status == 2){
                        html += `<td>
                                    拒绝理由: ${v.comment}
                                </td>`;
                    }
                    html += `</tr>`;
                    })
                    $("#search_data").html(html);
                }
            });
        })
        })
    </script>
</body>
</html>
