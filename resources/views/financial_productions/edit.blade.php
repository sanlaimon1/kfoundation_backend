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
    <script src="/ckeditor/ckeditor.js"></script>

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
        .picture
        {

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
        });

        $(function(){
            function readURLimages(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-picture').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#picture").change(function(){
                readURLimages(this);
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
                    <a href="{{ route('financial_productions.index') }}">交易所商品管理</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑交易所商品</li>
            </ol>
        </nav>

        <form action="{{ route('financial_productions.update',$financial_product->id) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('PATCH')
            <section class="row frame">
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="production_name" class="form-label">产品名称</label>
                        @error('production_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control " id="production_name" name="production_name" placeholder="产品名称" value="{{$financial_product->production_name}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="userid" class="form-label">用户</label>
                        @error('userid')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="userid" name="userid" class="form-select" >
                            @foreach($userid_data as $one)
                            <option value="{{$one->id}}"  @if($one->id == $financial_product->userid) selected @endif>{{$one->id}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="buy_price" class="form-label">买入价格</label>
                        @error('buy_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="buy_price" name="buy_price" placeholder="买入价格" value="{{$financial_product->buy_price}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="sell_price" class="form-label">卖出价格</label>
                        @error('sell_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="sell_price" name="sell_price" placeholder="卖出价格" value="{{$financial_product->sell_price}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="days" class="form-label">天数</label>
                        @error('days')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="days" name="days" placeholder="天数" value="{{$financial_product->days}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="status" class="form-label">状态</label>
                        @error('status')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="status" name="status" class="form-select" >
                            <option value="1" @if ($financial_product->status ===1) selected @endif>上架</option>
                            <option value="0"  @if ($financial_product->status ===0) selected @endif>下架</option>
                        </select>
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="description" class="form-label">描述</label>
                        @error('description')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="description" name="description" placeholder="描述" value="{{$financial_product->description}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="fee" class="form-label">手续费百分比</label>
                        @error('fee')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="fee" name="fee" placeholder="0.00" value="{{$financial_product->fee}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="max_times" class="form-label">最大投资次数</label>
                        @error('max_times')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="max_times" name="max_times" placeholder="最大投资次数" value="{{$financial_product->max_times}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="fake_process" class="form-label">进度百分比</label>
                        @error('fake_process')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="fake_process" name="fake_process" placeholder="0.00" value="{{$financial_product->fake_process}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="increment_process" class="form-label">自增进度</label>
                        @error('increment_process')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="increment_process" name="increment_process" placeholder="0.00" value="{{$financial_product->increment_process}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="lang" class="form-label">语言</label>
                        @error('lang')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="lang" name="lang" class="form-select" >
                            <option value="cn" @if ($financial_product->lang === 'cn') selected @endif>简体中文</option>
                            <option value="en" @if ($financial_product->lang === 'en') selected @endif>English</option>
                        </select>

                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="" class="form-label">图片</label>
                        <!-- <button class="btn btn-warning">选择</button> -->
                        <img id="show-picture" src="{{$financial_product->picture}}" width="120" height="120" />
                        <input type="file" id="picture" name="picture" hidden/>
                        <label class="picture" for="picture">选择</label>
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
