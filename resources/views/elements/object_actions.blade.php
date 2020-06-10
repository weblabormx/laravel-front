@php 
    $link = $base_url.'/'.$object->getKey();
    $edit = $edit_link ?? '{key}/edit';
    $edit = str_replace('{key}', $object->getKey(), $edit);
@endphp
<td class="text-center">
	@if( Gate::allows('view', $object) && in_array('show', $front->actions) )
    	<!-- Edit button -->
        <a href="{{$link}}{{$show_link ?? ''}}" class="btn btn-default p-0" aria-hidden="true" title="{{ __('See') }}"><i class="fa fa-eye"></i></a>
    @endif
    @if( Gate::allows('update', $object) && in_array('edit', $front->actions) )
    	<!-- Edit button -->
        <a href="{{$base_url}}/{{$edit}}" class="btn btn-default p-0" aria-hidden="true" title="{{ __('Edit') }}"><i class="fa fa-edit"></i></a>
    @endif
    <!-- Remove button -->
    @if( Gate::allows('delete', $object) && in_array('destroy', $front->actions) )
        <a data-type="confirm" title="{{ __('Delete') }}" data-info="{{ __('Do you really want to remove this item?') }}" data-button-yes="{{ __('Yes') }}" data-button-no="{{ __('No') }}" data-action="{{url($link)}}" data-redirection="{{url($base_url)}}" data-variables='{ "_method": "delete", "_token": "{{ csrf_token() }}" }' class="btn btn-default p-0" href="#"><i class="fa fa-times"></i></a>
    @endif
</td>
