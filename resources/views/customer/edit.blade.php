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
    <!-- include summernote css/js -->
    <link href="/static/adminlte/plugins/summernote/summernote.min.css" rel="stylesheet">
    <script src="/static/adminlte/plugins/summernote/summernote.min.js"></script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">用户中心</li>
                <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">会员列表</a></li>
                <li class="breadcrumb-item active" aria-current="page">编辑客户</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        
        <form action="{{route('customer.update',$customer->id)}}" method="post" enctype="multipart/form-data">
            @csrf
            @method('put')
            <section class="row frame mt-5 mx-5 px-5">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">用户手机号</label>
                        @error('phone')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="phone" name="phone" readonly value="{{$customer->phone}}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="realname" class="form-label">姓名</label>
                        @error('realname')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="realname" name="realname" value="{{$customer->realname}}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="is_allowed_code" class="form-label">允许邀请码</label>
                        @error('is_allowed_code')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_allowed_code" name="is_allowed_code"  class="form-select" >
                            <option value="0" {{ $customer->is_allowed_code == 0? 'selected' : ''}}>不允许</option>
                            <option value="1"{{ $customer->is_allowed_code == 1? 'selected' : ''}}>允许</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="identity" class="form-label">身份</label>
                        @error('identity')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="identity" name="identity"  class="form-select" >
                            <option value="0" {{ $customer->identity == 0? 'selected' : ''}}>真实账号</option>
                            <option value="1" {{ $customer->identity == 1? 'selected' : ''}}>一级内部账号</option>
                            <option value="2" {{ $customer->identity == 2? 'selected' : ''}}>二级内部账号</option>
                        </select>
                    </div>
                </div>

                <div class="row">                    
                    <div class="col-md-4 mb-3">
                        <label for="is_sure" class="form-label">认证</label>
                        @error('is_sure')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_sure" name="is_sure"  class="form-select" >
                            <option value="0" {{ $customer->is_sure == 0? 'selected' : ''}}>未认证</option>
                            <option value="1" {{ $customer->is_sure == 1? 'selected' : ''}}>已认证</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="level_id" class="form-label">会员等级</label>
                        @error('level_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        
                        <select id="level_id" name="level_id"  class="form-select" >
                            @foreach( $levels as $level )
                            <option value="{{ $level->level_id }}" {{ $customer->level_id == $level->level_id? 'selected' : ''}}> {{ $level->level_name }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="team_id" class="form-label">团队等级</label>
                        @error('team_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        
                        <select id="team_id" name="team_id"  class="form-select" >
                            @foreach( $teamlevels as $teamlevel )
                            <option value="{{ $teamlevel->tid }}" {{ $customer->team_id == $teamlevel->tid? 'selected' : ''}}> {{ $teamlevel->level_name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

              

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idcard_front" class="form-label">身份证正面</label>
                        @error('idcard_front')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="file" class="form-control" id="idcard_front" name="idcard_front" >
                        <input type="hidden" name="old_idcard_front" value="{{$customer->idcard_front}}">
                        <img src="{{$customer->idcard_front}}" alt="" class="img-fluid w-25 py-5">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idcard_back" class="form-label">身份证背面</label>
                        @error('idcard_back')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="file" class="form-control" id="idcard_back" name="idcard_back">
                        <input type="hidden" name="old_idcard_back" value="{{$customer->idcard_back}}">
                        <img src="{{$customer->idcard_back}}" alt="" class="img-fluid w-25 py-5">
                    </div>
                </div>
                
                <div align="center">
                    <!-- <a class="btn btn-secondary w-25" href="{{ route('project.index') }}" style="margin-right:1rem;">返回</a> -->
                    <button class="btn btn-secondary  w-25" action="action" onclick="window.history.go(-1); return false;" style="margin-right:1rem;">返回</button>
                    <button type="submit" class="btn btn-primary w-25 " >编辑</button>
                </div>
            </section>

        </form>
        
    </div>
    @include('loading')
    @include('modal')
    <script>
        $(document).ready(function() {
            $('#detail').summernote();
        });
    </script>
</body>
</html>