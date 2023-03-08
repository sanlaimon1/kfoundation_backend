<!DOCTYPE html>
<html lang="zh">

<head>
    <title>系统角色管理</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app {
            padding-top: 1rem;
        }

        #app td {
            height: 20px;
            line-height: 20px;
        }
    </style>
    <link href="/static/adminlte/plugins/summernote/summernote.min.css" rel="stylesheet">
    <script src="/static/adminlte/plugins/summernote/summernote.min.js"></script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item"><a href="{{ route('inbox.index') }}">inbox list</a></li>
                <li class="breadcrumb-item active" aria-current="page">inbox edit</li>
            </ol>
        </nav>

        <div class="container">
            <form action="{{ route('inbox.update', $mail->id) }}" method="post">
                {{ csrf_field() }}
                @method('PATCH')
                <div class="form-group">
                    <label for="title">标题</label>
                    <input type="text" name="title" class="form-control" id="title" placeholder="标题" value="{{$mail->title}}">
                    @error('title')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mt-4">
                    <label for="content">内容</label>
                    <textarea name="content" id="content" class="form-control" rows="5">{{ html_entity_decode($mail->content) }}</textarea>
                    @error('content')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mt-4">
                    <input class="form-check-input" type="radio" name="is_top" id="enable" value="1" @if($mail->is_top == 1) checked @endif>
                    <label class="form-check-label" for="enable">
                        置顶
                    </label>

                    <input class="form-check-input" type="radio" name="is_top" id="disable" value="0" @if($mail->is_top == 0) checked @endif>
                    <label class="form-check-label" for="disable">
                        不置顶
                    </label>
                </div>
                <div class="form-group mt-4">
                    <label for="sort">排序</label>
                    <input type="text" class="form-control" name="sort" id="sort" placeholder="排序" value="{{$mail->sort}}">
                    @error('sort')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mt-4">
                    <label for="user_phone">用户手机号 若为空则是全部</label>
                    <input type="text" name="user_phone" class="form-control" id="user_phone" placeholder="用户手机号 若为空则是全部" value="{{$mail->user_phone}}">
                    @error('user_phone')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- <a class="btn btn-secondary mt-4" href="{{ route('inbox.index') }}" style="margin-top:1rem; margin-right:1rem;">返回</a> -->
                <button class="btn btn-secondary mt-4" action="action" onclick="window.history.go(-1); return false;" style="margin-top:1rem; margin-right:1rem;">返回</button>
                <button type="submit" class="btn btn-primary mt-4">编辑</button>
            </form>

        </div>


    </div>
    <script>
        $(document).ready(function() {
            $('#content').summernote();
        });
    </script>
</body>

</html>