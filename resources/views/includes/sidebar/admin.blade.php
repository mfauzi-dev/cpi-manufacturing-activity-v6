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

            <li class="{{ Request::is('admin/departments*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('department.index') }}">
                    <i class="fas fa-sitemap"></i>
                    <span>Departments</span>
                </a>
            </li>

            <li class="{{ Request::is('admin/positions*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('position.index') }}">
                    <i class="fas fa-user-tie"></i>
                    <span>Positions</span>
                </a>
            </li>

            <li class="{{ Request::is('admin/outsourcings*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.outsourcing.index') }}">
                    <i class="fas fa-building"></i>
                    <span>Yayasan</span>
                </a>
            </li>


            <li class="{{ Request::is('admin/cost-centers*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.cost-center.index') }}">
                    <i class="fas fa-sitemap"></i>
                    <span>Cost Center</span>
                </a>
            </li>

            <li class="{{ Request::is('admin/groups*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.ps-group.index') }}">
                    <i class="fas fa-layer-group"></i>
                    <span>Group</span>
                </a>
            </li>


            <li class="{{ Request::is('admin/liness*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.line.index') }}">
                    <i class="fas fa-list"></i>
                    <span>Line</span>
                </a>
            </li>

            <li class="{{ Request::is('admin/product-groups*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.product-group.index') }}">
                    <i class="fas fa-industry"></i>
                    <span>Product Group</span>
                </a>
            </li>

            <li class="{{ Request::is('admin/users*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.user.index') }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
            </li>

            <li class="menu-header">Manajemen Karyawan</li>

            <li class="dropdown {{ Request::is('admin/employees*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
                    <i class="fas fa-users"></i>
                    <span>Karyawan</span>
                </a>

                <ul class="dropdown-menu">
                    <li class="{{ Request::routeIs('admin.employee.index') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.employee.index') }}">
                            Data Karyawan
                        </a>
                    </li>

                    <li class="{{ Request::routeIs('admin.employee.create') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.employee.create') }}">
                            Tambah Karyawan
                        </a>
                    </li>

                    <li class="{{ Request::routeIs('admin.employee.import') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.employee.import') }}">
                            Import Karyawan
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </aside>
</div>
