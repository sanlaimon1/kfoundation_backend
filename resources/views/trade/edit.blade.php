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
        .images
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
                        $('#show-images').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#images").change(function(){
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
                <li class="breadcrumb-item">商城管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('trade.index') }}">交易所管理</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">编辑交易所</li>
            </ol>
        </nav>

        <form action="{{ route('trade.update',['trade'=>$trade_goods->id]) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('PATCH')
            <section class="row frame">
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="goods_name" class="form-label">商品名称</label>
                        @error('goods_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control " id="goods_name" name="goods_name" placeholder="商品名称" value="{{$trade_goods->goods_name}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="days" class="form-label">天数</label>
                            @error('days')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <input type="number" class="form-control" id="days" name="days" placeholder="天数" value="{{$trade_goods->days}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="price" class="form-label">价格</label>
                        @error('price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="price" name="price" placeholder="价格" value="{{$trade_goods->price}}">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="fee" class="form-label">手续费百分比</label>
                        @error('fee')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="fee" name="fee" placeholder="手续费百分比" value="{{$trade_goods->fee}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-4">
                        <label for="step" class="form-label">步骤</label>
                        @error('step')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="step" name="step" placeholder="步骤" value="{{$trade_goods->step}}">
                    </div>
                    <div class="mb-3 col-4">
                        <label for="is_over" class="form-label">是否结束 </label>
                        @error('is_over')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_over" name="is_over" class="form-select" >
                            <option value="0" @if($trade_goods->is_over ===0) selected @endif>未结束</option>
                            <option value="1" @if($trade_goods->is_over === 1) selected @endif>结束</option>
                        </select>
                    </div>
                    <div class="mb-3 col-4">
                        <label for="show" class="form-label">显示 / 不显示 </label>
                        @error('show')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="show" name="show" class="form-select" >
                            <option value="0"  @if($trade_goods->show === 0) selected @endif>不显示</option>
                            <option value="1" @if ($trade_goods->show === 1) selected @endif>显示</option>
                        </select>
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="next_id" class="form-label">下一个商品变化的id</label>
                        @error('next_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="next_id" name="next_id" class="form-select" >
                            <option value="0" @if($trade_goods->next_id == 0) selected @endif>0</option>
                        @foreach($nextid_data as $one)
                            <option value="{{$one->id}}" @if($one->id == $trade_goods->next_id) selected @endif>{{$one->id}}</option>
                        @endforeach
                        </select>

                    </div>
                    <div class="mb-3 col-6">
                        <label for="selling_price" class="form-label">卖出价格</label>
                        @error('selling_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="selling_price" name="selling_price" placeholder="0.00" value="{{$trade_goods->selling_price}}">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-12">
                        <label for="content" class="form-label">描述</label>
                        @error('content')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <textarea class="form-control" id="content" name="content">{{ html_entity_decode( $trade_goods->description ) }}</textarea>
                        <script>
                           CKEDITOR.replace('content',{
                                language: 'zh'
                            });
                        </script>
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="" class="form-label">缩略图</label>
                        <!-- <button class="btn btn-warning">选择</button> -->
                        <img id="show-images" src="{{$trade_goods->images}}" width="120" height="120" />
                        <input type="file" id="images" name="images" hidden/>
                        <label class="images" for="images">选择</label>
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
