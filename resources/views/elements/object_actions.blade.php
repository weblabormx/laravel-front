@php $helper = $front->getActionsHelper($object, $base_url, $edit_link ?? null, $show_link ?? null); @endphp
<td class="text-center d-print-none">
    @if( $helper->isSortable() )
        <!-- Edit button -->
        <a href="{{$helper->upUrl()}}" class="btn btn-default p-0" aria-hidden="true" title="{{ __('Up') }}"><i class="fa fa-arrow-up"></i></a>
        <a href="{{$helper->downUrl()}}" class="btn btn-default p-0" aria-hidden="true" title="{{ __('Down') }}"><i class="fa fa-arrow-down"></i></a>
    @endif
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
        {!! getButtonByName('delete', $front, $object)->setType('')->setClass('btn btn-default p-0')->setTitle('')->form() !!}
    @endif
    @foreach($helper->getActions($object) as $action)
        <a href="{{ $action->url }}" class="btn btn-default p-0" aria-hidden="true" title="{{ $action->title }}"><i class="{{$action->icon}}"></i></a>
    @endforeach
</td>
