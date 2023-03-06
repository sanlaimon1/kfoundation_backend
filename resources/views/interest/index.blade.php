<!DOCTYPE html>
<html lang="zh">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
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
                <input type="text" name="action" id="action" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">投资时间</label>
                <input type="date" name="created_at" id="created_at" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">返款时间</label>
                <input type="date" name="date" id="date" class="form-control" />
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
                <button class="btn btn-success" id="log_search">查询</button>
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
            $("#log_search").click(function(){
            var adminid = $("#adminid").val();
            var action = $("#action").val();
            var date = $("#date").val();
            var data = {
                "adminid": adminid,
                "action": action,
                "date" : date,
            };

            $.ajax({
                url : "/log_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    console.log(response);
                    $.each(response.search_logs,function(i,v){
                        console.log(v);
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.action}</td>
                                <td>${v.route}</td>
                                <td>${v.created_at}</td>
                                <td>
                                    <a class="btn btn-primary" href="log/${v.id}">查看请求数据</a>
                                </td>
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