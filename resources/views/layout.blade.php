@extends((isset($front) && isset($front->layout)) ? $front->layout : ((isset($page) && isset($page->layout)) ? $page->layout : config('front.default_layout')))

@section('after-nav')

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark p-0">
        <div class="container">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarFrontMenu" aria-controls="navbarFrontMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarFrontMenu">
                @isset(\Auth::user()->currentTeam)
                    <div class="d-inline-block bg-light p-1 px-2 mr-2 rounded">
                        <img src="{{ \Auth::user()->currentTeam->photo_url }}" height="20" />
                        <span class="font-weight-normal ml-2">{{ \Auth::user()->currentTeam->name }}</span>
                    </div>
                @endisset
                <ul class="navbar-nav">
                    @include('front.sidebar')
                </ul>
            </div>
        </div>
    </nav>
    
@endsection

@push('scripts-footer')
    <script type="text/javascript" src="https://weblabormx.github.io/Easy-JS-Library/library/script.js"></script>
@endpush
