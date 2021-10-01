<!-- Dashboards -->
<li class="nav-item @active_exact(admin)">
    <a href="/admin" class="nav-link">
        <i class="fa fa-dashboard mr-1"></i> Dashboard
    </a>
</li>

@if( Gate::allows('viewAny', App\Page::class) )
    <li class="nav-item @active(admin/pages)">
        <a href="/admin/pages" class="nav-link">
            <i class="fa fa-file mr-1"></i> Pages
        </a>
    </li>
@endif
