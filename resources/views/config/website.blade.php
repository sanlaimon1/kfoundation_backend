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
        .logo , .video_home{
            display: inline-block;
            background-color: #ffc107;
            color: white;
            padding: 0.45rem 0.75rem;
            border-radius: 0.3rem;
            cursor: pointer;
            margin-top: 1rem;
            color: #000
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
                console.log("Reach")
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

            function readURLogo(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-logo').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $(".select_logo").change(function(){
                readURLogo(this);
            });

            function readURLVideoHome(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-video-home').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $(".select_video_home").change(function(){
                console.log("reach");
                readURLVideoHome(this);
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
                <li class="breadcrumb-item active" aria-current="page">网站信息</li>
            </ol>
        </nav>
        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $website->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $website->cid }}" type="text" name="{{ $website->config_name }}" value="{{ $website->config_value }}" />
                    <button class="btn btn-danger" data="{{ $website->cid }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $domain_name->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $domain_name->cid }}" type="text" name="{{ $domain_name->config_name }}" value="{{ $domain_name->config_value }}" />
                    <button class="btn btn-danger" data="{{ $domain_name->cid }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $customer_service->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $customer_service->cid }}" type="text" name="{{ $customer_service->config_name }}" value="{{ $customer_service->config_value }}" />
                    <button class="btn btn-danger" data="{{ $customer_service->cid }}">保存</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $min_withdrawal->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $min_withdrawal->cid }}" type="text" name="{{ $min_withdrawal->config_name }}" value="{{ $min_withdrawal->config_value }}" />
                    <button class="btn btn-danger" data="{{ $min_withdrawal->cid }}">保存</button>
                </div>
            </div>
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $min_charge->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $min_charge->cid }}" type="text" name="{{ $min_charge->config_name }}" value="{{ $min_charge->config_value }}" />
                    <button class="btn btn-danger" data="{{ $min_charge->cid }}">保存</button>
                </div>
            </div>
            
        </div>

        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $times_withdrawal_everyday->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $times_withdrawal_everyday->cid }}" type="text" name="{{ $times_withdrawal_everyday->config_name }}" value="{{ $times_withdrawal_everyday->config_value }}" />
                    <button class="btn btn-danger" data="{{ $times_withdrawal_everyday->cid }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $kline_homepage->comment }}
                </label>
                <div class="card-body">
                    <select class="form-select" id="item-{{ $kline_homepage->cid }}" name="{{ $kline_homepage->config_name }}" aria-label="{{ $kline_homepage->comment }}">
                        <option value="1" <?= ($kline_homepage->config_value==1) ? 'selected' : "" ?>>是</option>
                        <option value="0" <?= ($kline_homepage->config_value==0) ? 'selected' : "" ?>>否</option>
                    </select>
                    <button class="btn btn-danger" data="{{ $kline_homepage->cid }}">保存</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $logo->comment }}
                </label>
                <form action="{{ route('website.update',['website'=>$logo->cid]) }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{ $logo->cid }}" />
                    <div class="card-body">
                        <div class="mb-3 col-6">

                            <img id="show-logo" src="{{ $logo->config_value }}" width="120" height="120" />
                            <input type="file" class="select_logo" id="item-{{ $logo->cid }}" name="logo" hidden/>
                            <label class="logo" for="item-{{ $logo->cid }}">选择</label>
                            <!-- <button class="btn btn-warning">选择</button> -->
                            <button class="btn btn-primary" type="submit">保存</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $video_homepage->comment }}
                </label>
                <form action="{{ route('website.update',['website'=>$video_homepage->cid]) }}" method="post" enctype="multipart/form-data">
                    {{ csrf_field() }}
                    @method('PATCH')
                    <input type="hidden" name="id" value="{{ $video_homepage->cid }}" />
                    <div class="card-body">
                        <div class="mb-3 col-6">
                            <img id="show-video-home" src="{{ $video_homepage->config_value }}" width="120" height="120" />
                            <input type="file" class="select_video_home" id="item-{{ $video_homepage->cid }}" name="video_home" hidden/>
                            <label class="video_home" for="item-{{ $video_homepage->cid }}">选择</label>
                            <!-- <button class="btn btn-warning">选择</button> -->
                            <button class="btn btn-primary" type="submit">保存</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    @include('loading')
    @include('modal')
</body>
</html>