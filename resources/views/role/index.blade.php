<!DOCTYPE html>
<html lang="zh">
<head>
    <title>系统角色管理</title>
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
        #app td
        {
            height: 20px;
            line-height: 20px;
        }
        .box1, .box2
        {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item active" aria-current="page">角色列表</li>
            </ol>
        </nav>
        <a href="{{ route('role.create') }}" class="btn btn-primary mb-5">创建角色</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>标题</th>
                    <th>创建时间</th>
                    <th>排序</th>
                    <th>描述</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roles as $one)
                <tr>
                    <td>{{ $one->rid }}</td>
                    <td>{{ $one->title }}</td>
                    <td>
                        {{ $one->created_at }}
                    </td>
                    <td>
                        {{ $one->sort }}
                    </td>
                    <td>
                        {{ $one->desc }}
                    </td>
                    <td>
                        @if($one->rid!=1)
                        <a href="{{ route('role.show', ['role'=>$one->rid]) }}" class="btn btn-primary">编辑权限</a>
                        
                        <a href="{{ route('role.edit', ['role'=>$one->rid]) }}" class="btn btn-warning">编辑</a>
                        
                        <form action="{{ route('role.destroy', ['role'=>$one->rid]) }}"
                         method="post"
                         style="float:right;" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <footer style="display:flex;">
        <div class="container-fluid">
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $roles->count() }}</strong>条数据
                </aside>
                {{ $roles->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('role.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $roles->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $roles->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $roles->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $roles->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $roles->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>

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
