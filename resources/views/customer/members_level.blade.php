@foreach ($members as $one)
<tr>
    <td class="single_check">
        <input type="checkbox" class="row_checkbox" data-item-id="{{$one->id}}">
    </td>
    <td>
        {{ $one->id }}
    </td>
    <td>{{ $one->phone }}</td>
    <td class="sheep_column{{$one->id}}">
        @if ($one->is_sheep == 1)
        <button class="btn-sm text-white bg-danger" id="change_btn"  data-id="{{$one->id}}"  data-status="{{$one->is_sheep}}">
            是
        </button>
        @else
        <button class="btn-sm text-white bg-success" id="change_btn"  data-id="{{$one->id}}"  data-status="{{$one->is_sheep}}">
            否
        </button>
        @endif
    </td>
    <td>{{ $one->realname }}</td>
    <td>{{ $one->customerExtra()->got_interest }}</td>
    <td>{{ $one->balance }}</td>
    <td>{{ $one->created_at }}</td>
    <td><?= $one->is_sure==1 ? '<span style="color:green;">已认证</span>' : '<span style="color:red;">未认证</span>' ?></td>
    <td>
        @if($one->customerExtra()->all_children_ids!=null or $one->customerExtra()->all_children_ids!='')
        <button class="btn-sm btn-success children" href="{{ route('customer.list_children', ['id'=>$one->id]) }}">
            查看下级列表
        </button>
        @endif
        <a class="btn-sm btn-primary" href="{{ route('customer.show', ['customer'=>$one->id]) }}">查看会员</a>
    </td>
</tr>
@endforeach