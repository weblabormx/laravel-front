<div class="demo-navbar-user nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
        <span class="d-inline-flex flex-lg-row-reverse align-items-center align-middle">
            <img src="{{Auth::user()->photo_url}}" alt class="d-block ui-w-30 rounded-circle">
            <span class="px-1 mr-lg-2 ml-2 ml-lg-0">{{Auth::user()->name}}</span>
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <a href="/settings" class="dropdown-item"><i class="ion ion-md-settings text-lightest"></i> &nbsp; Account settings</a>
        <a href="/settings/teams/{{Auth::user()->currentTeam->id}}" class="dropdown-item"><i class="ion ion-md-settings text-lightest"></i> &nbsp; Team settings</a>
        <a href="/" class="dropdown-item"><i class="fa fa-exchange-alt  text-lightest"></i> &nbsp; Change team</a>
        <div class="dropdown-divider"></div>
        <a href="/logout" class="dropdown-item"><i class="ion ion-ios-log-out text-danger"></i> &nbsp; Log Out</a>
    </div>
</div>