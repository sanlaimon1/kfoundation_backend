<!DOCTYPE html>
<html lang="zh">
<head>
    <title>项目分类</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app
        {
            padding-top: 1rem;
        }
        #app td, #app th
        {
            height: 54px;
            line-height: 54px;
            font-size: 14px;
            padding: 0;
        }
        #app td img
        {
            height: 50px;
            width: 50px;
        }
        .box1, .box2 {
                display: inline-block;
            }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item active" aria-current="page">项目分类</li>
            </ol>
        </nav>
        <a href="{{ route('projectcate.create') }}" class="btn btn-primary">创建项目分类</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>排序</th>
                    <th>分类名称</th>
                    <th>创建时间</th>
                    <th>状态</th>
                    <th style="width:140px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projectcates as $one)
                <tr>
                    <td>{{ $one->sort }}</td>
                    <td>{{ $one->cate_name }}</td>
                    <td>
                        {{ $one->created_at }}
                    </td>
                    <td>
                        @if($one->enable==1)
                        <span style="color:green;">启用</span>
                        @else
                        <span style="color:red;">屏蔽</span>
                        @endif
                    </td>
                    <td>

                        <a href="{{ route('projectcate.edit', ['projectcate'=>$one->id]) }}" class="btn btn-warning">编辑</a>

                        <form action="{{ route('projectcate.destroy', ['projectcate'=>$one->id]) }}"
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
        <footer style="display:flex;">
        <div class="container-fluid">
            <div class="box1 p2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $projectcates->count() }}</strong>条数据
                </aside>
                {{ $projectcates->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('projectcate.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $projectcates->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $projectcates->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $projectcates->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $projectcates->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $projectcates->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </div>
            </form>
        </div>

        </footer>


    </div>
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
