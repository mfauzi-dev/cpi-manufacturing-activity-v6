<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
    <form class="form-inline mr-auto">
        <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
        </ul>
    </form>
    <ul class="navbar-nav navbar-right">

        <li class="nav-item dropdown" style="position:relative;">
            <a href="#" class="nav-link nav-link-lg" data-toggle="dropdown" id="notif-bell">
                <i class="fas fa-bell"></i>
                <span id="notif-badge" class="badge badge-danger"
                    style="display:none; position:absolute; top:6px; right:4px; font-size:9px; 
                   min-width:16px; height:16px; border-radius:8px; 
                   padding:2px 4px; line-height:12px;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="width:320px; max-height:400px; overflow-y:auto;">
                <div class="dropdown-title d-flex justify-content-between align-items-center">
                    Notifikasi
                    <span id="notif-clear" class="text-danger" style="font-size:12px; cursor:pointer;">Hapus
                        semua</span>
                </div>
                <ul id="notif-list" class="list-unstyled mb-0">
                    <li class="dropdown-item text-muted text-center" id="notif-empty">Tidak ada notifikasi</li>
                </ul>
            </div>
        </li>

        <li class="dropdown">
            <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                <img alt="image" src="{{ asset('stisla/dist/assets/img/avatar/avatar-1.png') }}"
                    class="rounded-circle mr-1">
                <div class="d-sm-none d-lg-inline-block">Hi, {{ Auth::user()->name }}</div>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <div class="dropdown-title">Logged in 5 min ago</div>
                <a href="{{ route('password.edit') }}" class="dropdown-item has-icon">
                    <i class="far fa-user"></i> Update Password
                </a>
                <a href="features-activities.html" class="dropdown-item has-icon">
                    <i class="fas fa-bolt"></i> Activities
                </a>
                <a href="features-settings.html" class="dropdown-item has-icon">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item has-icon text-danger"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>

{{-- @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/pusher-js@8.4.0/dist/web/pusher.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        const badge = document.getElementById('notif-badge');
        const list = document.getElementById('notif-list');
        const empty = document.getElementById('notif-empty');
        let count = 0;

        const iconMap = {
            'overtime': 'fas fa-clock text-warning',
            'bonus': 'fas fa-gift text-success',
            'daily_earning': 'fas fa-coins text-primary',
        };

        const handleNotif = (e) => {
            count++;
            badge.textContent = count;
            badge.style.display = 'inline-block';
            empty.style.display = 'none';

            const icon = iconMap[e.type] ?? 'fas fa-bell text-secondary';
            const li = document.createElement('li');
            li.className = 'dropdown-item';
            li.innerHTML = `
            <div class="d-flex align-items-start gap-2">
                <i class="${icon} mt-1 mr-2"></i>
                <div>
                    <div style="font-size:13px; font-weight:600;">${e.message}</div>
                    <div class="text-muted" style="font-size:11px;">Baru saja</div>
                </div>
            </div>
        `;
            list.prepend(li);
        };

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: `{{ env('REVERB_APP_KEY') }}`,
            wsHost: `{{ env('REVERB_HOST', 'localhost') }}`,
            wsPort: {{ env('REVERB_PORT', 6001) }},
            wssPort: {{ env('REVERB_PORT', 6001) }},
            forceTLS: false,
            enabledTransports: ['ws'],
        });

        Echo.channel('manager-notifications')
            .listen('DailyEarningUpdated', handleNotif)
            .listen('BonusUpdated', handleNotif)
            .listen('OvertimeUpdated', handleNotif);

        document.getElementById('notif-clear').addEventListener('click', () => {
            list.innerHTML = '';
            list.appendChild(empty);
            empty.style.display = 'block';
            count = 0;
            badge.style.display = 'none';
        });
    </script>
@endpush --}}
