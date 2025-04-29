<nav class="hidden px-5 py-2 mb-7 w-max bg-white rounded-md shadow-sm sm:flex" aria-label="Breadcrumb">
    <ol role="list" class="flex items-center space-x-3">
        <li class="relative">
            <a href="/admin" class="text-sm font-medium text-gray-400 transition-colors hover:text-gray-500">
                <span class="sr-only">{{ __('Home') }}</span>
                <span>
                    <svg class="h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                        xmlns="http://www.w3.org/2000/svg">
                        <path clip-rule="evenodd"
                            d="M9.293 2.293a1 1 0 011.414 0l7 7A1 1 0 0117 11h-1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3a1 1 0 00-1-1H9a1 1 0 00-1 1v3a1 1 0 01-1 1H5a1 1 0 01-1-1v-6H3a1 1 0 01-.707-1.707l7-7z"
                            fill-rule="evenodd"></path>
                    </svg>
                </span>
            </a>
        </li>
        @isset($slot)
            {{ $slot }}
        @endisset
        @isset($front)
            @foreach ($front->getBreadcrumbs($object ?? null, $data ?? null) as $breadcrumb)
                <li
                    class="relative before:absolute before:top-0 before:-translate-x-full before:left-0 before:content-['/'] pl-2">
                    <span
                        class="text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">{!! $breadcrumb['html'] !!}</span>
                </li>
            @endforeach
        @endisset
    </ol>
</nav>
