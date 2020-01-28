@extends('spark::layouts.app')

@section('after-nav')

    <div class="bg-dark text-light navbar-team">
        <div class="container">
            <div class="d-inline-block team-name">
                <img src="{{ \Auth::user()->currentTeam->photo_url }}" height="20" />
                <span class="font-weight-normal ml-2">{{ \Auth::user()->currentTeam->name }}</span>
                <a class="fa fa-arrow-left" href="/" title="Change of team" style="color:#aaa; margin-left: 10px;"></a>
            </div>
            <div class="d-inline-block">
                <nav class="nav">
                    @include('front.sidebar')
                </nav>
            </div>
        </div>
    </div>
    
@endsection

@section('scripts-footer')
    <script type="text/javascript" src="https://weblabormx.github.io/Easy-JS-Library/library/script.js"></script>
@endsection
