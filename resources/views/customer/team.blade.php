<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>查看团队</title>
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
            ul {
                padding:0;
                display:flex;
            }
            li {
                list-style-type: none;
                margin-left: .5rem;
            }
    </style>
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">用户列表 </a></li>
                <li class="breadcrumb-item active" aria-current="page">查看团队</li>
            </ol>
        </nav>
        <br />
        <nav class="row">
            <div class="col-3">
                <label class="form-label">手机号：</label>
                <input type="text" name="phone" id="phone" class="form-control" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="log_search">搜索</button>
            </div>

            <div class="col-9">
                <ul>
                    <li>团队等级: </li>
                    <li>团队人数: </li>
                    <li>团队总充值: </li>
                    <li>团队总提现: </li>
                </ul>
            </div>

        </nav>
        <nav class="row">
            <ul>
                <li>
                    <a href="" class="btn btn-primary">1级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">2级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">3级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">4级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">5级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">6级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">7级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">8级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">9级会员</a>
                </li>
                <li>
                    <a href="" class="btn btn-primary">10级会员</a>
                </li>
            </ul>
        </nav>
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">用户名</th>
                    <th scope="col">姓名</th>
                    <th scope="col">总收益</th>
                    <th scope="col">余额</th>
                    <th scope="col">是否认证</th>
                    <th scope="col">查看</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($members as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->phone }}</td>
                    <td>{{ $one->realname }}</td>
                    <td></td>
                    <td>{{ $one->balance }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td><?= $one->is_sure==1 ? '<span style="color:green;">已认证</span>' : '<span style="color:red;">未认证</span>' ?></td>
                    <td>
                        <a class="btn btn-primary" href="{{ route('log.show', ['log'=>$one->id]) }}">查看请求数据</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p-2">
                <nav aria-label="page">
                    <strong>总数: {{ $members->total() }}</strong>  <br /> {{ $members->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('log.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $members->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $members->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $members->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $members->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $members->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </div>
            </form>
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
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });

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