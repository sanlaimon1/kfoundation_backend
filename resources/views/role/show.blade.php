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

        li {
            margin-top: .5rem;
            list-style-type: none;
            display: flex;
        }

        li span {
            display: block;
        }

        li span.sub_name {
            width: 10rem;
        }

        li span.sub_path {
            width: 10rem;
        }

        li span.sub_permission {
            width: auto;
            display: flex;
        }

        .main-item {
            margin-top: 1rem;
            padding-left: 1rem;
        }

        .sub-item {
            padding-left: 2rem;
            border-bottom: 1px dashed black;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('role.index') }}">
                        角色列表
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">显示角色权限</li>
            </ol>
        </nav>
        <ul>

            <!-- index 1
            create 2
            store 4
            show 8
            edit 16
            update 32
            destory 64 -->

            <!-- 权限列表 -->
            @foreach( $items as $uripath=>$one_item )
            <li>
            <li class="main-item">{{ $one_item['main_name'] }}</li>
            <ul>
                @foreach( $one_item['sub_menu'] as $sub_name=>$sub_path )
                <form action="{{ route('permission.store') }}" method="post">
                    {{ csrf_field() }}
                    <li class="sub-item">
                        <input type="text" name="role_id" value="{{$id}}" hidden>
                        <input type="text" class="sub_path border-0" name="path_name" value="{{$sub_path}}" hidden>
                        <span class="sub_name">{{ $sub_name }}</span>
                        <span class="sub_path">{{ $sub_path }}</span>
                        <span class="sub_permission">

                            @php
                                $permission = App\Models\Permission::where("path_name" , "=", $sub_path)->where("role_id", "=", Auth::user()->rid)->first();
                            @endphp                            
                            
                            <div class="col-2">
                                <input type="checkbox" id="{{$sub_path}}index" name="index" value="1" @if( ($permission['auth2']??0) & 1 ) checked @endif>
                                <label for="{{$sub_path}}index">列出</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="{{$sub_path}}create" name="create" value="2" @if( ($permission['auth2']??0) & 2 ) checked @endif>
                                <label for="{{$sub_path}}create">创建页面</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="{{$sub_path}}store" name="store" value="4" @if( ($permission['auth2']??0) & 4 ) checked @endif>
                                <label for="{{$sub_path}}store">创建逻辑</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="{{$sub_path}}show" name="show" value="8" @if( ($permission['auth2']??0) & 8 ) checked @endif>
                                <label for="{{$sub_path}}show">显示</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="{{$sub_path}}edit" name="edit" value="16" @if( ($permission['auth2']??0) & 16 ) checked @endif>
                                <label for="{{$sub_path}}edit">编辑页面</label>
                            </div>
                            <div class="col-3">
                                <input type="checkbox" id="{{$sub_path}}update" name="update" value="32" @if( ($permission['auth2']??0) & 32 ) checked @endif>
                                <label for="{{$sub_path}}update">编辑逻辑</label>
                            </div>
                            <div class="col-3">
                                <input type="checkbox" id="{{$sub_path}}destory" name="destory" value="64" @if( ($permission['auth2']??0) & 64 ) checked @endif>
                                <label for="{{$sub_path}}destory">删除</label>
                            </div>
                            <div class="col-3">
                                <button type="submit" class="btn btn-primary mt-4">保存</button>
                            </div>
                        </span>
                    </li>
                </form>
                @endforeach
            </ul>
            </li>
            @endforeach
        </ul>



    </div>
    <script>
        function del() {
            var msg = "您真的确定要删除吗？\n\n请确认！";
            if (confirm(msg) == true) {
                return true;
            } else {
                return false;
            }
        }
    </script>
</body>

</html>