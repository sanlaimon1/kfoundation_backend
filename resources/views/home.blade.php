<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <link rel="stylesheet" href="/static/adminlte/plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" href="/static/adminlte/dist/css/adminlte.min.css?v=3.2.0">
    <link rel="stylesheet" href="/css/loading.css">
    <style>
        body {
            color: black;
        }

        .nav-link {
            /* color: black; */
        }

        .content .item1 .info-box {
            min-height: 165px;
        }

        .content .item1 .info-box-content {
            justify-content: unset;
        }

        .content .item2 .info-box {
            background-color: #7986CB;
        }

        .content .item2 .info-box-content {
            text-align: center;
            color: white;
        }

        ul#sys-menu 
        {
            margin: 0 auto;
            padding: 0;
            display: flex;
            width: 80%;
        }
        ul#sys-menu li
        {
            flex: 1;
            list-style-type: none;
            text-align: center;
            height: 2.5rem;
            line-height: 2.5rem;
            cursor: pointer;
        }
        ul#sys-menu li.active, ul#sys-menu li:hover
        {
            background-color: #007bff;
            border-radius: 5px;
            color: #fff;
        }
        ul#sys-menu li.active a, ul#sys-menu li:hover a
        {
            color: #fff;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini">

    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white
                navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link " data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
            </ul>

            <ul id="sys-menu">
                <li class="active">
                    <a href="{{ route('satistatics') }}" target="content">
                        后台首页
                    </a>
                </li>
                @foreach($menu_items as $key=>$item)
                <li subkey="{{ route( 'subitem', ['keyid'=>$key] ) }}">
                    {{ $item }}
                </li>
                @endforeach
            </ul>

            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg
                            dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15
                            Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new
                            messages
                            <span class="float-right text-muted text-sm">3
                                mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2"></i> 8 friend
                            requests
                            <span class="float-right text-muted text-sm">12
                                hours</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-file mr-2"></i> 3 new reports
                            <span class="float-right text-muted text-sm">2
                                days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See
                            All Notifications</a>
                    </div>
                </li>
                <li class="nav-item dropdown">

                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <div class="user-nav d-sm-flex d-none">
                            <span>{{ Auth::user()->username }}</span>
                            <span>
                                <img src="/static/images/logout.jpg" class="img-circle"
                                    style="width:30px; height:30px;" />
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-divider"></div>
                        <form action="/logout" method="post">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="fas fa-sign-out-alt mr-2"></i>登出
                            </button>
                        </form>
                    </div>

                </li>
            </ul>
        </nav>

        <!-- Left Side bar -->
        <aside class="main-sidebar sidebar-light-primary elevation-4
                bg-light">
            <a href="{{route('home')}}" class="brand-link">
                <img src="/static/images/logo.png" alt="Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-bold">DBSchenker</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul id="nav-tree" class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">

                        <li class="nav-item">
                            <a href="{{ route('satistatics') }}" target="content" class="nav-link">
                                <i class="fas fa-fw fa-home"></i>
                                <p>后台统计</p>
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a href="" class="nav-link">
                                <i class="fa fa-users"></i>
                                <p>
                                    会员管理
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('customer.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-users"></i>
                                        <p>会员列表</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('asset.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-money-bill"></i>
                                        <p>资产流水记录</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('balance.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-money-bill"></i>
                                        <p>余额流水记录</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('integration.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-money-bill"></i>
                                        <p>积分流水记录</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('platformcoin.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-money-bill"></i>
                                        <p>平台币流水记录</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('withdrawal.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-money-check"></i>
                                        <p>余额提现审核</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('charge.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-file-contract"></i>
                                        <p>资产充值审核</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a href="" class="nav-link">
                                <i class="fa fa-cog fa-fw"></i>
                                <p>
                                    项目管理
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('project.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-cog fa-fw"></i>
                                        <p>项目列表</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('order1.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-user"></i>
                                        <p>已购项目</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('interest.index') }}" class="nav-link" target="content">
                                        <i class="fa fa-blog"></i>
                                        <p>返息明细</p>
                                    </a>
                                </li>
                            </ul>
                        </li>


                    </ul>
                </nav>


            </div>

        </aside>

        <!-- Content -->
        <div class="content-wrapper">

            <div class="content">
                <iframe src="{{ route('satistatics') }}" name="content" width="100%" style="min-height: 700px;" frameborder="no" id="iframe">
                </iframe>
            </div>

        </div>

    </div>

    <svg class="loading" viewBox="0 0 120 120" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
        <g id="circle" class="g-circles g-circles--v1">
            <circle id="12" transform="translate(35, 16.698730) rotate(-30) translate(-35, -16.698730) " cx="35" cy="16.6987298" r="10"></circle>
            <circle id="11" transform="translate(16.698730, 35) rotate(-60) translate(-16.698730, -35) " cx="16.6987298" cy="35" r="10"></circle>
            <circle id="10" transform="translate(10, 60) rotate(-90) translate(-10, -60) " cx="10" cy="60" r="10"></circle>
            <circle id="9" transform="translate(16.698730, 85) rotate(-120) translate(-16.698730, -85) " cx="16.6987298" cy="85" r="10"></circle>
            <circle id="8" transform="translate(35, 103.301270) rotate(-150) translate(-35, -103.301270) " cx="35" cy="103.30127" r="10"></circle>
            <circle id="7" cx="60" cy="110" r="10"></circle>
            <circle id="6" transform="translate(85, 103.301270) rotate(-30) translate(-85, -103.301270) " cx="85" cy="103.30127" r="10"></circle>
            <circle id="5" transform="translate(103.301270, 85) rotate(-60) translate(-103.301270, -85) " cx="103.30127" cy="85" r="10"></circle>
            <circle id="4" transform="translate(110, 60) rotate(-90) translate(-110, -60) " cx="110" cy="60" r="10"></circle>
            <circle id="3" transform="translate(103.301270, 35) rotate(-120) translate(-103.301270, -35) " cx="103.30127" cy="35" r="10"></circle>
            <circle id="2" transform="translate(85, 16.698730) rotate(-150) translate(-85, -16.698730) " cx="85" cy="16.6987298" r="10"></circle>
            <circle id="1" cx="60" cy="10" r="10"></circle>
        </g>
        <use xlink:href="#circle" class="use"/>
    </svg>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>

    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>

    <script>
        $(function () {
            $("#includedNav_sidebar").load("nav_sidebar.html");
            //加载a 为target="content"
            //$('#nav-tree a').attr("target", "content");
            $('#nav-tree a').click(function () {
                $('#nav-tree a, #nav-tree .nav-item p').removeClass("active");
                $(this).addClass("active");
            });

            //点击主菜单，变换状态，并且加载数据到左侧
            $('#sys-menu li[subkey]').click(function(){
                var subkey = $(this).attr('subkey');
                var ss = $(this);
                //加载动画
                $('.loading').show();
                //请求ajax 成功后关闭加载动画。 改变active的样式。 并且清空 $('#nav-tree').html(); 更新菜单
                $.ajax({
                    type: "GET",
                    url: subkey,
                    dataType: "json",
                    success: function(msg){
                        $('#nav-tree').html('');
                        //构建目录结构
                        var item_name = msg.item_name;
                        var subitems = msg.subitems;
                        var html_string = '<li class="nav-item menu-is-opening menu-open"><a href="" class="nav-link"><i class="fa fa-cog fa-fw"></i><p>'
                                    + item_name + '<i class="fas fa-angle-left right"></i></p></a><ul class="nav nav-treeview">';

                        $.each(subitems, function(index, value) {
                            html_string += '<li class="nav-item"><a href="' + value + '" class="nav-link" target="content">'
                                    + '<i class="fa fa-link"></i><p>' + index + '</p></a></li>';
                        });
                        html_string += '</ul></li>';

                        $('#nav-tree').html(html_string);

                        //改变active的样式。
                        $('#sys-menu li').removeClass('active');
                        ss.addClass('active');
                        //关闭动画
                        $('.loading').hide();
                    },
                    'error': function (jqXHR, textStatus, errorThrown) {
                        alert(errorThrown);
                    }
                });

            });
        });
    </script>

</body>

</html>
