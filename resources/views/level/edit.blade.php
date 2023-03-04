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
                <li class="breadcrumb-item">会员中心</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('level.index') }}">会员等级</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑会员等级</li>
            </ol>
        </nav>

        <form action="{{ route('level.update',['level'=>$level->level_id]) }}" method="post">
            {{ csrf_field() }}
            @method('PATCH')
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="level_name" class="form-label">等级名称</label>
                        @error('level_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="level_name" name="level_name" placeholder="等级名称" value="{{$level->level_name}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="accumulative_amount" class="form-label">累计充值金额</label>
                        @error('accumulative_amount')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="accumulative_amount" name="accumulative_amount" placeholder="累计充值金额" value="{{$level->accumulative_amount}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="interest" class="form-label">加息利率(%)</label>
                        @error('interest')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="interest" name="interest" placeholder="0.00" value="{{$level->interest}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="personal_charge" class="form-label">个人充值奖励(％)</label>
                        @error('personal_charge')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="personal_charge" name="personal_charge" placeholder="0.00" value="{{$level->personal_charge}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="level1_award" class="form-label">一级奖励(％)</label>
                        @error('level1_award')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="level1_award" name="level1_award" placeholder="0.00" value="{{$level->level1_award}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="level2_award" class="form-label">二级奖励(％)</label>
                        @error('level2_award')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="level2_award" name="level2_award" placeholder="0.00" value="{{$level->level2_award}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="level3_award" class="form-label">三级奖励(％)</label>
                        @error('level3_award')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="level3_award" name="level3_award" placeholder="0.00" value="{{$level->level3_award}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="min_coin" class="form-label">挖矿最低虚拟币</label>
                        @error('min_coin')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="min_coin" name="min_coin" placeholder="挖矿最低虚拟币" value="{{$level->min_coin}}">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="max_coin" class="form-label">挖矿最高虚拟币</label>
                        @error('max_coin')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="max_coin" name="max_coin" placeholder="挖矿最高虚拟币" value="{{$level->max_coin}}">
                    </div>
                </div>

            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">添加</button>
        </form>

    </div>
    @include('loading')
    @include('modal')

</body>
</html>
