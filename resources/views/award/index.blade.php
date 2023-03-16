<!DOCTYPE html>
<html lang="zh">
<head>
    <title>奖励设置</title>
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
                    url: '/award/' + dataid,
                    dataType: "json",
                    data: { award_value:  config_value_string },
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
                <li class="breadcrumb-item">奖励管理</li>
                <li class="breadcrumb-item active" aria-current="page">系统奖励</li>
            </ol>
        </nav>
        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $registration_award->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $registration_award->id }}" type="text" name="{{ $registration_award->award_name }}" value="{{ $registration_award->award_value }}" />
                    <button class="btn btn-danger" data="{{ $registration_award->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $realname_award->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $realname_award->id }}" type="text" name="{{ $realname_award->award_name }}" value="{{ $realname_award->award_value }}" />
                    <button class="btn btn-danger" data="{{ $realname_award->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $everyday_award->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $everyday_award->id }}" type="text" name="{{ $everyday_award->award_name }}" value="{{ $everyday_award->award_value }}" />
                    <button class="btn btn-danger" data="{{ $everyday_award->id }}">保存</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $first_invest->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $first_invest->id }}" type="text" name="{{ $first_invest->award_name }}" value="{{ $first_invest->award_value }}" />
                    <button class="btn btn-danger" data="{{ $first_invest->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $reinvest->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $reinvest->id }}" type="text" name="{{ $reinvest->award_name }}" value="{{ $reinvest->award_value }}" />
                    <button class="btn btn-danger" data="{{ $reinvest->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $balance_benefit->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $balance_benefit->id }}" type="text" name="{{ $balance_benefit->award_name }}" value="{{ $balance_benefit->award_value }}" />
                    <button class="btn btn-danger" data="{{ $balance_benefit->id }}">保存ggg</button>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $balance_min->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $balance_min->id }}" type="text" name="{{ $balance_min->award_name }}" value="{{ $balance_min->award_value }}" />
                    <button class="btn btn-danger" data="{{ $balance_min->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $balance_max->comment }}
                </label>
                <div class="card-body">
                <input class="form-control" id="item-{{ $balance_max->id }}" type="text" name="{{ $balance_max->award_name }}" value="{{ $balance_max->award_value }}" />
                    <button class="btn btn-danger" data="{{ $balance_max->id }}">保存</button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $machine_days->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $machine_days->id }}" type="text" name="{{ $machine_days->award_name }}" value="{{ $machine_days->award_value }}" />
                    <button class="btn btn-danger" data="{{ $machine_days->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $machine_yield->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $machine_yield->id }}" type="text" name="{{ $machine_yield->award_name }}" value="{{ $machine_yield->award_value }}" />
                    <button class="btn btn-danger" data="{{ $machine_yield->id }}">保存</button>
                </div>
            </div>

            <div class="card">
                <label class="card-title bg-success text-white">
                    {{ $machine_rate->comment }}
                </label>
                <div class="card-body">
                    <input class="form-control" id="item-{{ $machine_rate->id }}" type="text" name="{{ $machine_rate->award_name }}" value="{{ $machine_rate->award_value }}" />
                    <button class="btn btn-danger" data="{{ $machine_rate->id }}">保存</button>
                </div>
            </div>

        </div>


    </div>
    @include('loading')
    @include('modal')
</body>
</html>
