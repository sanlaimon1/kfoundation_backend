<!DOCTYPE html>
<html lang="zh">
<head>
    <title>系统用户管理</title>
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
                <li class="breadcrumb-item">系统用户管理</li>
                <li class="breadcrumb-item active" aria-current="page">用户列表</li>
            </ol>
        </nav>
        <a href="{{ route('sysusers.create') }}" class="btn btn-primary">创建管理员</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>用户名</th>
                    <th>状态</th>
                    <th>创建时间</th>
                    <th>登录时间</th>
                    <th>角色名</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sysusers as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->username }}</td>
                    <td>
                        @if($one->status==1)
                        <span style="color:green;">启用</span>
                        @else
                        <span style="color:red;">禁用</span>
                        @endif
                    </td>
                    <td>
                        {{ $one->create_at }}
                    </td>
                    <td>
                        {{ $one->login_at }}
                    </td>
                    <td>
                        {{ $one->role->title }}
                    </td>
                    <td>
                        <a href="{{ route('sysusers.edit', ['sysuser'=>$one->id]) }}" class="btn btn-warning">编辑</a>
                        |
                        <a href="{{ route('sysusers.modifypass', ['id'=>$one->id]) }}" class="btn btn-success">修改密码</a>
                        |
                        <form action="{{ route('sysusers.destroy', ['sysuser'=>$one->id]) }}"
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
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $sysusers->count() }}</strong>条数据
                </aside>
                {{ $sysusers->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('sysusers.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $sysusers->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $sysusers->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $sysusers->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $sysusers->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $sysusers->perPage() == 200 ? 'selected' : '' }}>200</option>
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
