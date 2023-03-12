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
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
            .box1, .box2 {
                display: inline-block;
            }
            ul {
                margin:0;
                padding:0;
                display:flex;
                height: 40px;
            }
            li {
                list-style-type: none;
                margin-left: .5rem;
                height: 40px;
                line-height: 40px;
            }
            .value
            {
                color:red;
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
        
        <nav class="row">
            <div class="col-3">
                <div class="row">
                    <label class="form-label col-4">手机号：</label>
                    <input type="text" name="phone" id="phone" class="form-control col-8" style="width: 66%;" />
                </div>
            </div>

            <div class="col-1">
                <button class="btn btn-success" id="log_search">搜索</button>
            </div>

            <div class="col-8">
                <ul>
                    <li>团队等级: <strong class="value">{{ $one_team->level_name }}</strong></li>
                    <li>团队人数: <strong class="value">{{ $count_children }}</strong></li>
                    <li>团队总充值: <strong class="value">{{ $one_team_extra->charge_total }}</strong></li>
                    <li>团队总提现: <strong class="value">{{ $one_team_extra->withdrawal_total }}</strong></li>
                </ul>
            </div>

        </nav>
        
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">用户名</th>
                    <th scope="col">姓名</th>
                    <th scope="col">总收益</th>
                    <th scope="col">余额</th>
                    <th scope="col">创建时间</th>
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
                    <td>{{ $one->customerExtra()->got_interest }}</td>
                    <td>{{ $one->balance }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td><?= $one->is_sure==1 ? '<span style="color:green;">已认证</span>' : '<span style="color:red;">未认证</span>' ?></td>
                    <td>
                        @if($one->customerExtra()->all_children_ids!=null or $one->customerExtra()->all_children_ids!='')
                        <button class="btn btn-success children" href="{{ route('customer.list_children', ['id'=>$one->id]) }}">查看下级列表</button>
                        @endif
                        <a class="btn btn-primary" href="{{ route('customer.show', ['customer'=>$one->id]) }}">查看会员</a>
                    </td>
                </tr>
                <!-- 查询下级动态数据 query child customers with dynamic datas //start -->
                
                <!-- 查询下级动态数据 query child customers with dynamic datas //end -->
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
                });

        });

        //查询下级
        $(document).on('click', 'button.children', function(){
            var url = $(this).attr('href');
            var btn_obj = $(this);
            $('.loading').show();
            $.get(url, function(html_text){
                btn_obj.parent().parent().next('tr.child').remove();
                btn_obj.parent().parent().after('<tr class="child">'+html_text+'</tr>');
                $('.loading').hide();
            }, 'html');
        });
    </script>
    @include('loading')
</body>
</html>
