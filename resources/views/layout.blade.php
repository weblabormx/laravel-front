@extends(isset($front) ? $front->layout : (isset($page) ? $page->layout : 'layouts.app'))

@section('after-nav')

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark p-0">
        <div class="container">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarFrontMenu" aria-controls="navbarFrontMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarFrontMenu">
                @isset(\Auth::user()->currentTeam)
                    <div class="d-inline-block">
                        <img src="{{ \Auth::user()->currentTeam->photo_url }}" height="20" />
                        <span class="font-weight-normal ml-2">{{ \Auth::user()->currentTeam->name }}</span>
                        <a class="fa fa-arrow-left" href="/" title="Change of team" style="color:#aaa; margin-left: 10px;"></a>
                    </div>
                @endisset
                <ul class="navbar-nav">
                    @include('front.sidebar')
                </ul>
            </div>
        </div>
    </nav>
    
@endsection

@section('scripts-footer')
    <script type="text/javascript" src="https://weblabormx.github.io/Easy-JS-Library/library/script.js"></script>
@endsection
