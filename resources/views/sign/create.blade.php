<!DOCTYPE html>
<html lang="zh">
<head>
    <title>创建签到</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <link href="/css/jquery-ui.css" rel="stylesheet" >
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
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
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">奖励管理</li>
                <li class="breadcrumb-item">
                    <a href="{{ route('sign.index') }}">签到日期列表</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">创建</li>
            </ol>
        </nav>

        <form action="{{ route('sign.store') }}" method="post">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="mb-3">
                        <label for="signdate" class="form-label">日期</label>
                        @error('signdate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="signdate"
                            name="signdate" autocomplete="false" placeholder="日期" value="{{ old('signdate') }}" required />

                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">添加</button>
        </form>

    </div>
    @include('loading')
    @include('modal')

</body>
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
              $( "#signdate" ).datepicker({
                dateFormat: 'yy-mm-dd',
                language : 'sv'
              });


          });
      })
</script>
</html>
