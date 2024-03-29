@php $helper = $front->getActionsHelper($object, $base_url, $edit_link ?? null, $show_link ?? null); @endphp
<td class="text-center d-print-none">
    @if( $helper->isSortable() )
        <!-- Sortable buttons -->
        {!! getButtonByName('up')->addLink($helper->upUrl())->setType('btn-default')->setTitle('')->setClass('p-0')->form() !!}
        {!! getButtonByName('down')->addLink($helper->downUrl())->setType('btn-default')->setTitle('')->setClass('p-0')->form() !!}
    @endif
	@if( $helper->canShow() )
    	<!-- Edit button -->
        {!! getButtonByName('show')->addLink($helper->showUrl())->setType('btn-default')->setTitle('')->setClass('p-0')->form() !!}
    @endif
    @if( $helper->canUpdate() )
    	<!-- Edit button -->
        {!! getButtonByName('edit')->addLink($helper->updateUrl())->setType('btn-default')->setTitle('')->setClass('p-0')->form() !!}
    @endif
    @if( $helper->canRemove() )
        <!-- Remove button -->
        {!! getButtonByName('delete', $front, $object)->setType('btn-default')->setTitle('')->setClass('p-0')->form() !!}
    @endif
    @foreach($helper->getActions($object) as $action)
        <a href="{{ $action->url }}" class="btn btn-default p-0" aria-hidden="true" title="{{ $action->title }}"><i class="{{$action->icon}}"></i></a>
    @endforeach
</td>
