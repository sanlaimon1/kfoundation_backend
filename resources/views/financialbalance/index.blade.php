<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
        #app td, #app th
        {
            font-size: 14px;
        }
        .box1, .box2 {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
        <h3 class="text-center text-primary">{{ $title }}</h3>
        <nav class="row">
            <div class="col-3">
                <label class="form-label">财务编号：</label>
                <input type="text" name="financial_id" id="financial_id" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">用户名：</label>
                <input type="text" name="customer" id="customer" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">财务类型：</label>
                <select name="financial_type" id="financial_type"  class="form-select">
                    <option value="0">--请选择--</option>
                    @foreach($types as $type_val=>$one_type)
                    <option value="{{ $type_val }}">{{ $one_type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="text" name="date" id="date" class="form-control" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="balance_search">查询</button>
            </div>
        </nav>
        <br />
        <table class="table table-bordered table-striped text-center" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">财务编号</th>
                    <th scope="col">用户名</th>
                    <th scope="col">财务类型</th>
                    <th scope="col">发生前余额</th>
                    <th scope="col">金额</th>
                    <th scope="col">当前余额</th>
                    <th scope="col">发生时间</th>
                    <th scope="col">备注</th>
                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($records as $one)
                <tr>
                    <td>{{ $one->id }}</td>
                    <td>{{ $one->customer->phone }}</td>
                    <td>{{ $types[ $one->financial_type ]}}</td>
                    <td>
                        {{ $one->balance }}
                    </td>
                    <td>
                        @if($one->direction==1)
                        <span style="color:green;">+{{ $one->amount }}</span>
                        @elseif($one->direction==-1)
                        <span style="color:red;">-{{ $one->amount }}</span>
                        @else
                        方向错误
                        @endif
                    </td>
                    <td>{{ $one->after_balance }}</td>
                    <td>{{ $one->created_at }}</td>
                    <td>
                        {{ $one->details }}
                        @if($one->financial_type==2)
                        <a href="{{ route('withdrawal.show',[ 'withdrawal'=>json_decode($one->extra, true)['withdrawal_id'] ]) }}">申请记录编号 {{ json_decode($one->extra, true)['withdrawal_id'] }}</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>&nbsp;</td>
                    <td><strong>财务类型</strong></td>
                    <td>&nbsp;</td>
                    <td><strong>合计</strong></td>
                    <td id="total_amount"></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
        <div class="container-fluid">
            <div>
                <nav aria-label="page">
                    <strong>总数: {{ $records->total() }}</strong>  <br /> {{ $records->links() }}
                </nav>
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('balance.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $records->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $records->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $records->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $records->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
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
                mode: "range",
                enableTime: true,  // 启用时间选择
                dateFormat: "Y-m-d H:i", // 自定义日期格式
                locale: "zh"       // 使用中文语言
             });
             //balance search function
            $("#balance_search").click(function(){
            var financial_id = $("#financial_id").val();
            var customer = $("#customer").val();
            var financial_type = $("#financial_type").val();
            var date = $("#date").val();
            var data = {
                "financial_id": financial_id,
                "customer": customer,
                "financial_type": financial_type,
                "date" : date,
            };

            $.ajax({
                url : "/balance_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    var total_amount = 0;
                    $.each(response.balance_search,function(i,v){
                        if(v.direction == 1){
                            var amount = `<span style='color:green;'> +${v.amount} </span>`
                        } else if(v.direction == -1) {
                            var amount = `<span style='color:red;'> -${v.amount} </span>`
                        } else {
                            var amount = '方向错误'
                        };

                        total_amount += parseFloat(v.amount);

                        var type = response.types[v.financial_type];
                        if(v.financial_type == 2){
                            var withdrawal_id = JSON.parse(v.extra, true)['withdrawal_id']
                            var url = `{{ route('withdrawal.show',[ 'withdrawal'=> ':withdrawal_id' ]) }}`
                            url = url.replace(':withdrawal_id', withdrawal_id)
                            var withdrawal_link = `<a href="${url}">申请记录编号 ${withdrawal_id}</a>`
                        } else {
                            withdrawal_link = ''
                        }
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${type}</td>
                                <td>${v.phone}</td>
                                <td>${v.balance}</td>
                                <td>${amount}</td>
                                <td>${v.after_balance}</td>
                                <td>${v.created_at}</td>
                                <td>${v.details}
                                    ${withdrawal_link}
                                </td>
                            </tr>`;
                    })
                    $('#total_amount').html(total_amount);
                    $("#search_data").html(html);
                }
            });
        })
        })
    </script>
</body>
</html>
