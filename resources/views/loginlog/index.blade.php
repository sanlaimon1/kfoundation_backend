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
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
        .box1, .box2
        {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <br />
        <nav class="row">
            <div class="col-3">
                <label class="form-label">用户手机：</label>
                <input type="text" name="phone" id="phone" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">操作：</label>
                <input type="text" name="action" id="action" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="text" name="date" id="date" class="form-control" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="loginlog_search">查询</button>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">用户名</th>
                    <th scope="col">操作</th>
                    <th scope="col">IP</th>
                    <th scope="col">国家</th>
                    <th scope="col">省</th>
                    <th scope="col">市</th>
                    <th scope="col">ISP运营商</th>
                    <th scope="col">登录时间</th>
                    <th scope="col">查看</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($log_datas as $one)
                <tr>
                    <td>{{ $one['id'] }}</td>
                    <td>{{ $one['phone'] }}</td>
                    <td>{{ $one['action'] }}</td>
                    <td>{{ $one['ip'] }}</td>
                    <td>{{ $one['state'] }}</td>
                    <td>{{ $one['province'] }}</td>
                    <td>{{ $one['city'] }}</td>
                    <td>{{ $one['isp'] }}</td>
                    <td>{{ $one['created_at'] }}</td>
                    <td>
                        <a class="btn btn-primary" href="{{ route('loginlog.show', ['loginlog'=>$one['id']]) }}">查看请求数据</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p-2">
                <nav aria-label="page">
                    <strong>总数: {{ $logs->total() }}</strong>  <br /> {{ $logs->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('loginlog.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $logs->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $logs->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $logs->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $logs->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $logs->perPage() == 200 ? 'selected' : '' }}>200</option>
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
                mode: "range",
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });

             //loginlog_search function
            $("#loginlog_search").click(function(){
            var phone = $("#phone").val();
            var action = $("#action").val();
            var date = $("#date").val();
            var data = {
                "phone": phone,
                "action": action,
                "date" : date,
            };

            $.ajax({
                url : "/loginlog_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    console.log(response);
                    $.each(response.loginlog_search,function(i,v){
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.phone}</td>
                                <td>${v.action}</td>
                                <td>${v.ip}</td>
                                <td>${v.state}</td>
                                <td>${v.province}</td>
                                <td>${v.city}</td>
                                <td>${v.isp}</td>
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
