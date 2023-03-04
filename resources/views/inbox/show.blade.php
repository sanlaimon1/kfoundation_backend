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
    <style>
        #app td, .card-header
        {
            text-align: left;
        }
    </style>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">信息管理</li>
                <li class="breadcrumb-item"><a href="{{ route('inbox.index') }}">站内信列表</a></li>
                <li class="breadcrumb-item active" aria-current="page">显示</li>
            </ol>
        </nav>
        <br />

        <div class="card text-center">
            <div class="card-header">
                显示站内信 <a href="{{ route('inbox.index') }}" class="btn btn-primary">返回</a>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <td>标题</td>
                            <td>{{ $mail->title }}</td>
                        </tr>
                        <tr>
                            <td>状态</td>
                            <td>@if($mail->is_top == 0) 未置顶 @else 已置顶  @endif</td>
                        </tr>
                        <tr>
                            <td>发布时间</td>
                            <td>{{ $mail->created_at }}</td>
                        </tr>
                        <tr>
                            <td>排序</td>
                            <td>{{ $mail->sort }}</td>
                        </tr>
                        <tr>
                            <td>用户手机号</td>
                            <td>@if( $mail->user_phone==null ) 所有人 @else {{ $mail->user_phone }} @endif</td>
                        </tr>
                        <tr>
                            <td>内容</td>
                            <td>
                                <?= html_entity_decode($mail->content) ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <script src="/static/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/dist/js/adminlte.min.js?v=3.2.0"></script>

</body>

</html>