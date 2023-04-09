<!DOCTYPE html>
<html lang="zh">
<head>
    <title>编辑绑定项目</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/js/bootstrap.bundle.min.js"></script>
    <!-- include summernote css/js -->
    <link href="/css/bootstrap-v3.4.1.min.css" rel="stylesheet">
    <script src="/js/jquery-v3.5.1.min.js"></script>
    <script src="/js/bootstrap-v3.4.1.min.js"></script>
    <link href="/css/summernote.min.css" rel="stylesheet">
    <script src="/js/summernote.min.js"></script>
    <script src="/js/summernote-zh-CN.js"></script>
    <!-- include summernote css/js -->
    <link href="/static/adminlte/plugins/summernote/summernote.min.css" rel="stylesheet">
    <script src="/static/adminlte/plugins/summernote/summernote.min.js"></script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">项目管理</li>
                <li class="breadcrumb-item"><a href="{{ route('project.index') }}">项目列表 </a></li>
                <li class="breadcrumb-item active" aria-current="page">编辑绑定项目</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{route('bind.update',['id'=>$project->id])}}" method="post" enctype="multipart/form-data">
            @csrf
            <section class="row frame mt-5 mx-5 px-5">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="project_name" class="form-label">项目名称</label>
                        {{$project->project_name}}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="bindid" class="form-label">请选择要绑定的产品</label>
                        @error('bindid')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="bindid" name="bindid"  class="form-select" >
                            @foreach( $select_array as $key=>$one )
                            <option value="{{ $key }}" {{ $key == $project->bind_projectid ? 'selected' : '' }}> {{ $one }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div align="center">
                    <button class="btn btn-secondary  w-25" action="action" onclick="window.history.go(-1); return false;" style="margin-right:1rem;">返回</button>
                    <button type="submit" class="btn btn-primary w-25 " >绑定</button>
                </div>
            </section>

        </form>

    </div>
    @include('loading')
    @include('modal')
    <script>
        $(document).ready(function() {
            $('#detail').summernote({
                lang: 'zh-CN'
            });
        });
    </script>
</body>
</html>
