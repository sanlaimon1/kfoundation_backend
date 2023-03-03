<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>日志列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
</head>

<body>
    <div class="container-fluid">
        <br />
        <nav class="row">
            <div class="col-3">
                <label class="form-label">管理员：</label>
                <select id="adminid" name="adminid" id="adminid" class="form-control">
                    <option>--请选择--</option>
                    @foreach ($managers as $oneadmin)
                    <option value="{{ $oneadmin->id }}">{{ $oneadmin->username }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-3">
                <label class="form-label">操作：</label>
                <input type="text" name="action" id="action" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="date" name="date" id="date" class="form-control" />
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
                    <th scope="col">ID</th>
                    <th scope="col">管理员</th>
                    <th scope="col">操作</th>
                    <th scope="col">IP</th>
                    <th scope="col">路由</th>
                    <th scope="col">时间</th>
                    <th scope="col">查看</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($logs as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->oneadmin->username }}</td>
                    <td>{{ $one->action }}</td>
                    <td>{{ $one->ip }}</td>
                    <td>{{ $one->route }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td>
                        <a class="btn btn-primary" href="{{ route('log.show', ['log'=>$one->id]) }}">查看请求数据</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <nav aria-label="page">
              <strong>总数: {{ $logs->total() }}</strong>  <br /> {{ $logs->links() }}
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
                                <td>${v.username}</td>
                                <td>${v.action}</td>
                                <td>${v.ip}</td>
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
