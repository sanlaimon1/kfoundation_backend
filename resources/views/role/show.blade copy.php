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
        li
        {
            margin-top: .5rem;
            list-style-type: none;
            display: flex;
        }
        li span
        {
            display: block;
        }
        li span.sub_name
        {
            width: 10rem;
        }
        li span.sub_path
        {
            width: 10rem;
        }
        li span.sub_permission
        {
            width: auto;
            display: flex;
        }
        .main-item
        {
            margin-top: 1rem;
            padding-left: 1rem;
        }
        .sub-item
        {
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
            <!-- 权限列表 -->
            @foreach( $items as $uripath=>$one_item )
            <li>
                <li class="main-item">{{ $one_item['main_name'] }}</li>
                <ul>
                    @foreach( $one_item['sub_menu'] as $sub_name=>$sub_path )
                    <li class="sub-item">
                        <span class="sub_name">{{ $sub_name }}</span>
                        <span class="sub_path">{{ $sub_path }}</span>
                        <span class="sub_permission">
                            <div class="col-2">
                                <input type="checkbox" id="auth2_read" name="index" value="2">
                                <label for="auth2_read">列出所有</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="auth2_create" name="create" value="1">
                                <label for="auth2_create">创建页面</label>
                            </div>
                            
                            <div class="col-2">
                                <input type="checkbox" id="auth2_update" name="store" value="4">
                                <label for="auth2_update">创建逻辑</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="auth2_delete" name="edit" value="8">
                                <label for="auth2_delete">编辑页面</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="auth2_delete" name="update" value="8">
                                <label for="auth2_delete">编辑逻辑</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="auth2_delete" name="show" value="8">
                                <label for="auth2_delete">查询一条</label>
                            </div>
                            <div class="col-2">
                                <input type="checkbox" id="auth2_delete" name="destroy" value="8">
                                <label for="auth2_delete">删除</label>
                            </div>
                        </span>
                    </li>
                    @endforeach
                </ul>
            </li>
            @endforeach
        </ul>
        
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