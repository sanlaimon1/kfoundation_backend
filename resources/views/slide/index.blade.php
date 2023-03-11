<!DOCTYPE html>
<html lang="zh">
<head>
    <title>幻灯片</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <style>
        #app td {
            height: 100px;
            line-height: 100px;
            padding: 0;
        }
        #app td img {
            height: 100px;
        }
        .box1, .box2 {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <br />
        <a href="{{ route('slide.create') }}" class="btn btn-primary">创建幻灯图片</a>
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">排序</th>
                    <th scope="col">标题</th>
                    <th scope="col">图片</th>
                    <th scope="col">链接</th>
                    <th scope="col">类型</th>
                    <th scope="col">状态</th>
                    <th scope="col" style="width: 120px;">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($records as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->sort }}</td>
                    <td>{{ $one->title }}</td>
                    <td>
                        <img src="{{ $one->picture_path }}" />
                    </td>
                    <td>{{ $one->link }}</td>
                    <td>
                        @if($one->type==1)
                            轮播
                        @else
                            库存
                        @endif
                    </td>
                    <td>
                        @if($one->status==1)
                            <span style="color:green;">显示</span>
                        @else
                            <span style="color:red;">隐藏</span>
                        @endif
                    </td>
                    <td>
                        <a class="btn btn-warning" href="{{ route('slide.edit', ['slide'=>$one->id]) }}">编辑</a>
                        <form action="{{ route('slide.destroy', ['slide'=>$one->id]) }}"
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
            <form method="get" action="{{ route('slide.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $records->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $records->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $records->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $records->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $records->perPage() == 200 ? 'selected' : '' }}>200</option>
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
