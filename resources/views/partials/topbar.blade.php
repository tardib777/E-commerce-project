<header class="app-header">
  <nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item d-block d-xl-none">
        <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
          <i class="ti ti-menu-2"></i>
        </a>
      </li>
      <li class="nav-item d-none d-md-block">
        <span class="navbar-text fw-semibold text-dark">@yield('topbar_title', 'Admin Dashboard')</span>
      </li>
    </ul>

    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
      <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">

        <li class="nav-item dropdown">
          <a class="nav-link position-relative" href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="ti ti-bell fs-5"></i>
            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger rounded-circle" style="margin-left:-6px;margin-top:4px;"></span>
          </a>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="drop1" style="min-width:260px;">
            <h6 class="dropdown-header fw-bold">Notifications</h6>
            <a href="javascript:void(0)" class="dropdown-item text-muted small">No new notifications</a>
          </div>
        </li>

        <li class="nav-item dropdown ms-2">
          <a class="nav-link d-flex align-items-center gap-2" href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="d-flex align-items-center justify-content-center rounded-circle text-white fw-bold"
                  style="width:38px;height:38px;background:linear-gradient(135deg,#5d87ff,#4570ea);">
              {{ strtoupper(substr(Auth::user()->firstname ?? 'A', 0, 1)) }}
            </span>
            <span class="d-none d-md-block text-start lh-1">
              <span class="d-block fw-semibold text-dark">{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</span>
              <span class="d-block text-muted" style="font-size:.72rem;">Administrator</span>
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-end" aria-labelledby="drop2" style="min-width:220px;">
            <div class="px-3 py-2">
              <p class="mb-0 fw-semibold text-dark">{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</p>
              <p class="mb-0 text-muted small">{{ Auth::user()->email }}</p>
            </div>
            <div class="dropdown-divider"></div>
            <a href="{{ route('home') }}" class="dropdown-item d-flex align-items-center gap-2">
              <i class="ti ti-layout-dashboard fs-6"></i> Dashboard
            </a>
            <a href="{{ route('products.create') }}" class="dropdown-item d-flex align-items-center gap-2">
              <i class="ti ti-circle-plus fs-6"></i> Add Product
            </a>
            <div class="dropdown-divider"></div>
            <a href="{{ route('logout') }}" class="dropdown-item d-flex align-items-center gap-2 text-danger"
               onclick="event.preventDefault(); document.getElementById('topbar-logout-form').submit();">
              <i class="ti ti-logout fs-6"></i> Logout
            </a>
            <form id="topbar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
          </div>
        </li>

      </ul>
    </div>
  </nav>
</header>
