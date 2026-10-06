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

            <li class="dropdown {{ Request::is('admin-absensi/attendances*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Absensi</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('admin-absensi/attendances') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('admin-absensi.attendance.index') }}">Table Absensi</a></li>
                    <li class="{{ Request::is('admin-absensi/attendances/summary') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-absensi.attendance.summary') }}">Summary
                            Absensi</a></li>
                    <li class="{{ Request::is('admin-absensi/attendances/create') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-absensi.attendance.create') }}">Tambah
                            Absensi</a>
                    </li>

                </ul>
            </li>

        </ul>
    </aside>
</div>
