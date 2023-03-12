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
            <div class="col-2">
                <label class="form-label">项目名称</label>
                <input type="text" name="pid" id="pid" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">用户名：</label>
                <input type="text" name="customer" id="customer" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">投资时间</label>
                <input type="text" name="created_at" id="created_at" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">返款时间</label>
                <input type="text" name="refund_time" id="refund_time" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">状态：</label>
                <select name="status" id="status" class="form-select">
                    <option>--请选择--</option>
                    <option value="0">未返款</option>
                    <option value="1">已返款</option>
                </select>
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="interest_search">查询</button>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">编号</th>
                    <th scope="col">项目ID/名称</th>
                    <th scope="col">投资人ID/账号</th>
                    <th scope="col">预计返还金额</th>
                    <th scope="col">预计返还时间</th>
                    <th scope="col">实际返还数</th>
                    <th scope="col">投资时间</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($records as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>
                        {{ $one->project->id }}/{{ $one->project->project_name }}
                    </td>
                    <td>
                        {{ $one->customer->id }}/{{ $one->customer->phone }}
                    </td>
                    <td>
                        {{ $one->refund_amount }}
                    </td>
                    <td>{{ $one->refund_time }}</td>
                    <td>
                        @if($one->status!=0)
                        {{ $one->real_refund_amount }}
                        @else
                        未返款
                        @endif
                    </td>
                    <td>{{ $one->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p2">
                <nav aria-label="page">
                    <strong>总数: {{ $records->total() }}</strong>  <br /> {{ $records->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('interest.index') }}">
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
                mode : "range",
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });

              //datepicker
            flatpickr("#refund_time",
            {
                mode : "range",
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });

             //interest search function
            $("#interest_search").click(function(){
            var pid = $("#pid").val();
            var customer = $("#customer").val();
            var created_at = $("#created_at").val();
            var refund_time = $("#refund_time").val();
            var status = $('#status').val();
            var data = {
                "pid": pid,
                "customer": customer,
                "created_at" : created_at,
                "refund_time": refund_time,
                "status": status,
            };

            console.log(data)

            $.ajax({
                url : "/interest_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    $.each(response.interest_search,function(i,v){
                        if(v.status != 0)
                        {
                            var real_refund_amount = v.real_refund_amount;
                        } else {
                            var real_refund_amount = '未返款';
                        }
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.pid} / ${v.pname}</td>
                                <td>${v.cid} / ${v.cphone}</td>
                                <td>${v.refund_amount}</td>
                                <td>${v.refund_time}</td>
                                <td>${real_refund_amount}</td>
                                <td>${v.created_at}</td>
                            </tr>`;
                    })
                    $("#search_data").html(html);
                }
            });
        })
        })
    </script>
</body>
</html>
