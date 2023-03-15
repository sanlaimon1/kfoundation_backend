<?php
    use App\models\Admin;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <title>日志列表</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link href="/css/bootstrap.min.css" rel="stylesheet" >
</head>

<body>
    <div class="container-fluid">
        <br />
        <form action="{{route('blockip.store')}}" method="POST">
            @csrf
            <nav class="row">

                <div class="col-3">
                    <label class="form-label">屏蔽IP：</label>
                    <input type="text" name="block_ip" id="block_ip" class="form-control @error('block_ip') is-invalid @enderror" />
                    @error('block_ip')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>
                <div class="col-3">
                    <label class="form-label">子网掩码</label>
                    <input type="number" name="subnet" id="subnet" class="form-control @error('subnet') is-invalid @enderror" />

                    @error('subnet')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="col-1">
                    <br>
                    <button type="submit" class="btn btn-success mt-2" >屏蔽</button>
                </div>
            </nav>

        </form>

        <br />
        <table class="table table-bordered table-striped" style="margin-top: 1rem;">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">IP地址</th>
                    <th scope="col">IP整数</th>
                    <th scope="col">  子网掩码</th>
                    <th style="width:260px;">操作</th>

                </tr>
            </thead>
            <tbody id="search_data">
                @foreach ($blockips as $blockip)
                <tr>
                    <td>{{ $blockip->id }}</td>
                    <td>{{ $blockip->ipaddress}}</td>
                    <td>{{ $blockip->longip }}</td>
                    <td>{{ $blockip->subnet }}</td>
                    <td >
                        <form action="{{ route('blockip.destroy', ['blockip'=>$blockip->id]) }}"
                         method="post"
                         class="d-inline-block" onsubmit="javascript:return del()">
                            {{ csrf_field() }}
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">删除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <footer style="display:flex;">
            <div class="container-fluid">
                <div class="box1 p-2">
                    <aside style="line-height: 37px; margin-right: 2rem;">
                        共计<strong>{{ $blockips->count() }}</strong>条数据
                    </aside>
                    {{ $blockips->links() }}
                </div>
                <div class="box2 p-2">
                <form method="get" action="{{ route('blockip.index') }}">
                    <label for="perPage">每页显示：</label>
                    <select id="perPage" name="perPage" class="p-2 m-2 text-primary rounded" onchange="this.form.submit()" >
                        <option value="10" {{ $blockips->perPage() == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $blockips->perPage() == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ $blockips->perPage() == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $blockips->perPage() == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ $blockips->perPage() == 200 ? 'selected' : '' }}>200</option>
                    </select>
                </form>
                </div>
            </div>
        </footer>
    </div>
<script>
function del() {
    var msg = "您真的确定要删除吗？\n\n请确认！";
    if (confirm(msg)==true){
        return true;
    }else{
        return false;
    }
}
</script>
</body>
</html>
