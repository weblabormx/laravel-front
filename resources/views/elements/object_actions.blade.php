@php 
    $link = $base_url.'/'.$object->getKey();
    $edit = $edit_link ?? '{key}/edit';
    $edit = str_replace('{key}', $object->getKey(), $edit);
@endphp
<td class="text-center">
	@if( Auth::user()->can('view', $object) )
    	<!-- Edit button -->
        <a href="{{$link}}{{$show_link ?? ''}}" class="btn btn-default btn-xs icon-btn md-btn-flat article-tooltip" aria-hidden="true" title="{{ __('See') }}"><i class="fa fa-eye"></i></a>
    @endif
    @if( Auth::user()->can('update', $object) )
    	<!-- Edit button -->
        <a href="{{$base_url}}/{{$edit}}" class="btn btn-default btn-xs icon-btn md-btn-flat article-tooltip" aria-hidden="true" title="{{ __('Edit') }}"><i class="fa fa-edit"></i></a>
    @endif
    <!-- Remove button -->
    @if( Auth::user()->can('delete', $object) )
        <a data-type="confirm" title="{{ __('Do you really want to remove this item?') }}" data-info="{{ __('Do you really want to remove this item?') }}" data-button-yes="{{ __('Yes') }}" data-button-no="{{ __('No') }}" data-action="{{url($link)}}" data-redirection="{{url($base_url)}}" data-variables='{ "_method": "delete", "_token": "{{ csrf_token() }}" }' class="btn btn-default btn-xs icon-btn md-btn-flat article-tooltip" href="#"><i class="fa fa-times"></i></a>
    @endif
</td>