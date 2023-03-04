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
                    <textarea name="content" id="content" class="form-control" rows="5">{{$mail->content}}</textarea>
                    @error('content')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group mt-4">
                    <input class="form-check-input" type="radio" name="read" id="enable" value="1" @if($mail->read == 1) checked @endif>
                    <label class="form-check-label" for="enable">
                        已读
                    </label>

                    <input class="form-check-input" type="radio" name="read" id="disable" value="0" @if($mail->read == 0) checked @endif>
                    <label class="form-check-label" for="disable">
                        未读 
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

                <button type="submit" class="btn btn-primary mt-4">修改</button>
            </form>

        </div>


    </div>

</body>

</html>