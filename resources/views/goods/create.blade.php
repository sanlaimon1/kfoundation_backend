<!DOCTYPE html>
<html lang="zh">

<head>
    <title>创建商品</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/loading.css">
    <script src="/js/bootstrap.bundle.min.js"></script>
    <script src="/static/adminlte/plugins/jquery/jquery.min.js"></script>
    <style>
        #app
        {
            padding-top: 1rem;
        }
        #app .form-label
        {
            color: green;
            font-size: 14px;
        }
        #app .alert
        {
            font-size: 14px;
        }
        #app .frame
        {
            border: 1px solid black;
            border-radius:5px;
            margin-top: .5rem;
        }
        .litpic{
            display: inline-block;
            background-color: #ffc107;
            color: white;
            padding: 0.375rem 0.75rem;
            border-radius: 0.3rem;
            cursor: pointer;
            margin-top: 1rem;
            color: #000
        }
    </style>
    <script>
        $(function(){
            function readURLlitpic(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#show-litpic').attr('src', e.target.result);
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $("#litpic").change(function(){
                readURLlitpic(this);
            });
        });
    </script>
</head>

<body>
    <div id="app" class="container-fluid">
        <nav id="nav" style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('satistatics') }}">后台首页</a></li>
                <li class="breadcrumb-item">商城管理</li>
                <li class="breadcrumb-item"><a href="{{ route('goods.index') }}">商品列表</a></li>
                <li class="breadcrumb-item active" aria-current="page">创建商品</li>
            </ol>
        </nav>
        
        <form action="{{ route('goods.store') }}" method="post" enctype="multipart/form-data">
            {{ csrf_field() }}
            <section class="row frame">
                <div class="row">
                    <div class="mb-3 col-6">
                        @error('goods_name')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <label for="goods_name">商品名称</label>
                        <input type="text" name="goods_name" class="form-control" id="goods_name" placeholder="商品名称" />
                    </div>
                    <div class="mb-3 col-6">
                        <label for="desc" class="form-label">商品图片</label>
                        <!-- <button class="btn btn-warning">选择</button> -->
                        <img id="show-litpic" src="#" width="120" height="120" />
                        <input type="file" id="litpic" name="litpic" hidden/>
                        <label class="litpic" for="litpic">选择</label>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-6">
                        @error('score')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <label for="score" class="form-label">积分</label>
                        <input type="number" name="score" class="form-control" id="score" placeholder="积分" />                    
                    </div>
                    <div class="mb-3 col-6">
                        <label for="level_id" class="form-label">需要的VIP等级</label>
                        @error('level_id')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="level_id" name="level_id" class="form-select" >
                            @foreach( $level_items as $key=>$one_level )
                            <option value="{{ $key }}"> {{ $one_level }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-6">
                        @error('store_num')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <label for="store_num" class="form-label">库存</label>
                        <input type="number" name="store_num" class="form-control" id="store_num" placeholder="库存" />                    
                    </div>
                    <div class="mb-3 col-6">
                        @error('count_exchange')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <label for="count_exchange" class="form-label">兑换数量</label>
                        <input type="number" name="count_exchange" class="form-control" id="count_exchange" placeholder="兑换数量" />                    
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-6">
                        @error('sort')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <label for="sort" class="form-label">排序</label>
                        <input type="number" name="sort" class="form-control" id="sort" placeholder="排序" />                    
                    </div>
                    {{-- <div class="mb-3 col-4">
                        <label for="enable" class="form-label">状态</label>
                        @error('enable')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <select id="enable" name="enable" class="form-select" >
                            <option value="1">启用</option>
                            <option value="0">禁用</option>
                        </select>
                    </div> --}}
                    <div class="mb-3 col-6">
                        <label for="comment" class="form-label">备注</label>
                        @error('comment')
                        <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                        <input type="text" name="comment" class="form-control" id="comment" placeholder="备注" />
                    </div>
                </div>

            </section>
            
            <button type="submit" class="btn btn-primary mt-4">创建</button>
        </form>

    </div>

</body>

</html>