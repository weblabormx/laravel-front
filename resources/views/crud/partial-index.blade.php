@php
    $helper = $front->getPartialIndexHelper($result, $pagination_name ?? null, $show_filters ?? null);

    if (isset($frontIndexComponent) && $frontIndexComponent->columnsEnabled()) {
        $helper->setSelectedColumns($frontIndexComponent->visibleColumnKeys(), $frontIndexComponent->manualColumnKeys());
    }
@endphp

@if ($result->count() > 0)
    <div class="pb-2 text-gray-500 mt-6">
        {{ $helper->views() }}
        {{ $helper->totals() }}
        {{ $helper->filters() }}
    </div>
    <div class="overflow-x-auto -mx-4 shadow sm:-mx-6 md:mx-0 md:rounded-lg {{ $table_container_class ?? '' }}">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    @foreach ($helper->headers() as $field)
                        <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 {{ $field->class }}">
                            @if(isset($frontIndexComponent) && $frontIndexComponent->sortingEnabled() && $front->sortableIndexFields()->has($field->key))
                                @php
                                    $isSorted = $frontIndexComponent->sort === $field->key;
                                    $sortDirection = $isSorted ? $frontIndexComponent->direction : null;
                                @endphp
                                <button type="button" spinner wire:click="sortBy('{{ $field->key }}')" @class([
                                    'group inline-flex items-center gap-1 font-semibold cursor-pointer hover:text-primary-600',
                                    'text-primary-700' => $isSorted,
                                ]) aria-sort="{{ $isSorted ? ($sortDirection === 'desc' ? 'descending' : 'ascending') : 'none' }}">
                                    <span>{{ $field->title }}</span>
                                    <span @class([
                                        'inline-flex h-4 w-4 items-center justify-center transition',
                                        'text-primary-700' => $isSorted,
                                        'text-secondary-300 group-hover:text-secondary-500' => ! $isSorted,
                                    ]) aria-hidden="true">
                                        @if($sortDirection === 'desc')
                                            <x-icon name="chevron-down" class="h-3.5 w-3.5" />
                                        @elseif($sortDirection === 'asc')
                                            <x-icon name="chevron-up" class="h-3.5 w-3.5" />
                                        @else
                                            <x-icon name="chevron-up-down" class="h-3.5 w-3.5" />
                                        @endif
                                    </span>
                                    <span class="sr-only">
                                        @if($sortDirection === 'desc')
                                            {{ __('Sorted descending. Activate to sort ascending.') }}
                                        @elseif($sortDirection === 'asc')
                                            {{ __('Sorted ascending. Activate to sort descending.') }}
                                        @else
                                            {{ __('Activate to sort.') }}
                                        @endif
                                    </span>
                                </button>
                            @else
                                {{ $field->title }}
                            @endif
                        </th>
                    @endforeach
                    @if ($helper->show_actions)
                        <th scope="col" class="relative py-3.5 pr-4 pl-3 sm:pr-6">
                            <span class="sr-only">@lang('Edit')</span>
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($helper->rows() as $row)
                    <tr>
                        @foreach ($row->columns as $field)
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6 {{ $field->class }}">
                                {!! $field->value !!}
                            </td>
                        @endforeach
                        @if ($helper->show_actions)
                            @include('front::elements.object_actions', ['base_url' => $front->getBaseUrl(), 'object' => $row->object])
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pt-2 text-gray-500">
        {{ $helper->views() }}
        {{ $helper->totals() }}
        {{ $helper->filters() }}
    </div>
    @if($helper->links()!==null && $helper->links()->paginator->hasPages())
        <div class="mt-4">
            {{ $helper->links() }}
        </div>
    @endif
@else
    <div class="py-20 mt-4 text-center text-gray-500 bg-white md:rounded-lg">
        {{ __('No data to show') }}
    </div>
@endif
