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
        .choose_logo{
            display: inline-block;
            background-color: #ffc107;
            color: white;
            padding: 0.375rem 0.75rem;
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

            $("#icon").change(function(){
                readURL(this);
            });

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-logo').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
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
                    <a href="{{ route('teamlevel.index') }}">团队等级</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">创建团队等级</li>
            </ol>
        </nav>

        <form action="{{ route('teamlevel.store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="level_name" class="form-label">团队等级名称	</label>
                        @error('level_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="level_name" name="level_name" placeholder="团队等级名称" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">等级图标</label>
                        @error('icon')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <img id="show-logo" src="" width="120" height="120" />

                        <input type="file" class="form-control" id="icon" name="icon" placeholder="项目图片" hidden>
                        <label class="choose_logo" for="icon">选择</label>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="spread_members_num" class="form-label">直推会员人数</label>
                        @error('spread_members_num')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="spread_members_num" name="spread_members_num" placeholder="直推会员人数" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="spread_leaders_num" class="form-label">直推团长人数</label>
                        @error('spread_leaders_num')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="spread_leaders_num" name="spread_leaders_num" placeholder="直推团长人数" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="accumulative_amount" class="form-label">团队累计充值</label>
                        @error('accumulative_amount')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="accumulative_amount" name="accumulative_amount" placeholder="团队累计充值" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="team_award" class="form-label">团队奖(%)	</label>
                        @error('team_award')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="team_award" name="team_award" placeholder="0.00" value="">
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="is_given" class="form-label">升级是否赠送</label>
                        @error('is_given')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select name="is_given" id="is_given" class="form-control" >
                            <option value="1">赠送</option>
                            <option value="0">不赠送</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                        <label for="award_amount" class="form-label">奖励金额</label>
                        @error('award_amount')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number"  step="any" class="form-control" id="award_amount" name="award_amount" placeholder="0.00" value="">
                    </div>
                </div>

                <!-- from level table -->
                <div class="row">
                    <div class="mb-3">
                        <label for="default_level" class="form-label">默认级别</label>
                        @error('default_level')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select name="default_level" id="default_level" class="form-control" >
                            @foreach($levels as $level)
                                <option value="{{$level->level_id}}">{{$level->level_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3">
                        <label for="status" class="form-label">状态</label>
                        @error('status')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select name="status" id="status" class="form-control" >
                            <option value="1">正常</option>
                            <option value="0">非正常</option>
                        </select>
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
