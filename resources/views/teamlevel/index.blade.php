<!DOCTYPE html>
<html lang="zh">
<head>
    <title>团队等级</title>
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
            height: 54px;
            line-height: 54px;
            font-size: 14px;
            padding: 0;
        }
        #app td img
        {
            height: 50px;
            width: 50px;
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
                <li class="breadcrumb-item">会员中心</li>
                <li class="breadcrumb-item active" aria-current="page">团队等级</li>
            </ol>
        </nav>
        <a href="{{ route('teamlevel.create') }}" class="btn btn-primary">创建团队等级</a>
        <table class="table table-bordered table-striped text-center">
            <thead>
                <tr>
                    <th>编号</th>
                    <th>等级名称</th>
                    <th>等级图标</th>
                    <th>直推会员人数</th>
                    <th>直推团长人数</th>
                    <th>团队累计充值</th>
                    <th>团队奖(%)</th>
                    <th>升级是否赠送</th>
                    <th>奖励金额</th>
                    <th>状态</th>
                    <th style="width:140px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teamlevels as $one)
                <tr>
                    <td>{{ $one->tid }}</td>
                    <td>{{ $one->level_name }}</td>
                    <td>
                        <img src="{{ $one->icon }}" />
                    </td>
                    <td>
                        {{ $one->spread_members_num }}
                    </td>
                    <td>
                        {{ $one->spread_leaders_num }}
                    </td>
                    <td>
                        {{ $one->accumulative_amount }}
                    </td>
                    <td>
                        {{ $one->team_award }}%
                    </td>
                    <td>
                        @if( $one->is_given==1 )
                        <span style="color:green;">赠送</span>
                        @else
                        <span style="color:red;">不赠送</span>
                        @endif
                    </td>
                    <td>
                        {{ $one->award_amount }}
                    </td>
                    <td>
                        @if( $one->status==1 )
                        <span style="color:green;">正常</span>
                        @else
                        <span style="color:red;">非正常</span>
                        @endif
                    </td>
                    <td>

                        <a href="{{ route('teamlevel.edit', ['teamlevel'=>$one->tid]) }}" class="btn btn-warning">编辑</a>

                        <form action="{{ route('teamlevel.destroy', ['teamlevel'=>$one->tid]) }}"
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
        <div class="container-fluid">
            <div class="box1 p-2">
                <aside style="line-height: 37px; margin-right: 2rem;">
                    共计<strong>{{ $teamlevels->count() }}</strong>条数据
                </aside>
                {{ $teamlevels->links() }}
            </div>
            <div class="box2 p-2">
            <form method="get" action="{{ route('teamlevel.index') }}">
                <label for="perPage">每页显示：</label>
                <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                    <option value="20" {{ $teamlevels->perPage() == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $teamlevels->perPage() == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $teamlevels->perPage() == 100 ? 'selected' : '' }}>100</option>
                    <option value="200" {{ $teamlevels->perPage() == 200 ? 'selected' : '' }}>200</option>
                </select>
            </form>
            </div>
        </div>

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
