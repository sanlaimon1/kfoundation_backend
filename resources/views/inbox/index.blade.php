<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>站内信列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item active" aria-current="page">站内信列表</li>
            </ol>
        </nav>
        <br>
        <a href="{{ route('inbox.create') }}" class="btn btn-primary mb-5">发送站内信</a>
        <br />
        <form action="{{route('inbox.index')}}" method="get">
            <nav class="row">
                <div class="col-3">
                    <label class="form-label">收件人(不填则为所有人)：</label>
                    <input type="text" name="title" id="title" class="form-control" />
                </div>

                <div class="col-2">
                    <label class="form-label">时间：</label>
                    <input type="text" name="date" id="date" class="form-control" />
                </div>

                <div class="col-1">
                    <br />
                    <button class="btn btn-success" id="log_search">查询</button>
                </div>
            </nav>
        </form>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">排序</th>
                    <th scope="col">置顶</th>
                    <th scope="col">标题</th>
                    <th scope="col">接收者</th>
                    <th scope="col">发布时间</th>
                    <th scope="col" style="width:200px;">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($mails as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->sort }}</td>
                    <td>
                        @if($one->is_top==1)
                        <span style="color:red;">已置顶</span>
                        @endif
                    </td>
                    <td>{{ $one->title }}</td>
                    <td>
                        @if( $one->user_phone==null )
                        所有人
                        @else
                        {{ $one->user_phone }}
                        @endif
                    </td>
                    <td>{{ $one->created_at }}</td>
                    <td>
                        <a class="btn btn-primary" href="{{ route('inbox.show', ['inbox'=>$one->id]) }}">查看</a>
                        <a class="btn btn-warning" href="{{ route('inbox.edit', ['inbox'=>$one->id]) }}">编辑</a>
                        <form action="{{ route('inbox.destroy', ['inbox'=>$one->id]) }}"
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
        <nav aria-label="page">
              <strong>总数: {{ $mails->total() }}</strong>  <br /> {{ $mails->links() }}
        </nav>
    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
    <script>
        function del() {
            var msg = "您真的确定要删除吗？\n\n请确认！";
            if (confirm(msg)==true){
                return true;
            }else{
                return false;
            }
        }

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
