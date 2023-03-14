<!DOCTYPE html>
<html lang="zh">
<head>
    <title>合同设置</title>
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
                    url: '/agreement/' + dataid,
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

    <!-- include summernote css/js -->
    <link href="/css/bootstrap-v3.4.1.min.css" rel="stylesheet">
    <script src="/js/jquery-v3.5.1.min.js"></script>
    <script src="/js/bootstrap-v3.4.1.min.js"></script>
    <link href="/css/summernote.min.css" rel="stylesheet">
    <script src="/js/summernote.min.js"></script>
    <script src="/js/summernote-zh-CN.js"></script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item active" aria-current="page">首页弹窗提示</li>
            </ol>
        </nav>
        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $agreement->comment }}
                </label>
                <div class="card-body" style="flex-direction:column;">
                    <textarea class="form-control" id="item-{{ $agreement->cid }}" name="{{ $agreement->config_name }}">{{ html_entity_decode( $agreement->config_value ) }}</textarea>
                    <br />
                    <p>
                        <button class="btn btn-danger" data="{{ $agreement->cid }}">保存</button>
                    </p>
                </div>
            </div>
        </div>

    </div>
    @include('loading')
    @include('modal')

    <script>
        $(document).ready(function() {
            $('#item-{{ $agreement->cid }}').summernote({
                lang: 'zh-CN'
            });
        });
    </script>
</body>
</html>