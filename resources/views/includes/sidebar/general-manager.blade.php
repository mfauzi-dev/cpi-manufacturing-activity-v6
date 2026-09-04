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

            <!-- MASTER DATA -->
            <li class="menu-header">Master Data</li>

            <li class="{{ Request::is('general-manager/departments*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.department.index') }}">
                    <i class="fas fa-sitemap"></i>
                    <span>Departments</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/positions*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.position.index') }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Positions</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/outsourcings*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.outsourcing.index') }}">
                    <i class="fas fa-building"></i>
                    <span>Yayasan</span>
                </a>
            </li>


            <li class="{{ Request::is('general-manager/cost-centers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.cost-center.index') }}">
                    <i class="fas fa-sitemap"></i>
                    <span>Cost Center</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/groups*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.ps-group.index') }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Group</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/users*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.user.index') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/employees*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.employee.index') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Karyawan</span>
                </a>
            </li>



            <li class="menu-header">Operational</li>
            <li class="dropdown {{ Request::is('general-manager/attendances*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Absensi</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('general-manager/attendances') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('general-manager.attendance.index') }}">Table Absensi</a></li>
                    <li class="{{ Request::is('general-manager/attendances/summary') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('general-manager.attendance.summary') }}">Summary
                            Absensi</a></li>
                    <li class="{{ Request::is('general-manager/attendances/create') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('general-manager.attendance.create') }}">Tambah
                            Absensi</a>
                    </li>

                </ul>
            </li>

            <li class="{{ Request::is('general-manager/employee-productivity*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.employee-productivity.list') }}">
                    <i class="fas fa-chart-line"></i>
                    <span>Produktivitas Karyawan</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/penggajian-borongan*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.penggajian-borongan.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Penggajian Borongan</span>
                </a>
            </li>

            <li class="{{ Request::is('general-manager/penggajian-harian*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('general-manager.penggajian-harian.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Penggajian Harian</span>
                </a>
            </li>

            <li class="menu-header">SAUSAGE</li>

            <li class="dropdown {{ Request::is('general-manager/daily-activity') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Daily Activity Borongan</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('general-manager/daily-activity') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('general-manager.daily-activity.index') }}">Daily Summary
                            Activity</a></li>
                </ul>
            </li>
            <li class="dropdown {{ Request::is('general-manager/daily-production*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-calendar-check"></i>
                    <span>Daily Production Harian</span>
                </a>

                <ul class="dropdown-menu">
                    <li class="{{ Request::is('general-manager/daily-production*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('general-manager.daily-production.index') }}">
                            Daily Summary Production
                        </a>
                    </li>
                </ul>
            </li>


            <li class="menu-header">FURTHER</li>

            <li class="dropdown {{ Request::is('general-manager/daily-activity-further*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-calendar-check"></i>
                    <span>Daily Production Further</span>
                </a>

                <ul class="dropdown-menu">
                    <li class="{{ Request::is('general-manager/daily-activity-further*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('general-manager.daily-activity-further.index') }}">
                            Daily Summary Production
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-header">SLAUGHTER HOUSE</li>
            <li class="dropdown {{ Request::is('general-manager/daily-activity-slaughter-house*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"> <i
                        class="fas fa-calendar-check"></i> <span>Daily Production Slaughter House</span> </a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('general-manager/daily-activity-slaughter-house*') ? 'active' : '' }}">
                        <a class="nav-link"
                            href="{{ route('general-manager.daily-activity-slaughter-house.index') }}"> Daily Summary
                            Production </a>
                    </li>
                </ul>
            </li>
        </ul>
    </aside>
</div>
