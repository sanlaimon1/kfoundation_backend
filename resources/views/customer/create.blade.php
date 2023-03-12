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
                <li class="breadcrumb-item"><a href="{{ route('customer.index') }}">{{$title}}</a></li>
                <li class="breadcrumb-item active" aria-current="page">创建客户</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        
        <form action="{{route('customer.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <section class="row frame mt-5 mx-5 px-5">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">用户手机号</label>
                        @error('phone')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="phone" name="phone" value="">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="realname" class="form-label">姓名</label>
                        @error('realname')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="realname" name="realname" value="">
                    </div>
                </div>

                {{-- <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="integration" class="form-label">积分</label>
                        @error('integration')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="integration" name="integration"  >
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="balance" class="form-label">余额</label>
                        @error('balance')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="balance" name="balance" placeholder="" >
                    </div>
                
                    <div class="col-md-4 mb-3">
                        <label for="asset" class="form-label">资产</label>
                        @error('asset')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="asset" name="asset"  min="0" max="10" step="0.25" placeholder="0.00" >
                    </div>
                    
                </div> --}}

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="is_allowed_code" class="form-label">允许邀请码</label>
                        @error('is_allowed_code')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_allowed_code" name="is_allowed_code"  class="form-select" >
                            <option value="1">允许</option>
                            <option value="0">不允许</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="identity" class="form-label">身份</label>
                        @error('identity')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="identity" name="identity"  class="form-select" >
                            @foreach ($customer_identity as $key => $identity)
                                <option value="{{$key}}">{{$identity}}</option>
                            @endforeach
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
                            <option value="0">未认证</option>
                            <option value="1">已认证</option>
                        </select>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="level_id" class="form-label">会员等级</label>
                        @error('level_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        
                        <select id="level_id" name="level_id"  class="form-select" >
                            @foreach( $levels as $level )
                            <option value="{{ $level->level_id }}" > {{ $level->level_name }} </option>
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
                            <option value="{{ $teamlevel->tid }}" > {{ $teamlevel->level_name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="yuebao_balance" class="form-label">余额宝余额</label>
                        @error('yuebao_balance')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="yuebao_balance" name="yuebao_balance">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="salt" class="form-label">盐</label>
                        @error('salt')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="salt" name="salt" >
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tick" class="form-label">密码尝试次数</label>
                        @error('tick')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                       <input type="text" class="form-control" id="tick" name="tick">
                    </div>
                </div> --}}

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password1" class="form-label">密码</label>
                        @error('password1')
                            <div class="alert alert-danger">密码必须包含大小写字母和数字的组合</div>
                        @enderror
                        <input type="password" class="form-control" id="password1" name="password1" >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password1_confirmation" class="form-label">确认登录密码</label>
                        {{-- @error('password1_confirmation')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror --}}
                        <input type="password" class="form-control" id="password1_confirmation" name="password1_confirmation" >
                    </div>

                   
                    {{-- <div class="col-md-4 mb-3">
                        <label for="platform_coin" class="form-label">平台币</label>
                        @error('platform_coin')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="platform_coin" name="platform_coin" >
                    </div> --}}
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password2" class="form-label">取款密码</label>
                        @error('password2')
                            <div class="alert alert-danger">密码必须包含大小写字母和数字的组合</div>
                        @enderror
                        <input type="password" class="form-control" id="password2" name="password2">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password2_confirmation" class="form-label">确认提现密码</label>
                        {{-- @error('password2_confirmation')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror --}}
                        <input type="password" class="form-control" id="password2_confirmation" name="password2_confirmation" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="idcard_front" class="form-label">身份证正面</label>
                        @error('idcard_front')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="file" class="form-control" id="idcard_front" name="idcard_front" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="idcard_back" class="form-label">身份证背面</label>
                        @error('idcard_back')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="file" class="form-control" id="idcard_back" name="idcard_back" placeholder="" >
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-25 col-4 offset-4" >创建</button>

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