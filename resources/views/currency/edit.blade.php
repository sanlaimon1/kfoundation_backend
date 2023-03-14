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
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('currency.index') }}">币价管理</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑币价管理</li>
            </ol>
        </nav>

        <form action="{{ route('currency.update',['currency'=>$currency->id]) }}" method="post">
            {{ csrf_field() }}
            @method('PATCH')
            <section class="row frame">
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="new_price" class="form-label">最新价格</label>
                        @error('new_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control " id="new_price" name="new_price" placeholder="最新价格" value="{{$currency->new_price}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="open_price" class="form-label">开盘价格</label>
                            @error('open_price')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <input type="number" step="any" class="form-control" id="open_price" name="open_price" placeholder="开盘价格" value="{{$currency->open_price}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="min_price" class="form-label">最低价格</label>
                        @error('min_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="min_price" name="min_price" placeholder="最低价格" value="{{$currency->min_price}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="max_price" class="form-label">最高价格</label>
                        @error('max_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="max_price" name="max_price" placeholder="最高价格" value="{{$currency->max_price}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="add_time" class="form-label">添加时间</label>
                        @error('add_time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="hidden" name="add_time" id="add_time_formatted" value="">
                        <input type="datetime-local" step="1" class="form-control" id="add_time"  placeholder="添加时间"  value="{{$currency->add_time}}">

                    </div>
                    <div class="mb-3 col-6">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="sort" name="sort" placeholder="排序" value="{{$currency->sort}}">
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

<script>
    const dateInput = document.getElementById('add_time');
    $(document).ready(function()
    {
        dateInput.addEventListener('change',function()
        {
            const dateTimeString = dateInput.value;
            console.log(dateTimeString);
            const formattedDateTimeString = dateTimeString.replace("T", " ").replace(/\.\d{3}Z/, "");
            $("#add_time_formatted").val(formattedDateTimeString);

        })

    });

</script>

