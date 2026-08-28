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

            <li class="{{ Request::is('dashboard') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="menu-header">Operational</li>
            <li class="dropdown {{ Request::is('manager/attendances*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Absensi</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('manager/attendances') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('manager.attendance.index') }}">Table Absensi</a></li>
                    <li class="{{ Request::is('manager/attendances/summary') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('manager.attendance.summary') }}">Summary
                            Absensi</a></li>
                    <li class="{{ Request::is('manager/attendances/create') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('manager.attendance.create') }}">Tambah
                            Absensi</a>
                    </li>

                </ul>
            </li>

            <li class="{{ Request::is('manager/penggajian-harian*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('manager.penggajian-harian.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Penggajian Harian</span>
                </a>
            </li>

            @if (strtolower(auth()->user()->department->name) === 'sausage')
                <li class="menu-header">SAUSAGE</li>

                <li class="dropdown {{ Request::is('manager/daily-activity') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                            class="fas fa-calendar-check"></i>
                        <span>Daily Activity Borongan</span></a>
                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('manager/daily-activity') ? 'active' : '' }}"><a class="nav-link"
                                href="{{ route('manager.daily-activity.index') }}">Summary Daily
                                Activity</a></li>
                    </ul>
                </li>

                <li class="dropdown {{ Request::is('manager/daily-production*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-calendar-check"></i>
                        <span>Daily Production Harian</span>
                    </a>

                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('manager/daily-production*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('manager.daily-production.index') }}">
                                Daily Summary Production
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="{{ Request::is('manager/penggajian-borongan*') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route('manager.penggajian-borongan.index') }}">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Penggajian Borongan</span>
                    </a>
                </li>
            @endif

            @if (strtolower(auth()->user()->department->name) === 'further processing')
                <li class="menu-header">FURTHER</li>

                <li class="dropdown {{ Request::is('manager/daily-activity-further*') ? 'active' : '' }}">
                    <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                        <i class="fas fa-calendar-check"></i>
                        <span>Daily Production Further</span>
                    </a>

                    <ul class="dropdown-menu">
                        <li class="{{ Request::is('manager/daily-activity-further*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('manager.daily-activity-further.index') }}">
                                Daily Summary Production
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </aside>
</div>
