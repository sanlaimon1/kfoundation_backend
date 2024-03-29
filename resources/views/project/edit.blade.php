<!DOCTYPE html>
<html lang="zh">
<head>
    <title>编辑项目</title>
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
                <li class="breadcrumb-item active" aria-current="page">编辑项目</li>
            </ol>
        </nav>

        @if(session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif

        <form action="{{route('project.update',$project->id)}}" method="post" enctype="multipart/form-data">
            @csrf
            @method('put')
            <section class="row frame mt-5 mx-5 px-5">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="project_name" class="form-label">项目名称</label>
                        @error('project_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="project_name" name="project_name" value="{{$project->project_name}}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="cate_name" class="form-label">项目分类</label>
                        @error('cate_name')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="cate_name" name="cate_name"  class="form-select" >
                            @foreach( $types as $key=>$one_cate )
                            <option value="{{ $key }}" {{ $key == $project->cid ? 'selected' : '' }}> {{ $one_cate }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="guarantee" class="form-label">担保机构（描述）</label>
                        @error('guarantee')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="guarantee" name="guarantee" value="{{$project->guarantee}}" >
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="risk" class="form-label">投资零风险（描述）</label>
                        @error('risk')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="risk" name="risk" value="{{$project->risk}}" >
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="usage" class="form-label">资金用途</label>
                        @error('usage')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="usage" name="usage" value="{{$project->usage}}" >
                    </div>

                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="return_mode" class="form-label">返利模式</label>
                        @error('return_mode')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <br>
                        <!-- <select id="return_mode" name="return_mode"  class="form-select" >
                            @foreach( $return_modes as $key=>$return_mode)
                            <option value="{{ $key }}" {{$key == $project->return_mode ? 'selected' : '' }}> {{ $return_mode }} </option>
                            @endforeach
                        </select> -->
                        <span class="mt-5 pt-5">{{ $return_modes[$project->return_mode] }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">购买金额</label>
                        @error('amount')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="amount" name="amount" value="{{$project->amount}}" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="is_given" class="form-label">赠送积分</label>
                        @error('is_given')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_given" name="is_given"  class="form-select" >
                            <option value="0" {{ $project->is_given == 0 ? 'selected' : ''}}>否</option>
                            <option value="1" {{ $project->is_given == 1 ? 'selected' : ''}}>是</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="team_rate" class="form-label">团购收益率（％）</label>
                        @error('team_rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="team_rate" name="team_rate" value="{{$project->team_rate}}" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="like_rate" class="form-label">拼赞收益率（％）</label>
                        @error('like_rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="like_rate" name="like_rate" value="{{$project->like_rate}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="project_scale" class="form-label">项目规模</label>
                        @error('project_scale')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="project_scale" name="project_scale" value="{{$project->project_scale}}" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="benefit_rate" class="form-label">收益率（％）</label>
                        @error('benefit_rate')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="benefit_rate" name="benefit_rate" value="{{$project->benefit_rate}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="fake_process" class="form-label">模拟进度</label>
                        @error('fake_process')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="fake_process" name="fake_process" value="{{$project->fake_process}}" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="days" class="form-label">
                            @if($project->return_mode == 3)
                                <span class="mt-5 pt-5">周期 (周)</span>
                            @elseif($project->return_mode == 4)
                                <span class="mt-5 pt-5">周期 (月)  </span>
                            @elseif($project->return_mode == 1)
                                <span class="mt-5 pt-5">周期 （小时）</span>
                            @else
                                <span class="mt-5 pt-5">周期 (天)</span>
                            @endif
                        </label>
                        <br>
                        @error('days')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <!-- <input type="text" class="form-control" id="days" name="days" value="{{$project->days}}" > -->
                        @if($project->return_mode == 3)
                            <span class="mt-5 pt-5">{{$project->weeks}} (周)</span>
                        @elseif($project->return_mode == 4)
                            <span class="mt-5 pt-5">{{$project->months}} (月)  </span>
                        @elseif($project->return_mode == 1)
                            <span class="mt-5 pt-5">{{$project->days}} * 24 （小时）</span>
                        @else
                            <span class="mt-5 pt-5">{{$project->days}} (天)</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="min_invest" class="form-label">最小投资金额</label>
                        @error('min_invest')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="min_invest" name="min_invest" value="{{$project->min_invest}}" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="max_invest" class="form-label">最大投资金额</label>
                        @error('max_invest')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="max_invest" name="max_invest" value="{{$project->max_invest}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="max_time" class="form-label">最大投资次数</label>
                        @error('max_time')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="max_time" name="max_time" value="{{$project->max_times}}" >
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="desc" class="form-label">项目描述</label>
                        @error('desc')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" class="form-control" id="desc" name="desc" value="{{$project->desc}}" >
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="is_homepage" class="form-label">是否首页显示</label>
                        @error('is_homepage')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_homepage" name="is_homepage"  class="form-select" >
                            <option value="1" {{ $project->is_homepage == 1 ? 'selected' : ''}}>是</option>
                            <option value="0" {{ $project->is_homepage == 0 ? 'selected' : ''}}>否</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="is_recommend" class="form-label">是否热门推荐</label>
                        @error('is_recommend')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="is_recommend" name="is_recommend"  class="form-select" >
                            <option value="1" {{ $project->is_recommend == 1 ? 'selected' : ''}}>是</option>
                            <option value="0" {{ $project->is_recommend == 0 ? 'selected' : ''}}>否</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="level_id" class="form-label">会员等级</label>
                        @error('level_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <select id="level_id" name="level_id"  class="form-select" >
                            @foreach( $levels as $level )
                            <option value="{{ $level->level_id }}" {{ $level->level_id == $project->level_id ? 'selected' : ''}}> {{ $level->level_name }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label">项目状态</label>
                        @error('status')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="status" name="status"  class="form-select" >
                            <option value="1" @if($project->status == 1) selected @endif>上架</option>
                            <option value="0" @if($project->status == 0) selected @endif>下架</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sort" class="form-label">排序</label>
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror

                        <input type="number" class="form-control" id="sort" name="sort" value="{{ $project->sort }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="lang" class="form-label">语言</label>
                        @error('lang')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="lang" name="lang" class="form-select" >
                            <option value="cn" @if ($project->lang === 'cn') selected @endif>简体中文</option>
                            <option value="en" @if ($project->lang === 'en') selected @endif>English</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="litpic" class="form-label">项目封面图片</label>
                        @error('litpic')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="file" class="form-control" id="litpic" name="litpic" >
                        <img src="{{$project->litpic}}" alt="" class="img-fluid my-3 w-25">
                        <input type="hidden" name="old_litpic" value="{{$project->litpic}}">
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="detail" class="form-label">项目详情</label>
                        @error('detail')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <textarea type="text" class="form-control" id="detail" name="detail" ><?= html_entity_decode( $project->details ) ?></textarea>
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
            $('#detail').summernote({
                lang: 'zh-CN'
            });
        });
    </script>
</body>
</html>
