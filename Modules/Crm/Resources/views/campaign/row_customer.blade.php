@foreach ($getContact as $item)
<div style="margin:0 2px 2px 0;" class="tr">
    <input type="hidden" name="contact_id[]" value="{{$item->id}}">
    <button type="button" class="btn btn-info btn-xs">
        <span class="remove_contact_row" style="margin-right: 2px;">×</span>
       {{$item->text}}  ({{ $item->mobile}})
    </button>
</div>
@endforeach
