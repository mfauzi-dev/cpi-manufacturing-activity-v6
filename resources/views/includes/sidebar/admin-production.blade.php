<div class="main-sidebar sidebar-style-2">
    <aside id="sidebar-wrapper">

        <!-- BRAND -->
        <div class="sidebar-brand">
            <a href="#">Manufacture Payroll</a>
        </div>
        <div class="sidebar-brand sidebar-brand-sm">
            <a href="#">MPS</a>
        </div>

        <ul class="sidebar-menu">

            <!-- DASHBOARD -->
            <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="menu-header">Operational</li>

            <li class="dropdown {{ Request::is('admin-production/attendances*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Absensi</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('admin-production/attendances') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('admin-production.attendance.index') }}">Table Absensi</a></li>
                    <li class="{{ Request::is('admin-production/attendances/summary') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.attendance.summary') }}">Summary
                            Absensi</a></li>
                    <li class="{{ Request::is('admin-production/attendances/create') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.attendance.create') }}">Tambah
                            Absensi</a>
                    </li>

                </ul>
            </li>

            @if (strtolower(auth()->user()->department->name) === 'sausage' ||
                    strtolower(auth()->user()->department->name) === 'slaughter house')
                <li class="{{ Request::is('admin-production/penggajian-borongan*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('admin-production.penggajian-borongan.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Penggajian Borongan</span>
                    </a>
                </li>
            @endif

            <li class="{{ Request::is('admin-production/penggajian-harian*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin-production.penggajian-harian.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Penggajian Harian</span>
                </a>
            </li>

            @if (strtolower(auth()->user()->department->name) === 'sausage')
                <li class="menu-header">SAUSAGE</li>

                <li class="dropdown {{ Request::is('admin-production/products*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                            class="fas fa-calendar-check"></i>
                        <span>Products</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('admin-production/products') ? 'active' : '' }}"><a class="nav-link"
                                href="{{ route('admin-production.product.index') }}">Table Product</a></li>
                        <li class="{{ Request::is('admin-production/products/create') ? 'active' : '' }}"><a
                                class="nav-link" href="{{ route('admin-production.product.create') }}">Tambah
                                Product</a>
                        </li>
                        <li class="{{ Request::is('admin-production/products/import') ? 'active' : '' }}"><a
                                class="nav-link" href="{{ route('admin-production.product.import') }}">Import
                                Product</a>
                        </li>

                    </ul>
                </li>
                <li
                    class="dropdown {{ Request::is('admin-production/daily-activity') || Request::is('admin-production/daily-activity/create')
                        ? 'active'
                        : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                            class="fas fa-calendar-check"></i>
                        <span>Daily Activity Borongan</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('admin-production/daily-activity') ? 'active' : '' }}"><a
                                class="nav-link" href="{{ route('admin-production.daily-activity.index') }}">Summary
                                Daily
                                Activity</a>
                        </li>
                        <li class="{{ Request::is('admin-production/daily-activity/create') ? 'active' : '' }}"><a
                                class="nav-link" href="{{ route('admin-production.daily-activity.create') }}">Tambah
                                Daily Activity</a>
                    </ul>
                </li>

                <li class="dropdown {{ Request::is('admin-production/daily-production*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                            class="fas fa-calendar-check"></i>
                        <span>Daily Production Harian</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('admin-production/daily-production') ? 'active' : '' }}"><a
                                class="nav-link" href="{{ route('admin-production.daily-production.index') }}">Summary
                                Daily Production</a>
                        </li>
                        <li class="{{ Request::is('admin-production/daily-production/create') ? 'active' : '' }}"><a
                                class="nav-link" href="{{ route('admin-production.daily-production.create') }}">Tambah
                                Daily Production</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (strtolower(auth()->user()->department->name) === 'further processing')
                <li class="menu-header">FURTHER</li>

                <li class="dropdown {{ Request::is('admin-production/products-further*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                            class="fas fa-calendar-check"></i>
                        <span>Products</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('admin-production/products-further') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-production.product-further.index') }}">Table
                                Product</a>
                        </li>
                        <li class="{{ Request::is('admin-production/products-further/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-production.product-further.create') }}">Tambah
                                Product</a>
                        </li>
                        <li class="{{ Request::is('admin-production/products-further/import') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-production.product-further.import') }}">Import
                                Product</a>
                        </li>
                    </ul>
                </li>

                <li class="dropdown {{ Request::is('admin-production/daily-activity-further*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                            class="fas fa-calendar-check"></i>
                        <span>Daily Production Further</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('admin-production/daily-activity-further') ? 'active' : '' }}"><a
                                class="nav-link"
                                href="{{ route('admin-production.daily-activity-further.index') }}">Summary
                                Daily Production</a>
                        </li>
                        <li
                            class="{{ Request::is('admin-production/daily-activity-further/create') ? 'active' : '' }}">
                            <a class="nav-link"
                                href="{{ route('admin-production.daily-activity-further.create') }}">Tambah
                                Daily Production</a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (strtolower(auth()->user()->department->name) === 'slaughter house')
                <li class="menu-header">SLAUGHTER HOUSE</li>

                <!-- PRODUCTS -->

                <li class="dropdown {{ Request::is('admin-production/products*') ? 'active' : '' }}">

                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-calendar-check"></i>
                        <span>Products</span>
                    </a>

                    <ul class="dropdown-menu">

                        <li class="{{ Request::is('admin-production/products') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-production.product.index') }}">
                                Table Product
                            </a>
                        </li>

                        <li class="{{ Request::is('admin-production/products/create') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-production.product.create') }}">
                                Tambah Product
                            </a>
                        </li>

                        <li class="{{ Request::is('admin-production/products/import') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin-production.product.import') }}">
                                Import Product
                            </a>
                        </li>

                    </ul>

                </li>

                <!-- DAILY ACTIVITY SLAUGHTER HOUSE -->

                <li
                    class="dropdown {{ Request::is('admin-production/daily-activity-slaughter-house*') ? 'active' : '' }}">

                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-calendar-check"></i>
                        <span>Daily Production Slaughter House</span>
                    </a>

                    <ul class="dropdown-menu">

                        <li
                            class="{{ Request::is('admin-production/daily-activity-slaughter-house') ? 'active' : '' }}">
                            <a class="nav-link"
                                href="{{ route('admin-production.daily-activity-slaughter-house.index') }}">
                                Summary Daily Production
                            </a>
                        </li>

                        <li
                            class="{{ Request::is('admin-production/daily-activity-slaughter-house/create') ? 'active' : '' }}">
                            <a class="nav-link"
                                href="{{ route('admin-production.daily-activity-slaughter-house.create') }}">
                                Tambah Daily Production
                            </a>
                        </li>

                    </ul>

                </li>
            @endif
        </ul>
    </aside>
</div>
