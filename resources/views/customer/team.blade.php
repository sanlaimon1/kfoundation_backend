<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>查看团队</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
            .box1, .box2 {
                display: inline-block;
            }
            ul {
                margin:0;
                padding:0;
                display:flex;
                height: 40px;
            }
            li {
                list-style-type: none;
                margin-left: .5rem;
                height: 40px;
                line-height: 40px;
            }
            li.query-children-customers {
                height: 20px;
                line-height: 20px;
                margin-right: .5rem;
            }
            .value
            {
                color:red;
            }
    </style>
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">用户列表 </a></li>
                <li class="breadcrumb-item active" aria-current="page">查看团队</li>
            </ol>
        </nav>

        <nav class="row">

            <div class="col-6">
                <form action="{{route('team_search')}}" method="POST" >
                    @csrf
                        <div class="form-group row">
                            <label class="form-label col-2">手机号：</label>
                            <input type="text" name="phone" id="phone" class="form-control col-6" style="width: 50%;" />
                            <button type="submit" class="btn-sm btn-success col-1 mx-3">搜索</button>
                        </div>
                </form>
            </div>
            <div class="col-6">
                <button class="btn-sm btn-success mx-3 col-2" id="set">设置羊毛</button>
                <button class="btn-sm btn-primary mx-3" id="unset">取消设置羊毛</button>
            </div>

            <div class="col-12">
                <ul>
                    <li>团队等级: <strong class="value">{{ $one_team->level_name }}</strong></li>
                    <li>团队人数: <strong class="value">{{ $count_children }}</strong></li>
                    <li>团队总充值(包含资产转余额): <strong class="value">{{ $one_team_extra->charge_total }}</strong></li>
                    <li>团队总提现: <strong class="value">{{ $one_team_extra->withdrawal_total }}</strong></li>
                </ul>
            </div>

            <div class="col-12">
                <ul>
                    <li class="query-children-customers">
                        <button id="level-1" level="{{ $customer_extra->level + 1 }}" data="{{ $id }}" class="btn-sm btn-primary">
                            1级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-2" level="{{ $customer_extra->level + 2 }}" data="{{ $customer_extra->level + 2 }}" class="btn-sm btn-primary">
                            2级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-3" level="{{ $customer_extra->level + 3 }}" data="{{ $customer_extra->level + 3 }}" class="btn-sm btn-primary">
                            3级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-4" level="{{ $customer_extra->level + 4 }}" data="{{ $customer_extra->level + 4 }}" class="btn-sm btn-primary">
                            4级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-5" level="{{ $customer_extra->level + 5 }}" data="{{ $customer_extra->level + 5 }}" class="btn-sm btn-primary">
                            5级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-6" level="{{ $customer_extra->level + 6 }}" data="{{ $customer_extra->level + 6 }}" class="btn-sm btn-primary">
                            6级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-7" level="{{ $customer_extra->level + 7 }}" data="{{ $customer_extra->level + 7 }}" class="btn-sm btn-primary">
                            7级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-8" level="{{ $customer_extra->level + 8 }}" data="{{ $customer_extra->level + 8 }}" class="btn-sm btn-primary">
                            8级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-9" level="{{ $customer_extra->level + 9 }}" data="{{ $customer_extra->level + 9 }}" class="btn-sm btn-primary">
                            9级会员
                        </button>
                    </li>
                    <li class="query-children-customers">
                        <button id="level-10" level="{{ $customer_extra->level + 10 }}" data="{{ $customer_extra->level + 10 }}" class="btn-sm btn-primary">
                            10级会员
                        </button>
                    </li>
                </ul>
            </div>

        </nav>

        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col"><input type="checkbox" id="check_all"></th>
                    <th scope="col">ID</th>
                    <th scope="col">用户名</th>
                    <th scope="col">羊毛</th>
                    <th scope="col">姓名</th>
                    <th scope="col">总收益</th>
                    <th scope="col">余额</th>
                    <th scope="col">创建时间</th>
                    <th scope="col">是否认证</th>
                    <th scope="col">查看</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($members as $one)
                <tr>
                    <td class="single_check">
                        <input type="checkbox" class="row_checkbox" data-item-id="{{$one->id}}">
                    </td>
                    <td>
                        {{ $one->id }}
                    </td>
                    <td>{{ $one->phone }}</td>

                    <td class="sheep_column{{$one->id}}">
                        @if ($one->is_sheep == 1)
                        <button class="btn-sm text-white bg-danger" id="change_btn"  data-id="{{$one->id}}"  data-status="{{$one->is_sheep}}">是</button>
                        @else
                        <button class="btn-sm text-white bg-success" id="change_btn"  data-id="{{$one->id}}"  data-status="{{$one->is_sheep}}">否</button>
                        @endif
                    </td>
                    <td>{{ $one->realname }}</td>
                    <td>{{ $one->customerExtra()->got_interest }}</td>
                    <td>{{ $one->balance }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td><?= $one->is_sure==1 ? '<span style="color:green;">已认证</span>' : '<span style="color:red;">未认证</span>' ?></td>
                    <td>
                        @if($one->customerExtra()->all_children_ids!=null or $one->customerExtra()->all_children_ids!='')
                        <button class="btn-sm btn-success children" href="{{ route('customer.list_children', ['id'=>$one->id]) }}">查看下级列表</button>
                        @endif
                        <a class="btn-sm btn-primary" href="{{ route('customer.show', ['customer'=>$one->id]) }}">查看会员</a>
                    </td>
                </tr>
                <!-- 查询下级动态数据 query child customers with dynamic datas //start -->

                <!-- 查询下级动态数据 query child customers with dynamic datas //end -->
                @endforeach
            </tbody>
        </table>
        <div class="container-fluid">
            <div class="box1 p-2">
                <nav aria-label="page">
                    <strong>总数: {{ $members->total() }}</strong>  <br /> {{ $members->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" >
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="100" {{ $members->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $members->perPage() == 200 ? 'selected' : '' }}>200</option>
                    <option value="500" {{ $members->perPage() == 500 ? 'selected' : '' }}>500</option>
                </select>
            </div>
            </form>
        </div>


    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

            $(document).ready(function(){
                //datepicker
                flatpickr("#date",
                {
                    enableTime: true,  // 启用时间选择
                    dateFormat: "Y-m-d H:i", // 自定义日期格式
                    locale: "zh"       // 使用中文语言
                });

                $("tbody").on("click","#change_btn",function(){
                    var id=$(this).data("id");
                    var is_sheep=$(this).data("status");

                    var data = {
                                "id":id,
                                "is_sheep":is_sheep,
                    };
                    $('.loading').show();
                    $.ajax({
                        url : "/change_sheep",
                        dataType : "json",
                        type: "POST",
                        data: data,
                        success : function(response){
                            var html = "";
                            if(response){
                                if (response.member.is_sheep == 1)
                                {
                                    html += `<button class="btn-sm text-white bg-danger" id="change_btn"  data-id="${response.member.id}"  data-status="${response.member.is_sheep}">是</button>`;

                                } else {
                                    html += ` <button class="btn-sm text-white bg-success" id="change_btn"  data-id="${response.member.id}"  data-status="${response.member.is_sheep}">否</button> `;
                                }
                                $('.loading').hide();
                                $('.sheep_column'+response.member.id).html(html);
                            }
                        }
                    })
                });

                //check all data
                const checkboxItems = document.querySelectorAll('input[type="checkbox"][data-item-id]');
                $("thead").on('click', '#check_all', function()
                {
                    $("input[type=checkbox]").prop('checked',$(this).prop('checked'));
                });

                //set wool
                $(document).on("click","#set", function()
                {
                    var isChecked = $("#check_all").prop("checked");
                    const checkedItemIds = [];

                    if(isChecked)
                    {
                        checkboxItems.forEach(item => {
                            item.checked = true;
                            checkedItemIds.push(item.getAttribute('data-item-id'));
                        });

                    }else
                    {
                        $('.row_checkbox:checked').each(function () {
                            checkedItemIds.push($(this).data('item-id'));
                        });

                    }

                    check_data = checkedItemIds;
                    var data = { checkedItemIds: check_data };
                    $('.loading').show();
                    $.ajax({
                            url : "/set_sheep",
                            dataType : "json",
                            type : "POST",
                            data :  data,
                            success : function (response) {
                                if(response){
                                    window.location.reload();
                                    $('.loading').hide();
                                }
                            }
                        });
                });

                //unset wool
                $(document).on("click","#unset", function()
                {
                    var isChecked = $("#check_all").prop("checked");
                    const checkedItemIds = [];

                    if(isChecked)
                    {
                        checkboxItems.forEach(item => {
                            item.checked = true;
                            checkedItemIds.push(item.getAttribute('data-item-id'));
                        });

                    }else
                    {
                        $('.row_checkbox:checked').each(function () {
                            checkedItemIds.push($(this).data('item-id'));
                        });

                    }

                    check_data = checkedItemIds;
                    var data = { checkedItemIds: check_data };
                    $('.loading').show();
                    $.ajax({
                            url : "/unset_sheep",
                            dataType : "json",
                            type : "POST",
                            data :  data,
                            success : function (response) {
                                if(response){
                                    window.location.reload();
                                    $('.loading').hide();
                                }
                            }
                        });
                });

                $("#team_search").click(function(){
                    var adminid = $("#adminid").val();
                    var action = $("#action").val();
                    var date = $("#date").val();
                    var data = {
                        "adminid": adminid,
                        "action": action,
                        "date" : date,
                    };

                    // $.ajax({
                    //     url : "/log_search",
                    //     dataType : "json",
                    //     type: "POST",
                    //     data: data,
                    //     success: function(response){
                    //         var html = "";
                    //         console.log(response);
                    //         $.each(response.search_logs,function(i,v){
                    //             console.log(v);
                    //         html +=`<tr>
                    //                     <td>${v.id}</td>
                    //                     <td>${v.username}</td>
                    //                     <td>${v.action}</td>
                    //                     <td>${v.ip}</td>
                    //                     <td>${v.route}</td>
                    //                     <td>${v.created_at}</td>
                    //                     <td>
                    //                         <a class="btn btn-primary" href="log/${v.id}">查看请求数据</a>
                    //                     </td>
                    //                 </tr>`;
                    //         })
                    //         $("#search_data").html(html);
                    //     }
                    // });
                });

                //查询层级用户
                $('li.query-children-customers button').click(function() {
                    $('li.query-children-customers button').removeClass('btn-success').addClass('btn-primary');
                    var this_button = $(this);
                    var level_id = this_button.attr('id');
                    $('.loading').show();
                    if(level_id=='level-1') {
                        $.get('{{ route('customer.level1',['parentid'=>$id]) }}', function(html_text){
                            $('#search_data').html(html_text);
                            $('.loading').hide();
                        }, 'html');
                    } else {
                        $.get(
                            '{{ route('customer.levelx',['id'=>$id]) }}', 
                            {
                                xlevel : this_button.attr('level')
                            },
                            function(html_text){
                                $('#search_data').html(html_text);
                                $('.loading').hide();
                            }, 
                            'html');
                    }
                    this_button.addClass('btn-success');
                });
        });

        //查询下级
        $(document).on('click', 'button.children', function(){
            var url = $(this).attr('href');
            var btn_obj = $(this);
            $('.loading').show();
            $.get(url, function(html_text){
                btn_obj.parent().parent().next('tr.child').remove();
                btn_obj.parent().parent().after('<tr class="child">'+html_text+'</tr>');
                $('.loading').hide();
            }, 'html');
        });
    </script>
    @include('loading')
</body>
</html>
