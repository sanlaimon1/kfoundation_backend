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
        #app .logo
        {
            width: 30px;
        }
        #app td
        {
            height: 30px;
            line-height: 30px;
        }
        .box1, .box2
        {
            display: inline-block;
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

    <!-- include summernote css/js -->
    <link href="/static/adminlte/plugins/summernote/summernote.min.css" rel="stylesheet">
    <script src="/static/adminlte/plugins/summernote/summernote.min.js"></script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">系统管理</li>
                <li class="breadcrumb-item active" aria-current="page">支付设置</li>
            </ol>
        </nav>

        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>排序</th>
                    <th>类型</th>
                    <th>名称</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $one)
                <tr>
                    <td>{{ $one->sort }}</td>
                    <td>{{ config('data.payment_ways')[ $one->ptype ] }}</td>
                    <td>
                        <img class="logo" src="{{ $one->logo }}" />
                        {{ $one->payment_name }}
                    </td>
                    <td>
                        @if( $one->show==1 )
                            <span style="color:green;">显示</span>
                        @else
                            <span style="color:red;">隐藏</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('payment.edit', ['payment'=>$one->pid]) }}" class="btn btn-warning">编辑</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <footer style="display:flex;">
        <div class="container-fluid">
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $payments->count() }}</strong>条数据
                </aside>
                {{ $payments->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('payment.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="10" {{ $payments->perPage() == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $payments->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $payments->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $payments->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $payments->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

        </footer>


    </div>

</body>
</html>
