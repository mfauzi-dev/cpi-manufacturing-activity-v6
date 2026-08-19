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
            {{-- 
            <li class="dropdown {{ Request::is('personalia/employees*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i class="fas fa-users"></i>
                    <span>Karyawan</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('personalia/employees') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('personalia.employee.index') }}">Table Karyawan</a></li>
                    <li class="{{ Request::is('personalia/employees/create') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('personalia.employee.create') }}">Tambah Karyawan</a></li>
                    <li class="{{ Request::is('personalia/employees/import') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('personalia.employee.import') }}">Import Excel</a></li>

                </ul>
            </li> --}}

            <li class="menu-header">Operational</li>

            <li class="dropdown {{ Request::is('admin-production/products*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Products</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('admin-production/products') ? 'active' : '' }}"><a class="nav-link"
                            href="{{ route('admin-production.product.index') }}">Table Product</a></li>
                    <li class="{{ Request::is('admin-production/products/create') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.product.create') }}">Tambah Product</a>
                    </li>
                    <li class="{{ Request::is('admin-production/products/import') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.product.import') }}">Import
                            Product</a>
                    </li>

                </ul>
            </li>

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
            <li class="dropdown {{ Request::is('admin-production/daily-activity*') ? 'active' : '' }}">
                <a href="#" class="nav-link has-dropdown" data-toggle="dropdown"><i
                        class="fas fa-calendar-check"></i>
                    <span>Daily Activity</span></a>
                <ul class="dropdown-menu">
                    <li class="{{ Request::is('admin-production/daily-activity') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.daily-activity.index') }}">Summary Daily
                            Activity</a></li>
                    <li class="{{ Request::is('admin-production/daily-activity/create') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.daily-activity.create') }}">Tambah
                            Daily Activity</a>
                    </li>
                    <li class="{{ Request::is('admin-production/daily-activity/import') ? 'active' : '' }}"><a
                            class="nav-link" href="{{ route('admin-production.daily-activity.import') }}">Import
                            Daily Activity</a>
                    </li>

                </ul>
            </li>

            <!-- PRODUCTION -->
            {{-- 
            <li class="{{ Request::is('admin-production/attendances*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin-production.attendance.index') }}">
                    <i class="fas fa-clock"></i>
                    <span>Absensi</span>
                </a>
            </li> --}}

            <!-- COMPENSATION -->
            {{-- <li class="menu-header">Compensation</li> --}}

            {{-- 
            <li class="{{ Request::is('deductions*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('deduction.index') }}">
                    <i class="fas fa-minus-circle"></i>
                    <span>Deduction</span>
                </a>
            </li> --}}

            <!-- PAYROLL -->
            {{-- <li class="menu-header">Payroll</li> --}}
            {{-- 
            <li class="{{ Request::is('payrolls/generate*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('payroll.generate') }}">
                    <i class="fas fa-cogs"></i>
                    <span>Generate Payroll</span>
                </a>
            </li> --}}

            {{-- <li class="{{ Request::is('admin/payrolls/harian*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.payroll.harian.index') }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Payroll Harian</span>
                </a>
            </li>

            <li class="{{ Request::is('admin/payrolls/bulanan*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.payroll.bulanan.index') }}">
                    <i class="fas fa-money-bill"></i>
                    <span>Payroll Bulanan</span>
                </a>
            </li>

            <li class="{{ Request::is('payroll-reports*') ? 'active' : '' }}">
                <a class="nav-link" href="#">
                    <i class="fas fa-file-excel"></i>
                    <span>Reports</span>
                </a>
            </li> --}}
        </ul>
    </aside>
</div>
