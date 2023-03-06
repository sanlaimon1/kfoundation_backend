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
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item"><a href="{{ route('project.index') }}">项目列表 </a></li>
                <li class="breadcrumb-item active" aria-current="page">编辑项目</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        
        <form action="" method="post">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">项目名称</label>
                        @error('project_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="project_name" name="project_name" placeholder="项目名称" value="">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="ptype" class="form-label">项目分类</label>
                        @error('cate_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="cate_name" name="cate_name"  class="form-select" >
                            @foreach( $types as $key=>$one_cate )
                            <option value="{{ $key }}" > {{ $one_cate }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">Guarantee</label>
                        @error('guarantee')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="guarantee" name="guarantee" placeholder="" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">Risk</label>
                        @error('risk')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="risk" name="risk" placeholder="" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">Guarantee</label>
                        @error('guarantee')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="guarantee" name="guarantee" placeholder="" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">Risk</label>
                        @error('risk')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="risk" name="risk" placeholder="" >
                    </div>
                </div>
            </section>

            <button type="submit" class="btn btn-primary" style="margin-top:1rem; float:right;">修改</button>
        </form>
        
    </div>
    @include('loading')
    @include('modal')

</body>
</html>