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
</head>

<body>
    <div class="container-fluid">
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
                <input type="text" name="id" id="action" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">用户名：</label>
                <input type="text" name="action" id="action" class="form-control" />
            </div>

            <div class="col-3">
                <label class="form-label">财务类型：</label>
                <select name="financial_type" id="financial_type"  class="form-select">
                    <option>--请选择--</option>
                    @foreach($types as $type_val=>$one_type)
                    <option value="{{ $type_val }}">{{ $one_type }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="date" name="date" id="date" class="form-control" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="log_search">查询</button>
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
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tfoot>
        </table>
        <nav aria-label="page">
              <strong>总数: {{ $records->total() }}</strong>  <br /> {{ $records->links() }}
        </nav>
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
            $("#log_search").click(function(){
            var adminid = $("#adminid").val();
            var action = $("#action").val();
            var date = $("#date").val();
            var data = {
                "adminid": adminid,
                "action": action,
                "date" : date,
            };

            $.ajax({
                url : "/log_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    console.log(response);
                    $.each(response.search_logs,function(i,v){
                        console.log(v);
                    html +=`<tr>
                                <td>${v.id}</td>
                                <td>${v.action}</td>
                                <td>${v.route}</td>
                                <td>${v.created_at}</td>
                                <td>
                                    <a class="btn btn-primary" href="log/${v.id}">查看请求数据</a>
                                </td>
                            </tr>`;
                    })
                    $("#search_data").html(html);
                }
            });
        })
        })
    </script>
</body>
</html>