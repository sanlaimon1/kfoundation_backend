<!DOCTYPE html>
<html lang="zh">
<head>
    <title>APP版本设置</title>
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
        #app .row
        {
            display: flex;
        }
        #app .card
        {
            margin: .5rem;
            padding: 0;
            flex: 1;
        }
        #app .card .card-body
        {
           display: flex;
        }
        #app .card .card-body .form-control, #app .card .card-body .form-select
        {
           width: 70%;
        }
        #app .card .card-body .btn
        {
           margin-left: 1rem;
        }
        #app .card .bg-success
        {
            padding-left: 1rem;
        }
        #nav
        {
            padding: .3rem auto;
            border-bottom: 1px solid #ccc;
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
                    url: '/version/' + dataid,
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
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item active" aria-current="page">app版本设置</li>
            </ol>
        </nav>
        <div class="row">
            <div class="card col-6">
                <label class="card-title bg-success text-white">
                    {{ $app_version->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $app_version->cid }}"
                     name="{{ $app_version->config_name }}" value="{{ $app_version->config_value }}" />
                    
                    <button class="btn btn-danger" data="{{ $app_version->cid }}">保存</button>
                </div>
            </div>

            <div class="card col-6">
                <label class="card-title bg-success text-white">
                    {{ $app_update->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $app_update->cid }}"
                     name="{{ $app_update->config_name }}" value="{{ $app_update->config_value }}" />
                    
                    <button class="btn btn-danger" data="{{ $app_update->cid }}">保存</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card col-4">
                <label class="card-title bg-success text-white">
                    {{ $android->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $android->cid }}"
                     name="{{ $android->config_name }}" value="{{ $android->config_value }}" />
                    
                    <button class="btn btn-danger" data="{{ $android->cid }}">保存</button>
                </div>
            </div>

            <div class="card col-4">
                <label class="card-title bg-success text-white">
                    {{ $android_apk->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $android_apk->cid }}"
                     name="{{ $android_apk->config_name }}" value="{{ $android_apk->config_value }}" />
                    
                    <button class="btn btn-danger" data="{{ $android_apk->cid }}">保存</button>
                </div>
            </div>

            <div class="card col-4">
                <label class="card-title bg-success text-white">
                    {{ $ios->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $ios->cid }}"
                     name="{{ $ios->config_name }}" value="{{ $ios->config_value }}" />
                    
                    <button class="btn btn-danger" data="{{ $ios->cid }}">保存</button>
                </div>
            </div>
        </div>

    </div>
    @include('loading')
    @include('modal')
</body>
</html>