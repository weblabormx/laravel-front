<!-- Links -->
<ul class="sidenav-inner p-0 py-1" style="flex:none!important;">
    <!-- Dashboards -->
    <li class="sidenav-item">
        <a href="/admin" class="sidenav-link @active_exact(admin)"><i class="sidenav-icon ion ion-md-speedometer"></i>
            <div>Dashboard</div>
        </a>
    </li>

    <li class="sidenav-divider mb-1"></li>
    @if( Auth::user()->can('viewAny', App\Page::class) )
        <li class="sidenav-item">
            <a href="/admin/pages" class="sidenav-link @active(admin/pages)"><i class="sidenav-icon ion-md-cart"></i>
                <div>Pages</div>
            </a>
        </li>
    @endif
</ul>