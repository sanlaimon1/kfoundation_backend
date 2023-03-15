<!DOCTYPE html>
<html lang="zh">
<head>
    <title>查看订单状态</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    
</head>

<body>
    <div class="container-fluid">

        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item"><a href="{{ route('project.index') }}">项目列表 </a></li>
                <li class="breadcrumb-item active" aria-current="page">查看详情</li>
            </ol>
        </nav>
        <br />
        <h3 class="text-center text-primary">项目详情</h3>

        <ul class="list-group list-group-flush" style="margin-top:1rem;">
            <li class="list-group-item"><strong>ID:</strong> {{ $project->id }}</li>
            <li class="list-group-item"><strong>项目名称:</strong> {{ $project->project_name }}</li>
            <li class="list-group-item"><strong>项目分类:</strong> {{ $project->projectcate->cate_name }}</li>

            <li class="list-group-item"><strong>担保机构:</strong> {{ $project->guarantee }}</li>
            <li class="list-group-item"><strong>投资零风险:</strong> {{ $project->risk }}</li>
            <li class="list-group-item"><strong>资金用途:</strong> {{ $project->usage }}</li>
            <li class="list-group-item"><strong>前台展示:</strong> {{ $project->frontend }}</li>
            <li class="list-group-item"><strong>返利模式:</strong> 
                @foreach($return_modes as $key => $return_mode)
                @if($project->return_mode == $key)
                {{ $return_mode }}
                @endif
                @endforeach
            </li>

            <li class="list-group-item"><strong>起购金额:</strong> {{ $project->amount }}</li>

            <li class="list-group-item"><strong>赠送积分:</strong> 
                @if($project->is_given == 0)
                否
                @else
                是
                @endif
            </li>
            <li class="list-group-item"><strong>团购收益率:</strong>{{ $project->team_rate }}</li>
            <li class="list-group-item"><strong>拼赞收益率:</strong> {{ $project->like_rate }}</li>
            <li class="list-group-item"><strong>项目规模:</strong> {{ $project->project_scale }}</li>            
            <li class="list-group-item"><strong>收益率:</strong> {{ $project->benefit_rate }}</li>
            <li class="list-group-item"><strong>项目进度:</strong> {{ $project->fake_process }}%</li>
            <li class="list-group-item"><strong>项目期限:</strong> 
                @if($project->return_mode == 3)
                    <span class="mt-5 pt-5">{{$project->weeks}} (周)</span>
                @elseif($project->return_mode == 4)
                    <span class="mt-5 pt-5">{{$project->months}} (月)  </span>
                @elseif($project->return_mode == 1)
                    <span class="mt-5 pt-5">{{$project->days}} * 24 （小时）</span>
                @else
                    <span class="mt-5 pt-5">{{$project->days}} (天)</span>
                @endif
            </li>
            <li class="list-group-item"><strong>最小投资金额:</strong> {{ $project->min_invest }}</li>
            <li class="list-group-item"><strong>最大投资金额:</strong> {{ $project->max_invest }}</li>
            <li class="list-group-item"><strong>限购次数:</strong> {{ $project->max_times }}</li>
            
            <li class="list-group-item"><strong>项目描述:</strong> {{ $project->desc }}</li>
            <li class="list-group-item"><strong>是否首页显示:</strong> 
                @if($project->is_homepage == 0)
                否
                @else
                是
                @endif
            </li>
            <li class="list-group-item"><strong>是否热门推荐:</strong> 
                @if($project->is_recommend == 0)
                否
                @else
                是
                @endif
            </li>
            <li class="list-group-item"><strong>会员等级:</strong> {{ $project->level->level_name }}</li>
            <li class="list-group-item"><strong>项目封面图片:</strong> 
                <img src="{{$project->litpic}}" alt="" class="img-fluid w-25 px-5 mx-5"> 
            </li>
            @php
                $details = html_entity_decode($project->details)
            @endphp
            <li class="list-group-item"><strong>项目详情:</strong> {!! $details !!}</li>

            <li class="list-group-item"><strong>发布时间:</strong> {{ $project->created_at }}</li>

        </ul>

    </div>
    <script>
    function del() { 
        var msg = "您真的确定要通过吗？\n\n请确认！"; 
        if (confirm(msg)==true){ 
            return true; 
        }else{ 
            return false; 
        }
    }
    </script>
</body>
</html>