<!DOCTYPE html>
<html lang="zh">
<head>
    <title>信息管理</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
        <!-- 引入 flatpickr 的 CSS 和 JS -->
    <link rel="stylesheet" href="/css/flatpickr.min.css">
    <script src="/js/flatpickr"></script>
    <script src="/js/zh.js"></script>
    <style>
        #app
        {
            padding-top: 1rem;
        }
        #app td
        {
            height: 20px;
            line-height: 20px;
        }
        .box1, .box2
        {
            display: inline-block;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">商城管理</li>
                <li class="breadcrumb-item active" aria-current="page">交易所管理</li>
            </ol>
        </nav>
        <nav class="row m-2">
            <div class="col-3">
                <label class="form-label">商品名称 :</label>
                <input type="text" name="goods_name" id="goods_name" class="form-control" />
            </div>
            <div class="col-3">
                <label class="form-label">是否结束：</label>
                <select name="is_over" id="is_over" class="form-select">
                    <option value="4">--请选择--</option>
                    <option value="0">未结束</option>
                    <option value="1">结束</option>
                </select>
            </div>
            <div class="col-3">
                <label class="form-label">显示 / 不显示</label>
                <select name="show" id="show" class="form-select">
                    <option value="4">--请选择--</option>
                    <option value="0">不显示</option>
                    <option value="1">显示</option>
                </select>
            </div>
            <div class="col-2">
                <label class="form-label">时间：</label>
                <input type="text" name="created_at" id="created_at" class="form-control" lang="zh-CN" />
            </div>

            <div class="col-1">
                <br />
                <button class="btn btn-success" id="product_search">查询</button>
            </div>
        </nav>
        <a href="{{ route('trade.create') }}" class="btn btn-primary">创建交易所商品</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>商品名称</th>
                    <th>天数</th>
                    <th>价格</th>
                    <!-- <th>缩略图</th> -->
                    <th>创建时间</th>
                    <th>是否结束</th>
                    <th>手续费百分比</th>
                    <th>卖出价格</th>
                    <th>显示 / 不显示</th>
                    <th style="width:260px;">操作</th>
                </tr>
            </thead>
            <tbody id="search_data">
            @foreach($trade_goods as $one)
                <tr>
                    <td>{{ $one->goods_name }}</td>
                    <td>{{ $one->days }}</td>
                    <td>{{ $one->price }}</td>
                    <!-- <td>{{ $one->images }}</td> -->
                    <td>{{ $one->created_at }}</td>
                    <td>
                        @if($one->is_over == 0)
                        <span style="color:blue;">未结束</span>
                        @elseif($one->is_over == 1)
                        <span style="color:red;">结束</span>
                        @endif
                    </td>
                    <td>{{$one->fee}}</td>
                    <td>{{$one->selling_price}}</td>
                    <td>
                        @if($one->show == 1)
                        <span style="color:blue;">显示</span>
                        @elseif($one->show == 0)
                        <span style="color:red;">不显示</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('trade.edit', ['trade'=>$one->id]) }}"  class="btn btn-warning mx-2">编辑</a>

                        <form action="{{ route('trade.destroy', ['trade'=>$one->id]) }}"
                         method="post"
                         class="d-inline-block" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <footer style="display:flex;">
        <div class="container-fluid">
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $trade_goods->count() }}</strong>条数据
                </aside>
                {{ $trade_goods->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('trade.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $trade_goods->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $trade_goods->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $trade_goods->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $trade_goods->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $trade_goods->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

        </footer>


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
    function del() {
        var msg = "您真的确定要删除吗？\n\n请确认！";
        if (confirm(msg)==true){
            return true;
        }else{
            return false;
        }
    }

    $(document).ready(function(){
        //datepicker
        flatpickr("#created_at",
                    {
                        mode: "range",
                        enableTime: true,  // 启用时间选择
                        dateFormat: "Y-m-d H:i", // 自定义日期格式
                        locale: "zh"       // 使用中文语言
        });
        $("#product_search").click(function(){
            var goods_name = $("#goods_name").val();
            var is_over = $("#is_over").val();
            var show = $("#show").val();
            var created_at = $("#created_at").val();
            var data = {
                "goods_name": goods_name,
                "is_over": is_over,
                "show" : show,
                "created_at" : created_at
            };

            $.ajax({
                url : "/product_search",
                dataType : "json",
                type: "POST",
                data: data,
                success: function(response){
                    var html = "";
                    console.log(response);
                    $.each(response.product_search,function(i,v){
                    html +=`<tr>
                                <td>${v.goods_name}</td>
                                <td>${v.days}</td>
                                <td>${v.price}</td>
                                <td>${v.created_at}</td>`;
                    if(v.is_over == 0){
                        html +=`<td>
                                    <span style="color:blue;">未结束</span>
                                </td>`;
                    }else{
                        html+=`<td>
                                    <span style="color:red;">结束</span>
                                </td>`;
                    }
                        html +=`<td>${v.fee}</td>
                                <td>${v.selling_price}</td>`;
                    if(v.show == 1){
                        html +=`<td>
                                    <span style="color:blue;">显示</span>
                                </td>`;
                    }else{
                        html+=`<td>
                                    <span style="color:red;">不显示</span>
                                </td>`;
                    }
                        html+=`<td>
                                    <a href="{{ url('trade/${v.id}/edit') }}"  class="btn btn-warning mx-2">编辑</a>

                                    <form action="{{ url('/trade/${v.id}') }}"
                                    method="post"
                                    class="d-inline-block" onsubmit="javascript:return del()">
                                        {{ csrf_field() }}
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">删除</button>
                                    </form>
                                </td>
                            </tr>`;
                    })
                    $("#search_data").html(html);
                }
            });
        })
    });
</script>
</body>
</html>
