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
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('customer.index') }}">用户中心</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑客户信息 <strong style="color:red;">{{ $onecustomer->realname }}</strong></li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{ route('customer.update',['customer'=>$onecustomer->id]) }}" method="post">
            {{ csrf_field() }}
            @method('PATCH')
            <input type="hidden" name="id" value="{{ $onecustomer->id }}" />
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="phone" class="form-label">手机号</label>
                        @error('phone')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="phone" name="phone" placeholder="手机号" value="{{ $onecustomer->phone }}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="realname" class="form-label">姓名</label>
                        @error('realname')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="realname" name="realname" placeholder="姓名" value="{{$onecustomer->realname}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="asset" class="form-label">余额</label>
                        @error('asset')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="asset" name="asset" placeholder="0.00" value="{{ $onecustomer->asset }}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="balance" class="form-label">资产</label>
                        @error('balance')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="balance" name="balance" placeholder="0.00" value="{{ $onecustomer->balance }}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="integration" class="form-label">积分</label>
                        @error('integration')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="integration" name="integration" placeholder="0" value="{{ $onecustomer->integration }}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="platform_coin" class="form-label">平台币</label>
                        @error('platform_coin')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="platform_coin" name="platform_coin" placeholder="0" value="{{ $onecustomer->platform_coin }}">
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">编辑</button>
            <button class="btn btn-secondary" action="action"  onclick="window.history.go(-1); return false;" style="margin-top:1rem; margin-right:1rem; float:right;">返回</button>
            <!-- <a class="btn btn-secondary" href="{{ route('customer.index') }}" style="margin-top:1rem; margin-right:1rem; float:right;">返回</a> -->
        </form>

    </div>
    @include('loading')
    @include('modal')

</body>
</html>
