<!DOCTYPE html>
<html lang="zh">
<head>
    <title>参数设置</title>
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
                <li class="breadcrumb-item active" aria-current="page">创建项目</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{route('project.store')}}" method="post" enctype="multipart/form-data">
            @csrf
            <section class="row frame mt-5 mx-5 px-5">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_name" class="form-label">项目名称</label>
                        @error('project_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="project_name" name="project_name" placeholder="项目名称" value="{{old('project_name')}}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="project_name_en" class="form-label">Project Name</label>
                        @error('project_name_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="project_name_en" name="project_name_en" placeholder="项目名称" value="{{old('project_name_en')}}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cate_name" class="form-label">项目分类</label>
                        @error('cate_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                            <select id="cate_name" name="cate_name"  class="form-select" >
                                @foreach( $types as $key=>$one_cate )
                                <option value="{{ $key }}" > {{ $one_cate }} </option>
                                @endforeach
                            </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="lang" class="form-label">语言</label>
                        @error('lang')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="lang" name="lang" class="form-select form-select-lg" >
                            <option value="cn">简体中文</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="guarantee" class="form-label">担保机构（描述）</label>
                        @error('guarantee')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="guarantee" name="guarantee" placeholder="" value="{{old('guarantee')}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="guarantee_en" class="form-label">Guarantee Agency (Description)</label>
                        @error('guarantee_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="guarantee_en" name="guarantee_en" placeholder="" value="{{old('guarantee_en')}}"  >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="risk" class="form-label">投资零风险（描述）</label>
                        @error('risk')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="risk" name="risk" placeholder="" value="{{old('risk')}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="risk_en" class="form-label">Investing with zero risk (description)</label>
                        @error('risk_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="risk_en" name="risk_en" placeholder="" value="{{old('risk_en')}}" >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="usage" class="form-label">资金用途</label>
                        @error('usage')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="usage" name="usage" placeholder="" value="{{old('usage')}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="usage_en" class="form-label">Use of funds</label>
                        @error('usage_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="usage_en" name="usage_en" placeholder="" value="{{old('usage_en')}}" >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">项目描述</label>
                        @error('desc')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="desc" name="desc" placeholder="" value="{{old('desc')}}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="desc_en" class="form-label">project description</label>
                        @error('desc_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="desc_en" name="desc_en" placeholder="" value="{{old('desc_en')}}" >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="return_mode" class="form-label">返利模式</label>
                        @error('return_mode')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="return_mode" name="return_mode"  class="form-select form-select-lg return_mode" >
                            @foreach( $return_modes as $key=>$return_mode)
                            <option value="{{ $key }}" <?=($key==2) ? 'selected' : '' ?>> {{ $return_mode }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="amount" class="form-label">购买金额</label>
                        @error('amount')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="amount" name="amount" placeholder="" value="{{old('amount')}}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="is_given" class="form-label">赠送积分</label>
                        @error('is_given')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_given" name="is_given"  class="form-select form-select-lg" >
                            <option value="0">否</option>
                            <option value="1">是</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="team_rate" class="form-label">团购收益率（％）</label>
                        @error('team_rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="team_rate" name="team_rate" placeholder="" value="{{old('team_rate')}}" >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="like_rate" class="form-label">拼赞收益率（％）</label>
                        @error('like_rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="like_rate" name="like_rate" placeholder="" value="{{old('like_rate')}}" >
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="project_scale" class="form-label">项目规模</label>
                        @error('project_scale')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="project_scale" name="project_scale" placeholder="" value="{{old('project_scale')}}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="benefit_rate" class="form-label">收益率（％）</label>
                        @error('benefit_rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="benefit_rate" name="benefit_rate" placeholder="" value="{{old('benefit_rate')}}" >
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="fake_process" class="form-label">模拟进度</label>
                        @error('fake_process')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="fake_process" name="fake_process" placeholder="" value="{{old('fake_process')}}" >
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="days" class="form-label days_label">周期 (天)</label>
                        @error('days')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control days" id="days" name="days" placeholder="" value="{{old('days')}}" >
                    </div>
                    <div class="col-md-3">
                        <label for="min_invest" class="form-label">最小投资金额</label>
                        @error('min_invest')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="min_invest" name="min_invest" placeholder="" value="{{old('min_invest')}}" >
                    </div>
                    <div class="col-md-3">
                        <label for="max_invest" class="form-label">最大投资金额</label>
                        @error('max_invest')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="max_invest" name="max_invest" placeholder="" value="{{old('max_invest')}}">
                    </div>
                    <div class="col-md-3">
                        <label for="max_time" class="form-label">最大投资次数</label>
                        @error('max_time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="max_time" name="max_time" placeholder="" value="{{old('max_time')}}">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="is_homepage" class="form-label">是否首页显示</label>
                        @error('is_homepage')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_homepage" name="is_homepage"  class="form-select form-select-lg" >
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="is_recommend" class="form-label">是否热门推荐</label>
                        @error('is_recommend')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_recommend" name="is_recommend"  class="form-select form-select-lg" >
                            <option value="1">是</option>
                            <option value="0">否</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="level_id" class="form-label">会员等级</label>
                        @error('level_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="level_id" name="level_id"  class="form-select form-select-lg" >
                            @foreach( $levels as $level )
                            <option value="{{ $level->level_id }}" > {{ $level->level_name }} </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">项目状态</label>
                        @error('status')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="status" name="status"  class="form-select form-select-lg" >
                            <option value="1">上架</option>
                            <option value="0">下架</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="number" class="form-control" id="sort" name="sort" placeholder="排序" value="{{old('sort')}}" >
                    </div>
                    <div class="col-md-4">
                        <label for="lang" class="form-label">语言</label>
                        @error('lang')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="lang" name="lang" class="form-select form-select-lg" >
                            <option value="cn">简体中文</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="litpic" class="form-label">项目封面图片</label>
                        @error('litpic')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="file" class="form-control" id="litpic" name="litpic" placeholder="" value="{{old('litpic')}}" >
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="detail" class="form-label">项目详情</label>
                        @error('detail')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <textarea type="text" class="form-control" id="detail" name="detail" placeholder="" >{{old('detail')}}</textarea>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="detail" class="form-label">project details</label>
                        @error('detail_en')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <textarea type="text" class="form-control" id="detail_en" name="detail_en" placeholder="" >{{old('detail_en')}}</textarea>
                    </div>
                </div>
               <button type="submit" class="btn btn-primary w-25  col-4 offset-4" >修改</button>
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
            $('#detail_en').summernote({
                lang: 'zh-CN'
            });

            $('.return_mode').change(function(){
                $('.days').val('');
                var return_mode = $(this).val();
                if(return_mode == 1){
                    $('.days_label').text('周期 (天)')
                } else if(return_mode == 3) {
                    $('.days_label').text('周期 (周)')
                }else if(return_mode == 4) {
                    $('.days_label').text('周期 (月)')
                } else {
                    $('.days_label').text('周期 (天)')
                }
            })

            $(".days").keyup(function(){
                var days = $(this).val();
                var return_mode  = $(".return_mode").val();
                if(return_mode == 1 && days > 1){
                    alert("按小时计算，天数最多1天");
                    $('.days').val(1);
                }
            })
        });
    </script>
</body>
</html>
