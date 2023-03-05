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

        <nav aria-label="breadcrumb" style="margin-top: 1rem;">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item"><a href="{{ route('charge.index') }}">资产充值审核</a></li>
                <li class="breadcrumb-item active" aria-current="page">查看详情</li>
            </ol>
        </nav>

        <h3 class="text-center text-primary">资产充值详情</h3>

        <ul class="list-group list-group-flush" style="margin-top:1rem;">
            <li class="list-group-item"><strong>ID:</strong> {{ $one->id }}</li>
            <li class="list-group-item"><strong>客户:</strong> {{ $one->customer->phone }}</li>
            <li class="list-group-item"><strong>金额:</strong> {{ $one->amount }}</li>
            <li class="list-group-item"><strong>申请时间:</strong> {{ $one->created_at }}</li>
            <li class="list-group-item">
                <span style="float:left;">
                    <strong>状态:</strong>
                    @if($one->status==0)
                    <span style="color:blue;">待审核</span>
                    @elseif($one->status==1)
                    <span style="color:green;">已通过</span>
                    @elseif($one->status==2)
                    <span style="color:red;">拒绝</span>
                    @endif
                </span>
                @if($one->status==0)
                <form action="{{ route('charge.update', ['charge'=>$one->id]) }}" 
                         method="post"
                         style="margin-left: 4rem; float:left;" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('PATCH')
                        <button type="submit" class="btn btn-success">确定通过</button>
                </form>
                
                <a style="margin-left: 8rem; float:left;" href="{{ route('charge.edit', ['charge'=>$one->id]) }}"
                 class="btn btn-danger">拒绝</a>
                @endif
            </li>
            @if($one->status!=0)
            <li class="list-group-item">
                <strong>操作员:</strong> {{ $one->admin->username }}
            </li>
            @endif
            @if($one->status==2)
            <li class="list-group-item">
                <strong>拒绝理由:</strong> {{ $one->comment }}
            </li>
            @endif
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