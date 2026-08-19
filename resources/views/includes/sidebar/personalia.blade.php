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

            <li class="menu-header">Master Data</li>

            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-briefcase"></i>
                    <span>Positions</span>
                </a>
            </li>

            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-layer-group"></i>
                    <span>PS Group</span>
                </a>
            </li>

            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-building"></i>
                    <span>Cost Center</span>
                </a>
            </li>

            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-handshake"></i>
                    <span>Outsourcing</span>
                </a>
            </li>

            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-user-shield"></i>
                    <span>Role</span>
                </a>
            </li>

            <li>
                <a class="nav-link" href="#">
                    <i class="fas fa-user-cog"></i>
                    <span>User</span>
                </a>
            </li>

            <li class="menu-header">Manajemen Karyawan</li>

            <li class="dropdown {{ Request::is('personalia/employees*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-users"></i>
                    <span>Karyawan</span>
                </a>

                <ul class="dropdown-menu">
                    <li class="{{ Request::routeIs('personalia.employee.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('personalia.employee.index') }}">
                            Data Karyawan
                        </a>
                    </li>

                    <li class="{{ Request::routeIs('personalia.employee.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('personalia.employee.create') }}">
                            Tambah Karyawan
                        </a>
                    </li>

                    <li class="{{ Request::routeIs('personalia.employee.import') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('personalia.employee.import') }}">
                            Import Karyawan
                        </a>
                    </li>
                </ul>
            </li>

            <li class="dropdown ">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-users"></i>
                    <span>Overtime</span>
                </a>

                <ul class="dropdown-menu">
                    <li class="{{ Request::routeIs('personalia.employee.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('personalia.employee.index') }}">
                            Data Overtime
                        </a>
                    </li>

                    <li class="{{ Request::routeIs('personalia.employee.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('personalia.employee.create') }}">
                            Tambah Overtime
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-header">Payroll</li>

            <li class="">
                <a class="nav-link" href="#">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payroll Borongan</span>
                </a>
            </li>

            <li class="">
                <a class="nav-link" href="#">
                    <i class="fas fa-money-bill"></i>
                    <span>Payroll Outsourcing</span>
                </a>
            </li>




            {{-- <li class="dropdown {{ Request::is('admin/attendance*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Absensi</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('admin/attendances') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('admin.attendance.index') }}">Table Absensi</a></li>
                    <li class="{{ Request::is('admin/attendances/create') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('admin.attendance.create') }}">Tambah Absensi</a></li>

                </ul>
            </li> --}}

        </ul>
    </aside>
</div>
