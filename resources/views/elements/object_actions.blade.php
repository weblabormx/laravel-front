@php $helper = $front->getActionsHelper($object, $base_url, $edit_link ?? null, $show_link ?? null); @endphp
<td class="text-center">
	@if( $helper->canShow() )
    	<!-- Edit button -->
        <a href="{{$helper->showUrl()}}" class="btn btn-default p-0" aria-hidden="true" title="{{ __('See') }}"><i class="fa fa-eye"></i></a>
    @endif
    @if( $helper->canUpdate() )
    	<!-- Edit button -->
        <a href="{{$helper->updateUrl()}}" class="btn btn-default p-0" aria-hidden="true" title="{{ __('Edit') }}"><i class="fa fa-edit"></i></a>
    @endif
    <!-- Remove button -->
    @if( $helper->canRemove() )
        <a data-type="confirm" title="{{ __('Delete') }}" data-info="{{ __('Do you really want to remove this item?') }}" data-button-yes="{{ __('Yes') }}" data-button-no="{{ __('No') }}" data-action="{{url($link)}}" data-redirection="{{url($helper->removeUrl())}}" data-variables='{ "_method": "delete", "_token": "{{ csrf_token() }}" }' class="btn btn-default p-0" href="#"><i class="fa fa-times"></i></a>
    @endif
</td>
