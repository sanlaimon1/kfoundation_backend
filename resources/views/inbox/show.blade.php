<?php

use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">

<head>
    <title>站内信列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item"><a href="{{ route('inbox.index') }}">inbox list</a></li>
                <li class="breadcrumb-item active" aria-current="page">inbox show</li>
            </ol>
        </nav>
        <br />

        <div class="card text-center">
            <div class="card-header">
                Inbox show
            </div>
            <div class="card-body">
                <h5 class="card-title">标题 : {{ $mail->title}}</h5>
                <p class="card-text">内容 : {{ $mail->content}}</p>
                <p class="card-text">Status : @if($mail->read == 0) 未读 @else 已读  @endif</p>
                <p class="card-text">创建时间 : {{ $mail->created_at}}</p>
                <p class="card-text">排序 : {{ $mail->sort}}</p>
                <p class="card-text">用户手机号 若为空则是全部 :  @if( $mail->user_phone==null ) 所有人 @else {{ $mail->user_phone }} @endif</p>
                <a href="{{ route('inbox.index') }}" class="btn btn-primary">Go back</a>
            </div>
        </div>

    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>

</body>

</html>