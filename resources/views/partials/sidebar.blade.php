<aside class="left-sidebar">
  <div>
    <!-- Brand -->
    <div class="brand-logo d-flex align-items-center justify-content-between">
      <a href="{{ route('home') }}" class="text-nowrap logo-img">
        <span class="logo-title"><i class="ti ti-basket"></i> {{ config('app.name', 'Shop') }}</span>
      </a>
      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-6"></i>
      </div>
    </div>

    <!-- Sidebar navigation -->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar>
      <ul id="sidebarnav">

        <li class="nav-small-cap"><span class="hide-menu">Main</span></li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
            <i class="ti ti-layout-dashboard"></i><span class="hide-menu">Dashboard</span>
          </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Catalog</span></li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">
            <i class="ti ti-box"></i><span class="hide-menu">All Products</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('products.create') ? 'active' : '' }}" href="{{ route('products.create') }}">
            <i class="ti ti-circle-plus"></i><span class="hide-menu">Add Product</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('categories.index') ? 'active' : '' }}" href="{{ route('categories.index') }}">
            <i class="ti ti-category"></i><span class="hide-menu">All Categories</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('categories.create') ? 'active' : '' }}" href="{{ route('categories.create') }}">
            <i class="ti ti-folder-plus"></i><span class="hide-menu">Add Category</span>
          </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Sales</span></li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}" href="{{ route('admin.orders') }}">
            <i class="ti ti-shopping-cart"></i><span class="hide-menu">All Orders</span>
          </a>
        </li>
        <li class="sidebar-item">
          <a class="sidebar-link {{ request()->routeIs('admin.transactions') ? 'active' : '' }}" href="{{ route('admin.transactions') }}">
            <i class="ti ti-receipt"></i><span class="hide-menu">All Transactions</span>
          </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Store</span></li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('home') }}">
            <i class="ti ti-building-store"></i><span class="hide-menu">View Storefront</span>
          </a>
        </li>

        <li class="nav-small-cap"><span class="hide-menu">Account</span></li>
        <li class="sidebar-item">
          <a class="sidebar-link" href="{{ route('logout') }}"
             onclick="event.preventDefault(); document.getElementById('sidebar-logout-form').submit();">
            <i class="ti ti-logout"></i><span class="hide-menu">Logout</span>
          </a>
          <form id="sidebar-logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
        </li>

      </ul>
    </nav>
  </div>
</aside>
