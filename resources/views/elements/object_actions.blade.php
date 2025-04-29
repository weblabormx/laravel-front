@php $helper = $front->getActionsHelper($object, $base_url, $edit_link ?? null, $show_link ?? null); @endphp
<td class="whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
    @if( $helper->isSortable() )
        <!-- Sortable buttons -->
        {!! \Front::buttons()->getByName('up')->addLink($helper->upUrl())->setType('')->setTitle('')->setClass('inline-block text-primary-600 hover:text-primary-800')->form() !!}
        {!! \Front::buttons()->getByName('down')->addLink($helper->downUrl())->setType('')->setTitle('')->setClass('inline-block text-primary-600 hover:text-primary-800')->form() !!}
    @endif
    @if( $helper->canShow() )
        <!-- Edit button -->
        {!! \Front::buttons()->getByName('show')->addLink($helper->showUrl())->setType('')->setTitle('')->setClass('inline-block text-primary-600 hover:text-primary-800')->form() !!}
    @endif
    @if( $helper->canUpdate() )
        <!-- Edit button -->
        {!! \Front::buttons()->getByName('edit')->addLink($helper->updateUrl())->setType('')->setTitle('')->setClass('inline-block text-primary-600 hover:text-primary-800')->form() !!}
    @endif

    @foreach($helper->getActions($object) as $action)
        <a href="{{ $action->url }}" class="inline-block text-primary-600 hover:text-primary-800" title="{{ $action->title }}">
            @if(str_contains($action->icon, 'fa-'))
                <i class="{{$action->icon}} w-6 h-6 text-center text-xl block"></i>
            @else
                <x-icon name="{{$action->icon}}" class="w-6 h-6" />
            @endif
        </a>
    @endforeach
    @if( $helper->canRemove() )
        <!-- Remove button -->
        {!! \Front::buttons()->getByName('delete', $front, $object)->setType('')->setTitle('')->setClass('inline-block text-red-400 hover:text-red-600')->form() !!}
    @endif
</td>
