@php    
    if(isset($front)) {
        $breadcrumbs = $front->getBreadcrumbs($object ?? null, $data ?? null)->map(function($item) {
            return [
                'label' => $item['title'],
                'url' => $item['url'] ?? null,
            ];
        })->all();
    } else {
        $breadcrumbs = [];
    }
@endphp
@include('layouts.partials.breadcrumbs', [
    'breadcrumb' => $breadcrumbs,
    'title' => Request::segment(1)=='app' ? config('app.name') : (Request::segment(1)=='team' ? team()->name : __('Dashboard')),
])