<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Smart Discussion Forum')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        :root {
            --ink: #1c2b33;
            --slate: #3d5a6c;
            --paper: #f6f4ee;
            --accent: #2f6f5e;
            --accent-dark: #204b3f;
            --warn: #b3542e;
            --line: #d8d2c4;
            --radius: 6px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Iowan Old Style', 'Georgia', serif;
            background: var(--paper);
            color: var(--ink);
        }
        header.topbar {
            background: var(--ink);
            color: var(--paper);
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            flex-wrap: wrap;
            gap: 8px;
        }
        header.topbar .brand { font-weight: 700; letter-spacing: .04em; text-transform: uppercase; font-size: 15px; }
        header.topbar nav { display: flex; align-items: center; flex-wrap: wrap; }
        header.topbar nav a {
            color: var(--paper);
            text-decoration: none;
            margin-left: 18px;
            font-size: 14px;
            opacity: .85;
            padding: 4px 2px;
            border-bottom: 2px solid transparent;
            transition: opacity 0.15s, border-color 0.15s;
        }
        header.topbar nav a:hover { opacity: 1; }
        header.topbar nav a.active { opacity: 1; border-bottom-color: var(--paper); font-weight: 600; }
        main { max-width: 880px; margin: 0 auto; padding: 32px 24px 80px; }
        /* Dashboard pages contain a .dash-shell, which is meant to fill the
           available width (sidebar + panels) rather than sit in a narrow
           centered column like plain content pages (forms, login, etc). */
        main:has(.dash-shell) { max-width: 1400px; }
        h1, h2, h3 { font-family: 'Iowan Old Style', Georgia, serif; color: var(--ink); }
        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 20px 22px;
            margin-bottom: 16px;
        }
        .btn {
            display: inline-block;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn:hover { background: var(--accent-dark); }
        .btn.secondary { background: transparent; color: var(--accent); border: 1px solid var(--accent); }
        .btn.warn { background: var(--warn); }
        input, textarea, select {
            width: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 10px 12px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            margin-bottom: 12px;
            font-size: 14px;
        }
        label { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; color: var(--slate); display:block; margin-bottom: 4px; }
        .eyebrow { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; text-transform: uppercase; letter-spacing: .08em; font-size: 12px; color: var(--accent); font-weight: 600; }
        .muted { color: var(--slate); font-size: 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .flag { color: var(--warn); font-weight: 600; }
        .error { color: var(--warn); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 13px; margin-top: -6px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); }

        /* --- Dashboard helpers shared across student/lecturer/admin views --- */
        .subnav {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
            margin: 18px 0 26px;
            border-bottom: 1px solid var(--line);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .subnav a {
            padding: 9px 14px;
            font-size: 13.5px;
            color: var(--slate);
            text-decoration: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
        }
        .subnav a:hover { color: var(--ink); }
        .subnav a.active { color: var(--ink); border-bottom-color: var(--accent); font-weight: 600; }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        .stat-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 14px 16px;
        }
        .stat-card .value { font-size: 26px; font-weight: 700; color: var(--ink); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .stat-card .label { font-size: 12px; color: var(--slate); text-transform: uppercase; letter-spacing: .04em; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .section-title { display: flex; align-items: center; justify-content: space-between; margin-top: 30px; }
        .badge {
            display: inline-block;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 2px 8px;
            border-radius: 10px;
            background: #eef2f1;
            color: var(--accent-dark);
        }
        .badge.role-administrator { background: #fbe7e0; color: var(--warn); }
        .badge.role-lecturer { background: #e2ecfa; color: #2a5a9c; }
        .badge.role-student { background: #eef2f1; color: var(--accent-dark); }
        .empty-state { color: var(--slate); font-size: 14px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 10px 0; }

        /* --- Sidebar dashboard shell (WhatsApp-style: nav list left, one panel at a time on the right) --- */
        .dash-shell {
            display: flex;
            margin-top: 18px;
            min-height: 70vh;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            overflow: hidden;
            background: #fff;
        }
        .dash-sidebar {
            width: 240px;
            flex-shrink: 0;
            background: #f7f5f2;
            border-right: 1px solid var(--line);
            padding: 10px 0;
            overflow-y: auto;
        }
        .dash-sidebar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 18px;
            cursor: pointer;
            font-size: 14px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--slate);
            border-left: 3px solid transparent;
            text-decoration: none;
        }
        .dash-sidebar-item:hover { background: #eef2f1; color: var(--ink); }
        .dash-sidebar-item.active { background: #e5efe9; color: var(--ink); font-weight: 600; border-left-color: var(--accent); }
        .dash-sidebar-item .icon { font-size: 17px; width: 20px; text-align: center; flex-shrink: 0; }
        .dash-main { flex: 1; padding: 22px 28px; overflow-y: auto; }
        .dash-panel { display: none; }
        .dash-panel.active { display: block; }
        @media (max-width: 760px) {
            .dash-shell { flex-direction: column; }
            .dash-sidebar { width: 100%; display: flex; overflow-x: auto; border-right: none; border-bottom: 1px solid var(--line); padding: 6px; }
            .dash-sidebar-item { border-left: none; border-bottom: 3px solid transparent; flex-shrink: 0; padding: 10px 14px; }
            .dash-sidebar-item.active { border-left-color: transparent; border-bottom-color: var(--accent); }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="brand">Smart Discussion Forum</div>
        <nav>
            <a href="/dashboard" class="{{ request()->is('dashboard') || request()->is('dashboard/student') || request()->is('dashboard/lecturer') ? 'active' : '' }}">Dashboard</a>
            <a href="/dashboard/admin" data-nav-admin-only style="display:none;" class="{{ request()->is('dashboard/admin') ? 'active' : '' }}">Admin Overview</a>
            <a href="/dashboard/admin/users" data-nav-admin-only style="display:none;" class="{{ request()->is('dashboard/admin/users') ? 'active' : '' }}">Manage Users</a>
            <a href="/profile" class="{{ request()->is('profile') ? 'active' : '' }}">My Profile</a>
            <a href="#" id="logoutLink">Log out</a>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
    <script>
        // Every API call attaches the bearer token stored at login.
        const apiToken = localStorage.getItem('sdf_token');
        async function api(path, options = {}) {
            const res = await fetch('/api' + path, {
                ...options,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...(apiToken ? { Authorization: 'Bearer ' + apiToken } : {}),
                    ...(options.headers || {}),
                },
                body: options.body ? JSON.stringify(options.body) : undefined,
            });
            if (res.status === 401) { window.location = '/'; return; }
            return res.json();
        }
        document.getElementById('logoutLink')?.addEventListener('click', async (e) => {
            e.preventDefault();
            await api('/logout', { method: 'POST' });
            localStorage.removeItem('sdf_token');
            window.location = '/';
        });

        // Shared current-user/role lookup. Every dashboard page calls this
        // once on load rather than re-implementing its own /me + role logic.
        // NOTE: this only decides what the *page* shows - the real
        // enforcement always happens server-side (role:* middleware / the
        // per-group authorization check in StatisticsController). Hiding a
        // nav link or section here is a UX convenience, not a security
        // boundary, exactly like the rest of this app's API calls.
        window.CURRENT_USER = null;
        window.CURRENT_ROLE = 'student'; // 'student' | 'lecturer' | 'administrator'

        async function loadCurrentUser() {
            const me = await api('/me');
            if (!me) return null;
            window.CURRENT_USER = me;

            const roleNames = (me.roles || []).map(r => r.role_name);
            window.CURRENT_ROLE = roleNames.includes('Administrator') ? 'administrator'
                : roleNames.includes('Lecturer') ? 'lecturer'
                : 'student';

            document.querySelectorAll('[data-nav-admin-only]').forEach(el => {
                el.style.display = window.CURRENT_ROLE === 'administrator' ? '' : 'none';
            });

            return me;
        }
        // Wires up a WhatsApp-style sidebar: clicking a .dash-sidebar-item
        // with a data-target shows the matching .dash-panel and hides the
        // rest. The first item with a data-target is active by default.
        // Sidebar items without data-target (plain links, e.g. "Manage
        // Users") are left alone and just navigate normally.
        function initDashSidebar(root = document) {
            const items = Array.from(root.querySelectorAll('.dash-sidebar-item[data-target]'));
            const panels = Array.from(root.querySelectorAll('.dash-panel'));

            function activate(targetId) {
                items.forEach(i => i.classList.toggle('active', i.dataset.target === targetId));
                panels.forEach(p => p.classList.toggle('active', p.id === targetId));
            }

            items.forEach(item => {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    activate(item.dataset.target);
                });
            });

            if (items.length) activate(items[0].dataset.target);
        }
    </script>
    @yield('scripts')
</body>
</html>