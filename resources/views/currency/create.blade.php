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
    <link href="/css/jquery-ui.css" rel="stylesheet" >
    <script src="/js/jquery.min.js"></script>
    <script src="/js/jquery-ui.min.js"></script>
    <script src="i18n/datepicker-zh-TW.js"></script>

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
                <li class="breadcrumb-item active" aria-current="page">创建新币价</li>
            </ol>
        </nav>

        <form action="{{ route('currency.store') }}" method="post">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="new_price" class="form-label">最新价格</label>
                        @error('new_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control " id="new_price" name="new_price" placeholder="最新价格" value="">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="open_price" class="form-label">开盘价格</label>
                            @error('open_price')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror
                            <input type="number" step="any" class="form-control" id="open_price" name="open_price" placeholder="开盘价格" value="">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="min_price" class="form-label">最低价格</label>
                        @error('min_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="min_price" name="min_price" placeholder="最低价格" value="">
                    </div>
                    <div class="mb-3 col-6">
                        <label for="max_price" class="form-label">最高价格</label>
                        @error('max_price')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" step="any" class="form-control" id="max_price" name="max_price" placeholder="最高价格" value="">
                    </div>
                </div>
                <div class="row p-3">
                    <div class="mb-3 col-6">
                        <label for="add_time" class="form-label">添加时间</label>
                        @error('add_time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="add_time" name="add_time"  placeholder="添加时间"  value="">

                    </div>
                    <div class="mb-3 col-6">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="sort" name="sort" placeholder="排序" value="">
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
    ( function( factory ) { //chinese edition datepicker function
        if ( typeof define === "function" && define.amd ) {

            // AMD. Register as an anonymous module.
            define( [ "../widgets/datepicker" ], factory );
        } else {

            // Browser globals
            factory( jQuery.datepicker );
        }
    }( function( datepicker ) {

        datepicker.regional[ "zh-TW" ] =
        {
            closeText: "關閉",
            prevText: "&#x3C;上個月",
            nextText: "下個月&#x3E;",
            currentText: "今天",
            monthNames: [ "一月","二月","三月","四月","五月","六月",
            "七月","八月","九月","十月","十一月","十二月" ],
            monthNamesShort: [ "一月","二月","三月","四月","五月","六月",
            "七月","八月","九月","十月","十一月","十二月" ],
            dayNames: [ "星期日","星期一","星期二","星期三","星期四","星期五","星期六" ],
            dayNamesShort: [ "週日","週一","週二","週三","週四","週五","週六" ],
            dayNamesMin: [ "日","一","二","三","四","五","六" ],
            weekHeader: "週",
            dateFormat: "yy/mm/dd",
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: true,
            yearSuffix: "年"
        };
        datepicker.setDefaults( datepicker.regional[ "zh-TW" ] );
        return datepicker.regional[ "zh-TW" ];
    }));
    $(document).ready(function() {

        $(function() {
            $( "#add_time" ).datepicker({
                dateFormat: 'yy-mm-dd',
                language : 'sv'
            });


        });
    })
</script>



