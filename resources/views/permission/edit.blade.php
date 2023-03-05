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
            @if (session('message'))
            <div class="alert alert-danger mb-5 alert-dismissible fade show" role="alert">
                <strong>{{session('message')}}</strong>
            </div>
            @endif
            <form action="{{ route('permission.store') }}" method="post">
                {{ csrf_field() }}
                @method('POST')
                <div class="row">
                    <div class="form-group mt-4">
                        <label for="auth">请选择栏目:</label>
                        <select id="first-menu">
                            <option>--请选择栏目--</option>
                            @foreach( $first_menus as $key=>$one_item )
                            <option value="{{ $key }}" @if($permission->path_name == $key) selected @endif >{{ $one_item }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-4">
                        <label for="auth">请选择URI:</label>
                        <select id="second-menu" name="path_name">

                        </select>
                        @error('path_name')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-group mt-4">
                    <label for="role_id">角色</label>
                    <select id="role_id" class="form-control" name="role_id">
                        @foreach($roles as $role)
                        <option value="{{$role->rid}}">{{$role->title}}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-4">
                    <label for="auth2">子栏目权限值</label>
                    <div class="row">
                        <div class="col-3">
                            <input type="checkbox" id="auth2_create" name="auth2_create" value="1">
                            <label for="auth2_create">创建</label>
                        </div>
                        <div class="col-3">
                            <input type="checkbox" id="auth2_read" name="auth2_read" value="2">
                            <label for="auth2_read">查询</label>
                        </div>
                        <div class="col-3">
                            <input type="checkbox" id="auth2_update" name="auth2_update" value="4">
                            <label for="auth2_update">修改</label>
                        </div>
                        <div class="col-3">
                            <input type="checkbox" id="auth2_delete" name="auth2_delete" value="8">
                            <label for="auth2_delete">删除</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-4">Submit</button>
            </form>

        </div>


    </div>
    @include('loading')
    @include('modal')
    <script>
        $(function() {
            $('#first-menu').change(function() {
                var dataid = $(this).val();
                $('.loading').show();

                $.ajax({
                    type: "get",
                    url: '/roles/geturi/' + dataid,
                    dataType: "json",
                    success: function(msg) {
                        if (msg.code == 1) {
                            $('#second-menu').html('');
                            var html_string = "";
                            $.each(msg.datas, function(index, val) {
                                html_string += '<option value="' + val + '">' + index + '</option>';
                            });
                            $('#second-menu').html(html_string);
                        }

                        $('.loading').hide(); //关闭动画  close the loading animation
                        //window.reload();
                    },
                    'error': function(jqXHR, textStatus, errorThrown) {
                        if (jqXHR.status == 419) {
                            $('.modal-body').html('网页已过期, 请刷新后再修改数据');
                            $('#myModal').show();
                        } else if (jqXHR.status == 500) {
                            $('.modal-body').html('服务器内部错误 500');
                            $('#myModal').show();
                        } else {
                            $('.modal-body').html(errorThrown);
                            $('#myModal').show();
                        }
                        $('.loading').hide();
                    }
                });

            });
        });
    </script>
</body>

</html>