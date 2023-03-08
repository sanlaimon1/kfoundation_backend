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
                <li class="breadcrumb-item"><a href="{{ route('role.index') }}">角色列表</a></li>
                <li class="breadcrumb-item active" aria-current="page">编辑角色</li>
            </ol>
        </nav>

        <div class="container">
            <form action="{{ route('role.update', $role->rid) }}" method="post">
                {{ csrf_field() }}
                @method('PATCH')
                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" name="title" class="form-control" id="title" placeholder="标题" value="{{$role->title}}">
                    @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mt-4">
                    <input class="form-check-input" type="radio" name="status" id="enable" value="1" @if($role->status == 1) checked @endif>
                    <label class="form-check-label" for="enable">
                        启用
                    </label>

                    <input class="form-check-input" type="radio" name="status" id="disable" value="0" @if($role->status == 0) checked @endif>
                    <label class="form-check-label" for="disable">
                        屏蔽
                    </label>
                </div>
                <div class="form-group mt-4">
                    <label for="soft">排序</label>
                    <input type="text" class="form-control" name="soft" id="soft" placeholder="排序" value="{{$role->sort}}">
                    @error('soft')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mt-4">
                    <label for="description">描述</label>
                    <textarea name="description" id="description" class="form-control" rows="5">{{$role->desc}}</textarea>
                    @error('description')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- <a class="btn btn-secondary mt-4" href="{{ route('role.index') }}" style="margin-top:1rem; margin-right:1rem;">返回</a> -->
                <button class="btn btn-secondary mt-4" action="action"  onclick="window.history.go(-1); return false;" style="margin-top:1rem; margin-right:1rem;">返回</button>
                <button type="submit" class="btn btn-primary mt-4">编辑</button>
            </form>

        </div>


    </div>

</body>

</html>