<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TaskFlow') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --emerald:#10B981; --teal:#14B8A6; --violet:#A855F7; --mint:#6EE7B7;
            --bg:#F0FDF4; --card:#FFFFFF; --border:#D1FAE5;
            --text:#064E3B; --text2:#6B7280; --text3:#9CA3AF;
            --todo-bg:#E0F2FE; --todo-color:#0EA5E9;
            --inprogress-bg:#FEF3C7; --inprogress-color:#F59E0B;
            --done-bg:#D1FAE5; --done-color:#10B981;
            --late-bg:#FEE2E2; --late-color:#EF4444;
            --grad: linear-gradient(135deg,#10B981,#14B8A6);
            --grad-accent: linear-gradient(135deg,#A855F7,#C084FC);
            --shadow: 0 4px 20px rgba(16,185,129,0.08), 0 1px 4px rgba(16,185,129,0.06);
            --shadow-hover: 0 8px 32px rgba(16,185,129,0.18), 0 2px 8px rgba(16,185,129,0.1);
            --radius: 16px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }
        input, textarea, select { font-family: 'Inter', sans-serif; }
        button { cursor: pointer; font-family: 'Inter', sans-serif; }

        @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeInUp .3s ease both; }

        /* ── App shell ── */
        .app-shell { display: flex; height: 100vh; background: var(--bg); overflow: hidden; }

        /* ── Sidebar ── */
        .sidebar {
            width: 240px; background: #fff; border-right: 1.5px solid var(--border);
            display: flex; flex-direction: column; flex-shrink: 0;
        }
        .sidebar-header { padding: 28px 20px 20px; }
        .sidebar-logo {
            font-size: 24px; font-weight: 800;
            background: var(--grad);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.03em;
        }
        .sidebar-workspace {
            font-size: 10px; color: var(--text3);
            margin-top: 2px; letter-spacing: 0.08em;
        }
        .sidebar-nav {
            flex: 1; padding: 0 12px;
            display: flex; flex-direction: column; gap: 4px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 12px; border-radius: 12px; border: none;
            background: transparent; color: var(--text2);
            font-weight: 500; font-size: 14px; transition: all .15s;
            text-align: left; width: 100%; text-decoration: none;
        }
        .nav-item:hover { background: #F9FAFB; }
        .nav-item.active {
            background: var(--done-bg); color: var(--emerald); font-weight: 600;
        }
        .nav-item.active svg { stroke: var(--emerald); }
        .nav-item svg { stroke: var(--text3); flex-shrink: 0; }
        .nav-badge {
            margin-left: auto; background: var(--emerald); color: #fff;
            border-radius: 999px; font-size: 10px; font-weight: 700; padding: 1px 7px;
        }
        .sidebar-user {
            padding: 16px; border-top: 1.5px solid var(--border);
        }
        .user-row {
            display: flex; align-items: center; gap: 10px; margin-bottom: 12px;
        }
        .avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--grad);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 14px; flex-shrink: 0;
        }
        .user-name { font-size: 13px; font-weight: 600; color: var(--text); }
        .user-role { font-size: 11px; color: var(--text3); }
        .logout-btn {
            display: flex; align-items: center; gap: 8px;
            background: none; border: 1.5px solid #E5E7EB; border-radius: 10px;
            padding: 8px 12px; color: var(--text2);
            font-size: 12px; font-weight: 500; width: 100%;
        }
        .logout-btn:hover { background: #F9FAFB; }

        /* ── Main area ── */
        .app-main {
            flex: 1; display: flex; flex-direction: column; overflow: hidden;
        }
        .topbar {
            background: #fff; border-bottom: 1.5px solid var(--border);
            padding: 0 28px; height: 64px;
            display: flex; align-items: center; gap: 16px; flex-shrink: 0;
        }
        .search-wrap { flex: 1; position: relative; }
        .search-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--text3);
        }
        .search-input {
            width: 100%; max-width: 360px;
            padding: 9px 14px 9px 42px;
            border: 1.5px solid var(--border); border-radius: 12px;
            font-size: 13px; color: var(--text); outline: none;
            background: var(--bg); transition: border-color .2s;
        }
        .search-input:focus { border-color: var(--emerald); }
        .bell-wrap { position: relative; cursor: pointer; }
        .bell-badge {
            position: absolute; top: -4px; right: -4px;
            width: 16px; height: 16px; border-radius: 50%;
            background: #EF4444; color: #fff;
            font-size: 9px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
        }
        .topbar-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--grad);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 14px; cursor: pointer;
        }
        .app-content {
            flex: 1; overflow-y: auto; padding: 32px;
        }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 8px; border: none; border-radius: 12px;
            font-weight: 600; font-size: 14px; cursor: pointer;
            transition: all .2s ease; outline: none; text-decoration: none;
        }
        .btn-primary {
            background: var(--grad); color: #fff;
            padding: 12px 24px;
            box-shadow: 0 0 12px rgba(16,185,129,0.25);
        }
        .btn-primary:hover {
            box-shadow: 0 0 24px rgba(16,185,129,0.45);
            transform: translateY(-1px);
        }

        /* ── Dashboard ── */
        .page-header {
            display: flex; align-items: flex-start;
            justify-content: space-between; margin-bottom: 32px;
        }
        .page-title { font-size: 30px; font-weight: 800; color: var(--text); margin-bottom: 4px; }
        .page-subtitle { font-size: 14px; color: var(--text3); text-transform: capitalize; }

        /* Stat cards */
        .stat-grid {
            display: grid; grid-template-columns: repeat(4, 1fr);
            gap: 20px; margin-bottom: 32px;
        }
        .stat-card {
            background: #fff; border-radius: var(--radius); padding: 22px;
            box-shadow: var(--shadow); border: 1.5px solid var(--border);
            transition: all .2s;
        }
        .stat-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }
        .stat-icon-wrap {
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
        }
        .stat-value {
            font-size: 36px; font-weight: 800; color: var(--text);
            line-height: 1; margin-bottom: 4px;
        }
        .stat-label {
            font-size: 13px; color: var(--text2); font-weight: 500; margin-bottom: 8px;
        }
        .stat-trend { font-size: 11px; font-weight: 600; }
        .progress-bar {
            height: 4px; border-radius: 999px; overflow: hidden;
        }
        .progress-fill {
            height: 100%; background: var(--grad); border-radius: 999px;
            transition: width 1s ease;
        }

        /* Late tasks alert */
        .late-block {
            background: #fff; border: 1.5px solid #FECACA;
            border-radius: var(--radius); padding: 24px; margin-bottom: 24px;
        }
        .late-header {
            display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
        }
        .late-icon-wrap {
            width: 32px; height: 32px; border-radius: 10px;
            background: var(--late-bg);
            display: flex; align-items: center; justify-content: center;
        }
        .late-title { font-size: 15px; font-weight: 700; color: #B91C1C; }
        .late-sub { font-size: 12px; color: var(--text3); }
        .late-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; background: var(--late-bg);
            border-radius: 10px; margin-bottom: 8px;
            border: 1px solid #FECACA;
        }
        .late-row:last-child { margin-bottom: 0; }
        .late-task-title { font-size: 13px; font-weight: 600; color: #991B1B; }
        .late-task-due { font-size: 11px; color: var(--late-color); }

        /* Bottom grid */
        .bottom-grid {
            display: grid; grid-template-columns: 1fr 320px; gap: 24px;
        }
        .panel {
            background: #fff; border-radius: var(--radius);
            padding: 24px; box-shadow: var(--shadow);
        }
        .panel-header {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px;
        }
        .panel-title { font-size: 16px; font-weight: 700; color: var(--text); }
        .link-btn {
            background: none; border: none; color: var(--emerald);
            font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .recent-list { display: flex; flex-direction: column; gap: 12px; }
        .recent-row {
            display: flex; align-items: center; gap: 14px;
            padding: 12px; background: var(--bg);
            border-radius: 12px; border: 1px solid var(--border);
        }
        .recent-icon-wrap {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .recent-info { flex: 1; min-width: 0; }
        .recent-title {
            font-size: 13px; font-weight: 600; color: var(--text);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .recent-cat { font-size: 11px; color: var(--text3); }

        /* Status badge */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 10px; border-radius: 999px;
            font-size: 11px; font-weight: 700; white-space: nowrap;
        }
        .badge-dot {
            width: 6px; height: 6px; border-radius: 50%;
            display: inline-block;
        }
        .badge-todo { background: var(--todo-bg); color: var(--todo-color); }
        .badge-todo .badge-dot { background: var(--todo-color); }
        .badge-inprogress { background: var(--inprogress-bg); color: var(--inprogress-color); }
        .badge-inprogress .badge-dot { background: var(--inprogress-color); }
        .badge-done { background: var(--done-bg); color: var(--done-color); }
        .badge-done .badge-dot { background: var(--done-color); }
        .badge-late { background: var(--late-bg); color: var(--late-color); }
        .badge-late .badge-dot { background: var(--late-color); }

        /* Cat badge */
        .cat-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 3px 8px; border-radius: 999px;
            font-size: 11px; font-weight: 600;
        }

        /* Weekly chart */
        .chart-sub { font-size: 12px; color: var(--text3); margin-bottom: 20px; }
        .chart-bars {
            display: flex; align-items: flex-end; gap: 10px;
            height: 120px; padding: 0 4px;
        }
        .chart-col {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; gap: 6px;
        }
        .chart-bar {
            width: 100%; border-radius: 8px;
            background: linear-gradient(180deg, #D1FAE5, #A7F3D0);
            min-height: 8px; transition: height .5s ease;
        }
        .chart-bar.best { background: var(--grad); }
        .chart-day { font-size: 10px; color: var(--text3); }
        .chart-day.best { color: var(--emerald); font-weight: 700; }
        .chart-footer {
            margin-top: 16px; padding: 12px;
            background: var(--bg); border-radius: 10px;
            border: 1px solid var(--border);
            font-size: 12px; color: var(--text2);
        }
        .chart-footer strong { color: var(--emerald); }
    </style>
</head>
<body>
<div class="app-shell">
    {{-- ── Sidebar ── --}}
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">TaskFlow</div>
            <div class="sidebar-workspace">WORKSPACE</div>
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Dashboard
            </a>
            <a href="{{ route('tasks.index') }}" class="nav-item {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Mes tâches
                @auth
                    @php $taskCount = \App\Models\Task::where('user_id', Auth::id())->count(); @endphp
                    @if($taskCount > 0)
                        <span class="nav-badge">{{ $taskCount }}</span>
                    @endif
                @endauth
            </a>

            <a href="#" class="nav-item">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Statistiques
            </a>
        </nav>
        <div class="sidebar-user">
            @auth
                <div class="user-row">
                    <div class="avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    <div>
                        <div class="user-name">{{ Auth::user()->name }}</div>
                        <div class="user-role">Product Designer</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Déconnexion
                    </button>
                </form>
            @endauth
        </div>
    </aside>

    {{-- ── Main ── --}}
    <div class="app-main">
        <header class="topbar">
            <div class="search-wrap">
                <span class="search-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
                <input class="search-input" placeholder="Rechercher une tâche...">
            </div>
            <div class="bell-wrap">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                <span class="bell-badge">3</span>
            </div>
            @auth
                <div class="topbar-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            @endauth
        </header>
        <main class="app-content">
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
