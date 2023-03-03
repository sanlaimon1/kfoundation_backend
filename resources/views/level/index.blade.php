<!DOCTYPE html>
<html lang="zh">
<head>
    <title>会员等级</title>
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
        #app td, #app th
        {
            height: 30px;
            line-height: 30px;
            font-size: 14px;
            padding: 0;
        }
        #app td img
        {
            height: 18px;
            width: 18px;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">会员中心</li>
                <li class="breadcrumb-item active" aria-current="page">会员等级</li>
            </ol>
        </nav>
        <a href="{{ route('level.create') }}" class="btn btn-primary">创建会员等级</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>等级名称</th>
                    <th>累计充值金额</th>
                    <th>加息利率(%)</th>
                    <th>个人充值奖励(％)</th>
                    <th>一级奖励(％)</th>
                    <th>二级奖励(％)</th>
                    <th>三级奖励(％)</th>
                    <th>挖矿最低虚拟币</th>
                    <th>挖矿最高虚拟币</th>
                    <th style="width:140px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($levels as $one)
                <tr>
                    <td>{{ $one->level_id }}</td>
                    <td>{{ $one->level_name }}</td>
                    <td>
                        {{ $one->accumulative_amount }}
                    </td>
                    <td>
                        {{ $one->interest }}
                    </td>
                    <td>
                        {{ $one->personal_charge }}
                    </td>
                    <td>
                        {{ $one->level1_award }}
                    </td>
                    <td>
                        {{ $one->level2_award }}
                    </td>
                    <td>
                        {{ $one->level3_award }}
                    </td>
                    <td>
                        {{ $one->min_coin }}
                    </td>
                    <td>
                        {{ $one->max_coin }}
                    </td>
                    <td>
                        
                        <a href="{{ route('level.edit', ['level'=>$one->level_id]) }}" class="btn btn-warning">编辑</a>
                        
                        <form action="{{ route('level.destroy', ['level'=>$one->level_id]) }}" 
                         method="post"
                         style="float:right;" onsubmit="javascript:return del()">
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
            <aside style="line-height: 37px; margin-right: 2rem;">
                共计<strong>{{ $levels->count() }}</strong>条数据
            </aside>
            {{ $levels->links() }}
        </footer>
        
        
    </div>
    <script>
    function del() { 
        var msg = "您真的确定要删除吗？\n\n请确认！"; 
        if (confirm(msg)==true){ 
            return true; 
        }else{ 
            return false; 
        }
    }
    </script>
</body>
</html>