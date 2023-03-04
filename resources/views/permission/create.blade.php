<!DOCTYPE html>
<html lang="zh">

<head>
    <title>系统角色管理</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app {
            padding-top: 1rem;
        }

        #app td {
            height: 20px;
            line-height: 20px;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item"><a href="{{ route('permission.index') }}">权限表</a></li>
                <li class="breadcrumb-item active" aria-current="page">创建权限</li>
            </ol>
        </nav>

        <div class="container">
            <form action="{{ route('permission.store') }}" method="post">
                {{ csrf_field() }}
                <!-- <div class="form-group">
                    <label for="path_name">uri名称</label>
                    <input type="text" name="path_name" class="form-control" id="path_name" placeholder="uri名称">
                    @error('path_name')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div> -->

                <div class="form-group">
                    <label for="path_name">uri名称</label>
                    <select id="path_name" class="form-control" name="path_name">
                        @foreach($menu_items as $key=>$item)
                            <?php $data_keys = config('data.' . $key); ?>
                            @foreach($data_keys as $data_key=>$data_value)
                                <option value="{{$data_value}}">{{$data_key}}</option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-4">
                    <label for="role_id">角色</label>
                    <select id="role_id" class="form-control" name="role_id">
                        @foreach($roles as $role)
                        <option value="{{$role->rid}}">{{$role->title}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-4">
                    <label for="auth2">子栏目权限值</label>
                    <div class="row">
                        <div class="col-3">
                            <input type="checkbox" id="create" name="create" value="1">
                            <label for="create">创建</label>
                        </div>
                        <div class="col-3">
                            <input type="checkbox" id="read" name="read" value="2">
                            <label for="read">查询</label>
                        </div>
                        <div class="col-3">
                            <input type="checkbox" id="update" name="update" value="4">
                            <label for="update">修改</label>
                        </div>
                        <div class="col-3">
                            <input type="checkbox" id="delete" name="delete" value="8">
                            <label for="delete">删除</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">Submit</button>
            </form>

        </div>


    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
</body>

</html>