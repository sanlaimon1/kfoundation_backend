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

            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-logo').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#upload-logo").change(function(){
                readURL(this);
            });


            function readURLQRcode(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-crypto-qrcode').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#upload-crypto-qrcode").change(function(){
                readURLQRcode(this);
            });
            
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
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item"><a href="{{ route('payment.index') }}">支付设置</a></li>
                <li class="breadcrumb-item active" aria-current="page">编辑支付方式</li>
            </ol>
        </nav>
        
        <form action="{{ route('payment.update',['payment'=>$id]) }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            @method('PATCH')
            <input type="hidden" name="pid" value="{{ $id }}" />
            <section class="row frame">
                <div class="row">
                    <div class="col-3 mb-3">
                        <label for="payment_name" class="form-label">支付名称</label>
                        @error('payment_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="payment_name" name="payment_name" placeholder="支付名称" value="{{ $one->payment_name }}">
                    </div>
                    <div class="col-3 mb-3">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="sort" name="sort" placeholder="排序" value="{{ $one->sort }}">
                    </div>
                    <div class="col-3 mb-3">
                        <label for="level" class="form-label">成长值达标显示</label>
                        @error('level')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="level" name="level" placeholder="成长值达标显示" value="{{ $one->level }}">
                    </div>
                    <div class="col-3 mb-3">
                        <label for="ptype" class="form-label">支付分类</label>
                        @error('ptype')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="ptype" name="ptype"  class="form-select" >
                            @foreach( $ptypes as $key=>$one_type )
                            <option value="{{ $key }}" <?=($one->ptype==$key) ? 'selected' : '' ?> > {{ $one_type }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-4 mb-3">
                        <label for="give" class="form-label">充值赠送比例</label>
                        @error('give')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="give" name="give" placeholder="充值赠送比例" value="{{ $one->give }}">
                    </div>
                    <div class="col-5 mb-3">
                        <label for="description" class="form-label">描述</label>
                        @error('description')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="description" name="description" placeholder="描述" value="{{ $one->description }}">
                    </div>
                    <div class="col-3 mb-3">
                        <label for="rate" class="form-label">汇率(美USDT->货币)</label>
                        @error('rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="rate" name="rate" placeholder="汇率(美USDT->货币)" value="{{ $one->rate }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-6 mb-3" style="marign: 0 auto;">
                        <label for="logo" class="form-label">Logo上传</label>

                        <img id="show-logo" src="{{ $one->logo }}" width="120" height="120" />
                        
                        <input type="file" id="upload-logo" name="upload_logo" hidden/>
                        <label class="choose_logo" for="upload-logo">选择</label>
                        <!-- <button class="btn btn-warning">选择</button> -->
                    </div>
                </div>
            </section>


            @if($one->ptype==1)
            <div class="row frame">
                <p><strong style="color:red;">{{ $ptypes[ $one->ptype ] }}</strong>类型特有属性</p>
                <!-- 加密货币: 货币地址 crypto_link，货币二维码 crypto_qrcode -->
                <div class="col-6 mb-3">
                    <label for="crypto_link" class="form-label">加密货币地址</label>
                    @error('crypto_link')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <input type="text" class="form-control" id="crypto_link" name="crypto_link" placeholder="加密货币地址"
                         value="<?= array_key_exists('crypto_link', $extra) ? $extra['crypto_link'] : '' ?>">
                </div>
                <div class="col-6 mb-3">
                    <label for="crypto_qrcode" class="form-label">加密货币二维码</label>
                    <img src="<?= array_key_exists('crypto_qrcode', $extra) ? $extra['crypto_qrcode'] : '' ?>" id="show-crypto-qrcode" width="120" height="120"  />
                        
                    <input type="file" id="upload-crypto-qrcode" name="upload_crypto_qrcode" hidden/>
                    <label class="choose_logo" for="upload-crypto-qrcode">选择</label>
                    <!-- <button class="btn btn-warning">选择</button> -->
                </div>
            </div>
            @elseif($one->ptype==4)
            <div class="row frame">
                <p><strong style="color:red;">{{ $ptypes[ $one->ptype ] }}</strong>类型特有属性</p>
                <div class="col-4 mb-3">
                    <label for="bank" class="form-label">银行名称</label>
                    @error('bank')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <input type="text" class="form-control" id="bank" name="bank" placeholder="银行名称"
                         value="<?= array_key_exists('bank', $extra) ? $extra['bank'] : '' ?>">
                </div>
                <div class="col-4 mb-3">
                    <label for="bank_name" class="form-label">收款人</label>
                    @error('bank_name')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="收款人"
                         value="<?= array_key_exists('bank_name', $extra) ? $extra['bank_name'] : '' ?>">
                </div>
                <div class="col-4 mb-3">
                    <label for="bank_account" class="form-label">银行账号</label>
                    @error('bank_account')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    <input type="text" class="form-control" id="bank_account" name="bank_account" placeholder="银行账号"
                         value="<?= array_key_exists('bank_account', $extra) ? $extra['bank_account'] : '' ?>">
                </div>
            </div>
            @else
            <div class="row frame">
                <p><strong style="color:red;">未定义类型</strong></p>
            </div>
            @endif
            
            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">编辑</button>
            <button class="btn btn-secondary" action="action"  onclick="window.history.go(-1); return false;" style="margin-top:1rem; margin-right:1rem;float:right;">返回</button>
            <!-- <a class="btn btn-secondary" href="{{ route('payment.index') }}" style="margin-top:1rem; margin-right:1rem;float:right;">返回</a> -->
        </form>
        
    </div>
    @include('loading')
    @include('modal')

</body>
</html>