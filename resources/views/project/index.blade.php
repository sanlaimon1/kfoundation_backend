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
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item active" aria-current="page">项目列表</li>
            </ol>
        </nav>
        <br />
        <nav class="row">
            <div class="col-3">
                <label class="form-label">项目名称：</label>
                <input type="text" name="project_name" id="project_name" class="form-control" />
            </div>

            <div class="col-2">
                <label class="form-label">项目分类：</label>
                <select id="cate_type" name="type" class="form-select">
                    <option>--请选择--</option>
                    @foreach( $types as $key=>$one_type )
                    <option value="{{ $key }}">{{ $one_type }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-1">
                <br />
                <button class="btn btn-success" id="project_search">查询</button>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">项目名称</th>
                    <th scope="col">项目分类</th>
                    <th scope="col">项目规模</th>
                    <th scope="col">收益率</th>
                    <th scope="col">项目期限</th>
                    <th scope="col">起购金额</th>
                    <th scope="col">限购次数</th>
                    <th scope="col">项目进度</th>
                    <th scope="col">发布时间</th>
                    <th scope="col" style="width:200px;">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($projects as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->project_name }}</td>
                    <td>{{ $one->projectcate->cate_name }}</td>
                    <td>{{ $one->project_scale }}</td>
                    <td>{{ $one->benefit_rate }}</td>
                    <td>{{ $one->days }}天</td>
                    <td>{{ $one->amount }}</td>
                    <td>{{ $one->max_times }}</td>
                    <td>{{ $one->fake_process }}%</td>
                    <td>{{ $one->created_at }}</td>
                    <td>
                        <a class="btn btn-primary" href="{{ route('project.show', ['project'=>$one->id]) }}">查看</a>
                        <a class="btn btn-warning" href="{{ route('project.edit', ['project'=>$one->id]) }}">编辑</a>
                        <form action="{{ route('project.destroy', ['project'=>$one->id]) }}" 
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
              <strong>总数: {{ $projects->total() }}</strong>  <br /> {{ $projects->links() }}
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
            $("#project_search").click(function(){
            var project_name = $("#project_name").val();
            var cate_type = $("#cate_type").val();
            var data = {
                "project_name": project_name,
                "cate_type": cate_type,
            };

            $.ajax({
                url : "/project_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    $.each(response,function(i,v){
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.project_name}</td>
                                <td>${v.projectcate.cate_name}</td>
                                <td>${v.project_scale }</td>
                                <td>${v.benefit_rate }</td>
                                <td>${v.days }天</td>
                                <td>${ v.amount }</td>
                                <td>${ v.max_times }</td>
                                <td>${v.fake_process }%</td>
                                <td>${ v.created_at }</td>
                                <td>
                                    <a class="btn btn-primary" href="/project/${v.id}">查看</a>
                                    <a class="btn btn-warning" href="/project/edit/${v.id}">编辑</a>
                                    <form action="{{url('/project/${v.id}')}}" 
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
</body>
</html>
