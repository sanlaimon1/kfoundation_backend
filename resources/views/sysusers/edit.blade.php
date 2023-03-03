<!DOCTYPE html>
<html lang="zh">
<head>
    <title>参数设置</title>
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
        #app .form-label
        {
            color: green;
            font-size: 14px;
        }
        #app .alert
        {
            font-size: 14px;
        }
        #app .frame
        {
            border: 1px solid black;
            border-radius:5px;
            margin-top: .5rem;
        }
    </style>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function(){
            $('button.btn[data]').click(function(){
                $('.loading').show();
                var dataid = $(this).attr('data');
                var config_value_string = $('#item-' + dataid).val();
                
                $.ajax({
                    type: "patch",
                    url: '/website/' + dataid,
                    dataType: "json",
                    data: { config_value:  config_value_string },
                    success: function(msg){
                        $('.modal-body').html(msg.message);
                        $('#myModal').show();
                        $('.loading').hide();   //关闭动画  close the loading animation
                        //window.reload();
                    },
                    'error': function (jqXHR, textStatus, errorThrown) {
                        if(jqXHR.status==419) {
                            $('.modal-body').html('网页已过期, 请刷新后再修改数据');
                            $('#myModal').show();
                        } else if(jqXHR.status==500) {
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

            $('button.btn-close, #btn-close').click(function(){
                $('#myModal').hide();
                location.reload();
            });
        });
    </script>

</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统用户管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('sysusers.index') }}">用户列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑用户 <strong style="color:red;">{{ $one->username }}</strong></li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        
        <form action="{{ route('sysusers.update',['sysuser'=>$id]) }}" method="post">
            {{ csrf_field() }}
            @method('PATCH')
            <input type="hidden" name="id" value="{{ $id }}" />
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="desc" class="form-label">描述</label>
                        @error('desc')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="desc" name="desc" placeholder="描述" value="{{ $one->desc }}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="status" class="form-label">状态</label>
                        @error('status')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="status" name="status"  class="form-select" >
                            <option value="1" <?=($one->status==1) ? 'selected' : '' ?> >启用</option>
                            <option value="0" <?=($one->status==0) ? 'selected' : '' ?> >禁用</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="ptype" class="form-label">角色</label>
                        @error('rid')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="rid" name="rid"  class="form-select" >
                            @foreach( $roles as $key=>$one_role )
                            <option value="{{ $key }}" <?=($one->rid==$key) ? 'selected' : '' ?> > {{ $one_role }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">修改</button>
        </form>
        
    </div>
    @include('loading')
    @include('modal')

</body>
</html>