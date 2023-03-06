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
            <li class="list-group-item"><strong>项目规模:</strong> {{ $project->project_scale }}</li>
            <li class="list-group-item"><strong>收益率:</strong> {{ $project->benefit_rate }}</li>
            <li class="list-group-item"><strong>项目期限:</strong> {{ $project->days }}天</li>
            <li class="list-group-item"><strong>起购金额:</strong> {{ $project->amount }}</li>
            <li class="list-group-item"><strong>限购次数:</strong> {{ $project->max_times }}</li>
            <li class="list-group-item"><strong>项目进度:</strong> {{ $project->fake_process }}%</li>
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