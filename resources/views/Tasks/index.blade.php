@php
    use Carbon\Carbon;
    Carbon::setLocale('fr');

    $catColors = [
        'Travail'        => ['bg' => '#CCFBF1', 'color' => '#14B8A6'],
        'Personnel'      => ['bg' => '#FCE7F3', 'color' => '#F472B6'],
        'Urgent'         => ['bg' => '#FFE4E6', 'color' => '#F43F5E'],
        'Idées'          => ['bg' => '#CFFAFE', 'color' => '#06B6D4'],
        'Apprentissage'  => ['bg' => '#F3E8FF', 'color' => '#A855F7'],
    ];
    $defaultCat = ['bg' => '#CCFBF1', 'color' => '#14B8A6'];

    $statusMap = [
        'todo'        => ['class' => 'badge-todo',       'label' => 'À faire'],
        'in_progress' => ['class' => 'badge-inprogress', 'label' => 'En cours'],
        'done'        => ['class' => 'badge-done',       'label' => 'Terminé'],
        'late'        => ['class' => 'badge-late',       'label' => 'En retard'],
    ];

    $statusFilters = [
        'all'         => 'Toutes',
        'todo'        => 'À faire',
        'in_progress' => 'En cours',
        'done'        => 'Terminé',
        'late'        => 'En retard',
    ];

    $activeStatus = request('status', 'all');
    $activeCat    = request('cat', 'all');
    $search       = trim((string) request('q', ''));

    $allTasks = \App\Models\Task::with('category')
        ->where('user_id', Auth::id())
        ->orderByDesc('created_at')
        ->get();

    $isLate = fn($t) => $t->due_date && $t->status !== 'done' && Carbon::parse($t->due_date)->lt(Carbon::today());

    $counts = [
        'all'         => $allTasks->count(),
        'todo'        => $allTasks->where('status', 'todo')->count(),
        'in_progress' => $allTasks->where('status', 'in_progress')->count(),
        'done'        => $allTasks->where('status', 'done')->count(),
        'late'        => $allTasks->filter($isLate)->count(),
    ];

    $tasks = $allTasks->filter(function ($t) use ($activeStatus, $activeCat, $search, $isLate) {
        if ($activeStatus === 'late') {
            if (!$isLate($t)) return false;
        } elseif ($activeStatus !== 'all' && $t->status !== $activeStatus) {
            return false;
        }
        if ($activeCat !== 'all' && (string)($t->category_id) !== (string)$activeCat) return false;
        if ($search !== '') {
            $hay = mb_strtolower(($t->title ?? '') . ' ' . ($t->description ?? ''));
            if (!str_contains($hay, mb_strtolower($search))) return false;
        }
        return true;
    })->values();

    $categories = \App\Models\Category::orderBy('name')->get();
@endphp

<x-app-layout>
    <style>
        /* ── Tasks page — Fresh Mint, animated ── */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes cardPop  { from { opacity: 0; transform: translateY(18px) scale(.97); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes floatY   { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }
        @keyframes pulseRing{ 0% { box-shadow: 0 0 0 0 rgba(239,68,68,.45);} 70% { box-shadow: 0 0 0 10px rgba(239,68,68,0);} 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0);} }
        @keyframes shimmer  { 0% { background-position: -240px 0; } 100% { background-position: 240px 0; } }
        @keyframes gradShift{ 0%,100%{ background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes glowPulse{ 0%,100% { box-shadow: 0 0 12px rgba(16,185,129,0.25); } 50% { box-shadow: 0 0 28px rgba(16,185,129,0.55); } }
        @keyframes wiggleIn { 0% { opacity: 0; transform: translateY(-6px) rotate(-2deg);} 100% { opacity: 1; transform: translateY(0) rotate(0);} }

        .tasks-wrap { animation: fadeInUp .35s ease both; }

        .tasks-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; gap: 16px; }
        .tasks-title {
            font-size: 28px; font-weight: 800; color: var(--text);
            background: linear-gradient(90deg,#064E3B,#10B981,#14B8A6,#064E3B);
            background-size: 300% 100%;
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
            animation: gradShift 6s ease infinite;
        }
        .tasks-sub { font-size: 14px; color: var(--text3); margin-top: 4px; }
        .tasks-cta { animation: glowPulse 2.6s ease-in-out infinite; }

        .filters-bar {
            background: #fff; border-radius: var(--radius);
            padding: 16px 20px; margin-bottom: 24px;
            box-shadow: var(--shadow); border: 1px solid var(--border);
            display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        }
        .filter-pills { display: flex; gap: 8px; flex-wrap: wrap; }
        .pill {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 999px; border: none;
            font-size: 12px; font-weight: 600; cursor: pointer;
            background: var(--bg); color: var(--text2);
            transition: all .18s ease; text-decoration: none;
        }
        .pill:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,185,129,0.12); }
        .pill .pill-count {
            padding: 1px 6px; border-radius: 999px; font-size: 10px;
            background: rgba(0,0,0,0.07);
        }
        .pill.active.pill-all       { background: var(--grad); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,0.35); }
        .pill.active.pill-todo      { background: var(--todo-bg);       color: var(--todo-color); }
        .pill.active.pill-inprogress{ background: var(--inprogress-bg); color: var(--inprogress-color); }
        .pill.active.pill-done      { background: var(--done-bg);       color: var(--done-color); }
        .pill.active.pill-late      { background: var(--late-bg);       color: var(--late-color); }
        .pill.active .pill-count    { background: rgba(255,255,255,0.55); }
        .pill.active.pill-todo .pill-count,
        .pill.active.pill-inprogress .pill-count,
        .pill.active.pill-done .pill-count,
        .pill.active.pill-late .pill-count { background: rgba(255,255,255,0.6); }

        .filters-right { margin-left: auto; display: flex; gap: 10px; align-items: center; }
        .cat-select {
            padding: 8px 12px; border-radius: 10px;
            border: 1.5px solid var(--border); font-size: 12px;
            color: var(--text2); background: #fff; outline: none; cursor: pointer;
        }
        .cat-select:focus { border-color: var(--emerald); }
        .search-mini { position: relative; }
        .search-mini svg {
            position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
        }
        .search-mini input {
            padding: 8px 12px 8px 34px; border-radius: 10px;
            border: 1.5px solid var(--border); font-size: 12px;
            color: var(--text); background: var(--bg); outline: none; width: 180px;
            transition: border-color .2s, box-shadow .2s;
        }
        .search-mini input:focus { border-color: var(--emerald); box-shadow: 0 0 0 3px rgba(16,185,129,0.12); }

        .tasks-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
        }
        @media (max-width: 1100px) { .tasks-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 720px)  { .tasks-grid { grid-template-columns: 1fr; } }

        .task-card {
            background: #fff; border-radius: var(--radius);
            padding: 20px; box-shadow: var(--shadow);
            border: 1.5px solid var(--border);
            display: flex; flex-direction: column; gap: 12px;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
            position: relative;
            animation: cardPop .45s cubic-bezier(.2,.8,.2,1) both;
        }
        .task-card.menu-open { z-index: 20; }
        .task-card::before {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(120deg, transparent 30%, rgba(16,185,129,0.08) 50%, transparent 70%);
            background-size: 240px 100%; background-repeat: no-repeat; background-position: -240px 0;
            opacity: 0; pointer-events: none; transition: opacity .2s;
            border-radius: var(--radius);
        }
        .task-card:hover::before { opacity: 1; animation: shimmer 1.2s ease forwards; }
        .task-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: #A7F3D0;
        }
        .task-card.is-late { border-color: #FECACA; }
        .task-card.is-late::after {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
            background: linear-gradient(180deg, #EF4444, #F87171);
        }

        .card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
        .card-title { font-size: 15px; font-weight: 700; color: var(--text); margin-bottom: 6px; line-height: 1.4; }
        .card-desc {
            font-size: 12px; color: var(--text3); line-height: 1.6;
            display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
        }
        .card-due { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--text3); }
        .card-due.late { color: var(--late-color); font-weight: 600; }
        .card-due .late-dot {
            width: 6px; height: 6px; border-radius: 50%; background: var(--late-color);
            display: inline-block; animation: pulseRing 1.4s ease-out infinite;
        }
        .card-created { font-size: 10px; color: var(--text3); }

        .card-actions {
            display: flex; align-items: center; gap: 8px;
            padding-top: 10px; border-top: 1px solid var(--border); margin-top: auto;
        }
        .btn-icon {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 6px 12px; border-radius: 8px;
            font-size: 11px; font-weight: 600; cursor: pointer;
            background: #fff; transition: all .18s ease; text-decoration: none;
        }
        .btn-icon.edit { color: var(--emerald); border: 1.5px solid var(--border); }
        .btn-icon.edit:hover { background: var(--done-bg); transform: translateY(-1px); }
        .btn-icon.del  { color: var(--late-color); border: 1.5px solid #FECACA; }
        .btn-icon.del:hover  { background: var(--late-bg); transform: translateY(-1px); }
        .btn-icon.del[type="submit"] { font-family: 'Inter', sans-serif; }

        .status-wrap { position: relative; margin-left: auto; }
        .status-toggle {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 6px 12px; border-radius: 8px;
            border: 1.5px solid var(--border); background: #fff;
            color: var(--text2); font-size: 11px; font-weight: 600; cursor: pointer;
            transition: all .18s;
        }
        .status-toggle .status-dot {
            width: 7px; height: 7px; border-radius: 50%; display: inline-block;
        }
        .status-toggle:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,185,129,0.15); }
        .status-toggle.is-todo        { background: var(--todo-bg);       color: var(--todo-color);       border-color: var(--todo-color); }
        .status-toggle.is-todo .status-dot        { background: var(--todo-color); }
        .status-toggle.is-in_progress { background: var(--inprogress-bg); color: var(--inprogress-color); border-color: var(--inprogress-color); }
        .status-toggle.is-in_progress .status-dot { background: var(--inprogress-color); }
        .status-toggle.is-done        { background: var(--done-bg);       color: var(--done-color);       border-color: var(--done-color); }
        .status-toggle.is-done .status-dot        { background: var(--done-color); }
        .status-toggle.is-late        { background: var(--late-bg);       color: var(--late-color);       border-color: var(--late-color); }
        .status-toggle.is-late .status-dot        { background: var(--late-color); }
        .status-menu {
            position: absolute; right: 0; bottom: calc(100% + 6px);
            background: #fff; border-radius: 12px;
            box-shadow: var(--shadow-hover); border: 1px solid var(--border);
            z-index: 50; min-width: 160px; overflow: hidden;
            animation: wiggleIn .18s ease both;
        }
        .status-menu button {
            display: block; width: 100%; padding: 9px 14px;
            background: none; border: none; cursor: pointer;
            text-align: left; font-size: 12px; font-weight: 500;
            border-bottom: 1px solid var(--border); transition: background .15s;
            font-family: 'Inter', sans-serif;
        }
        .status-menu button:last-child { border-bottom: none; }
        .status-menu button:hover { background: var(--bg); }
        .status-menu .opt-todo        { color: var(--todo-color); }
        .status-menu .opt-in_progress { color: var(--inprogress-color); }
        .status-menu .opt-done        { color: var(--done-color); }
        .status-menu form.is-current button { background: var(--bg); position: relative; padding-right: 28px; }
        .status-menu form.is-current button::after {
            content: '✓'; position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            font-weight: 800;
        }

        .empty-state { text-align: center; padding: 80px 20px; }
        .empty-leaf { font-size: 64px; margin-bottom: 16px; display: inline-block; animation: floatY 3s ease-in-out infinite; }
        .empty-title { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 8px; }
        .empty-sub { font-size: 14px; color: var(--text3); margin-bottom: 24px; }

        .toast {
            position: fixed; top: 24px; right: 24px; z-index: 200;
            background: #fff; border: 1.5px solid var(--border);
            border-left: 4px solid var(--emerald);
            border-radius: 12px; padding: 14px 18px;
            box-shadow: var(--shadow-hover);
            font-size: 13px; font-weight: 600; color: var(--text);
            animation: wiggleIn .25s ease both;
        }

        /* ── Modal (Create / Edit / Delete) ── */
        @keyframes backdropIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes modalIn { from { opacity: 0; transform: translateY(14px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes alertPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239,68,68,0.35); }
            50%      { transform: scale(1.06); box-shadow: 0 0 0 14px rgba(239,68,68,0); }
        }

        .modal-backdrop {
            position: fixed; inset: 0; z-index: 100;
            background: rgba(0,0,0,0.35);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
            display: none; align-items: center; justify-content: center;
            padding: 20px;
            animation: backdropIn .25s ease both;
        }
        .modal-backdrop.is-open { display: flex; }

        .modal-card {
            background: #fff; border-radius: 24px;
            padding: 36px; width: 100%; max-width: 520px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.15);
            max-height: 90vh; overflow-y: auto;
            animation: modalIn .25s cubic-bezier(.2,.8,.2,1) both;
        }
        .modal-card.is-confirm { max-width: 400px; text-align: center; }

        .modal-head {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px; gap: 12px;
        }
        .modal-title { font-size: 22px; font-weight: 800; color: var(--text); }
        .modal-sub { font-size: 13px; color: var(--text3); margin-top: 2px; }
        .modal-close {
            background: var(--bg); border: none; border-radius: 10px;
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: var(--text2); flex-shrink: 0;
            transition: background .15s;
        }
        .modal-close:hover { background: #E5E7EB; }

        .form-stack { display: flex; flex-direction: column; gap: 18px; }
        .form-label { font-size: 13px; font-weight: 600; color: var(--text); display: block; margin-bottom: 6px; }
        .form-input,
        .form-textarea,
        .form-select {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid var(--border); border-radius: 12px;
            font-size: 14px; color: var(--text); outline: none;
            font-family: 'Inter', sans-serif; background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            border-color: var(--emerald);
            box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
        }
        .form-textarea { resize: vertical; line-height: 1.6; min-height: 88px; }
        .form-select { cursor: pointer; }

        .status-pills { display: flex; gap: 8px; }
        .status-pill {
            flex: 1; padding: 10px; border-radius: 10px;
            border: 2px solid var(--border); background: #fff;
            color: var(--text3); font-size: 12px; font-weight: 600;
            cursor: pointer; transition: all .15s; font-family: 'Inter', sans-serif;
        }
        .status-pill[data-pill="todo"].is-active        { border-color: var(--todo-color);       background: var(--todo-bg);       color: var(--todo-color); }
        .status-pill[data-pill="in_progress"].is-active { border-color: var(--inprogress-color); background: var(--inprogress-bg); color: var(--inprogress-color); }
        .status-pill[data-pill="done"].is-active        { border-color: var(--done-color);       background: var(--done-bg);       color: var(--done-color); }

        .modal-actions { display: flex; gap: 12px; margin-top: 8px; }
        .btn-ghost {
            padding: 12px 20px; border-radius: 12px;
            background: var(--bg); border: 1.5px solid var(--border);
            color: var(--text2); font-size: 14px; font-weight: 600;
            cursor: pointer; transition: all .15s;
        }
        .btn-ghost:hover { background: #E5E7EB; color: var(--text); }
        .btn-danger {
            padding: 12px 20px; border-radius: 12px; border: none;
            background: #EF4444; color: #fff;
            font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all .2s;
            box-shadow: 0 4px 14px rgba(239,68,68,0.25);
        }
        .btn-danger:hover { box-shadow: 0 8px 22px rgba(239,68,68,0.4); transform: translateY(-1px); }

        .alert-icon-wrap {
            width: 64px; height: 64px; border-radius: 50%;
            background: var(--late-bg);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
            animation: alertPulse 1.5s ease infinite;
        }

        .form-error { font-size: 12px; color: var(--late-color); margin-top: 6px; font-weight: 500; }

        /* Stagger entrance */
        .task-card:nth-child(1)  { animation-delay: .02s; }
        .task-card:nth-child(2)  { animation-delay: .06s; }
        .task-card:nth-child(3)  { animation-delay: .10s; }
        .task-card:nth-child(4)  { animation-delay: .14s; }
        .task-card:nth-child(5)  { animation-delay: .18s; }
        .task-card:nth-child(6)  { animation-delay: .22s; }
        .task-card:nth-child(7)  { animation-delay: .26s; }
        .task-card:nth-child(8)  { animation-delay: .30s; }
        .task-card:nth-child(9)  { animation-delay: .34s; }
        .task-card:nth-child(n+10) { animation-delay: .38s; }
    </style>

    <div class="tasks-wrap">
        @if (session('success'))
            <div class="toast" id="flash-toast">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: -3px; margin-right: 6px;"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
            <script>setTimeout(() => document.getElementById('flash-toast')?.remove(), 2800);</script>
        @endif

        {{-- Header --}}
        <div class="tasks-header">
            <div>
                <h1 class="tasks-title">Mes tâches</h1>
                <p class="tasks-sub">Gérez votre productivité 🌿</p>
            </div>
            <button type="button" class="btn btn-primary tasks-cta" data-open-modal="create">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvelle tâche
            </button>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('tasks.index') }}" class="filters-bar" id="filters-form">
            <div class="filter-pills">
                @foreach ($statusFilters as $key => $label)
                    @php
                        $cls = match($key) {
                            'all'         => 'pill-all',
                            'todo'        => 'pill-todo',
                            'in_progress' => 'pill-inprogress',
                            'done'        => 'pill-done',
                            'late'        => 'pill-late',
                        };
                        $url = route('tasks.index', array_filter([
                            'status' => $key === 'all' ? null : $key,
                            'cat'    => $activeCat === 'all' ? null : $activeCat,
                            'q'      => $search !== '' ? $search : null,
                        ]));
                    @endphp
                    <a href="{{ $url }}" class="pill {{ $cls }} {{ $activeStatus === $key ? 'active' : '' }}">
                        {{ $label }}
                        <span class="pill-count">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </div>

            <div class="filters-right">
                <input type="hidden" name="status" value="{{ $activeStatus }}">
                <select name="cat" class="cat-select" onchange="document.getElementById('filters-form').submit()">
                    <option value="all">Catégorie : Toutes</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ (string)$activeCat === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <div class="search-mini">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher...">
                </div>
            </div>
        </form>

        {{-- Cards grid --}}
        @if ($tasks->isEmpty())
            <div class="empty-state">
                <div class="empty-leaf">🌿</div>
                <h3 class="empty-title">Aucune tâche trouvée</h3>
                <p class="empty-sub">Commencez par créer votre première tâche pour organiser votre journée</p>
                <button type="button" class="btn btn-primary" data-open-modal="create">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Créer une tâche
                </button>
            </div>
        @else
            <div class="tasks-grid">
                @foreach ($tasks as $task)
                    @php
                        $catName  = $task->category?->name ?? 'Travail';
                        $catColor = $catColors[$catName] ?? $defaultCat;
                        $late     = $isLate($task);
                        $sKey     = $late ? 'late' : $task->status;
                        $sCfg     = $statusMap[$sKey] ?? $statusMap['todo'];
                        $dueFmt   = $task->due_date
                            ? Carbon::parse($task->due_date)->translatedFormat('j M Y')
                            : null;
                        $createdFmt = Carbon::parse($task->created_at)->translatedFormat('j M');
                    @endphp
                    <article class="task-card {{ $late ? 'is-late' : '' }}">
                        <div class="card-top">
                            <span class="cat-badge" style="background: {{ $catColor['bg'] }}; color: {{ $catColor['color'] }};">
                                {{ $catName }}
                            </span>
                            <span class="badge {{ $sCfg['class'] }}">
                                <span class="badge-dot"></span>
                                {{ $sCfg['label'] }}
                            </span>
                        </div>

                        <div>
                            <h3 class="card-title">{{ $task->title }}</h3>
                            @if ($task->description)
                                <p class="card-desc">{{ $task->description }}</p>
                            @endif
                        </div>

                        @if ($dueFmt)
                            <div class="card-due {{ $late ? 'late' : '' }}">
                                @if ($late)
                                    <span class="late-dot"></span>
                                    <span>⚠️ En retard — {{ $dueFmt }}</span>
                                @else
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    <span>{{ $dueFmt }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="card-created">Créée le {{ $createdFmt }}</div>

                        <div class="card-actions">
                            <button type="button" class="btn-icon edit" title="Modifier"
                                    data-open-modal="edit"
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}"
                                    data-description="{{ $task->description }}"
                                    data-category="{{ $task->category_id }}"
                                    data-status="{{ $task->status }}"
                                    data-due="{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '' }}">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Modifier
                            </button>

                            <button type="button" class="btn-icon del" title="Supprimer"
                                    data-open-modal="delete"
                                    data-id="{{ $task->id }}"
                                    data-title="{{ $task->title }}">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                Supprimer
                            </button>

                            <div class="status-wrap" data-status-wrap>
                                <button type="button" class="status-toggle is-{{ $sKey }}" data-status-toggle>
                                    <span class="status-dot"></span>
                                    {{ $sCfg['label'] }}
                                    <span aria-hidden="true">▾</span>
                                </button>
                                <div class="status-menu" data-status-menu hidden>
                                    @foreach (['todo','in_progress','done'] as $opt)
                                        <form method="POST" action="{{ route('tasks.status', $task->id) }}" class="{{ $task->status === $opt ? 'is-current' : '' }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $opt }}">
                                            <button type="submit" class="opt-{{ $opt }}">
                                                {{ $statusMap[$opt]['label'] }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ── Modals: Create / Edit / Delete ── --}}
    @php
        $modalCategories = $categories->isEmpty() ? \App\Models\Category::orderBy('name')->get() : $categories;
        $oldModal = old('_modal');
    @endphp

    {{-- Create modal --}}
    <div class="modal-backdrop {{ $oldModal === 'create' ? 'is-open' : '' }}" id="modal-create" data-modal="create">
        <div class="modal-card">
            <div class="modal-head">
                <div>
                    <div class="modal-title">Nouvelle tâche</div>
                    <div class="modal-sub">Ajoutez une nouvelle tâche à votre liste</div>
                </div>
                <button type="button" class="modal-close" data-close-modal aria-label="Fermer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('tasks.store') }}" class="form-stack">
                @csrf
                <input type="hidden" name="_modal" value="create">
                <input type="hidden" name="status" value="{{ $oldModal === 'create' ? old('status', 'todo') : 'todo' }}" data-status-input>

                <div>
                    <label class="form-label" for="c_title">Titre de la tâche</label>
                    <input id="c_title" type="text" name="title" required
                           class="form-input"
                           placeholder="Ex: Préparer la présentation Q2"
                           value="{{ $oldModal === 'create' ? old('title') : '' }}">
                    @error('title') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="c_description">Description</label>
                    <textarea id="c_description" name="description" rows="3" class="form-textarea"
                              placeholder="Décrivez la tâche en détail...">{{ $oldModal === 'create' ? old('description') : '' }}</textarea>
                    @error('description') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="c_category">Catégorie</label>
                    <select id="c_category" name="category_id" class="form-select" required>
                        @foreach ($modalCategories as $c)
                            <option value="{{ $c->id }}"
                                {{ ($oldModal === 'create' ? old('category_id') : null) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Statut initial</label>
                    <div class="status-pills" data-status-pills>
                        @foreach (['todo' => 'À faire', 'in_progress' => 'En cours', 'done' => 'Terminé'] as $val => $label)
                            <button type="button" class="status-pill" data-pill="{{ $val }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="form-label" for="c_due">Date d'échéance</label>
                    <input id="c_due" type="date" name="due_date" class="form-input"
                           value="{{ $oldModal === 'create' ? old('due_date') : '' }}">
                    @error('due_date') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-ghost" style="flex:1;" data-close-modal>Annuler</button>
                    <button type="submit" class="btn btn-primary" style="flex:2;">Créer la tâche</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div class="modal-backdrop {{ $oldModal === 'edit' ? 'is-open' : '' }}" id="modal-edit" data-modal="edit">
        <div class="modal-card">
            <div class="modal-head">
                <div>
                    <div class="modal-title">Modifier la tâche</div>
                    <div class="modal-sub">Mettez à jour les informations de votre tâche</div>
                </div>
                <button type="button" class="modal-close" data-close-modal aria-label="Fermer">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ $oldModal === 'edit' && old('_task_id') ? route('tasks.update', old('_task_id')) : '' }}"
                  class="form-stack" data-edit-form>
                @csrf
                @method('PUT')
                <input type="hidden" name="_modal" value="edit">
                <input type="hidden" name="_task_id" value="{{ $oldModal === 'edit' ? old('_task_id') : '' }}" data-task-id>
                <input type="hidden" name="status" value="{{ $oldModal === 'edit' ? old('status', 'todo') : 'todo' }}" data-status-input>

                <div>
                    <label class="form-label" for="e_title">Titre de la tâche</label>
                    <input id="e_title" type="text" name="title" required
                           class="form-input"
                           placeholder="Ex: Préparer la présentation Q2"
                           value="{{ $oldModal === 'edit' ? old('title') : '' }}">
                    @error('title') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="e_description">Description</label>
                    <textarea id="e_description" name="description" rows="3" class="form-textarea"
                              placeholder="Décrivez la tâche en détail...">{{ $oldModal === 'edit' ? old('description') : '' }}</textarea>
                    @error('description') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label" for="e_category">Catégorie</label>
                    <select id="e_category" name="category_id" class="form-select" required>
                        @foreach ($modalCategories as $c)
                            <option value="{{ $c->id }}"
                                {{ ($oldModal === 'edit' ? old('category_id') : null) == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="form-label">Statut</label>
                    <div class="status-pills" data-status-pills>
                        @foreach (['todo' => 'À faire', 'in_progress' => 'En cours', 'done' => 'Terminé'] as $val => $label)
                            <button type="button" class="status-pill" data-pill="{{ $val }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="form-label" for="e_due">Date d'échéance</label>
                    <input id="e_due" type="date" name="due_date" class="form-input"
                           value="{{ $oldModal === 'edit' ? old('due_date') : '' }}">
                    @error('due_date') <div class="form-error">{{ $message }}</div> @enderror
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-ghost" style="flex:1;" data-close-modal>Annuler</button>
                    <button type="submit" class="btn btn-primary" style="flex:2;">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete modal --}}
    <div class="modal-backdrop" id="modal-delete" data-modal="delete">
        <div class="modal-card is-confirm">
            <div class="alert-icon-wrap">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h2 style="font-size:20px; font-weight:800; color:var(--text); margin-bottom:8px;">Supprimer cette tâche ?</h2>
            <p style="font-size:13px; color:var(--text2); line-height:1.6; margin-bottom:8px;">Cette action est irréversible.</p>
            <p style="font-size:13px; color:var(--text3); line-height:1.6; margin-bottom:24px;">
                La tâche <strong style="color:var(--text);" data-delete-title>"…"</strong> sera définitivement supprimée.
            </p>
            <form method="POST" action="" data-delete-form>
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="button" class="btn-ghost" style="flex:1;" data-close-modal>Annuler</button>
                    <button type="submit" class="btn-danger" style="flex:1;">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const backdrops = document.querySelectorAll('.modal-backdrop');
            const editForm  = document.querySelector('[data-edit-form]');
            const editFormBaseUrl = "{{ url('tasks') }}";
            const deleteForm = document.querySelector('[data-delete-form]');
            const deleteTitleEl = document.querySelector('[data-delete-title]');

            function openModal(name) {
                const target = document.getElementById('modal-' + name);
                if (!target) return;
                target.classList.add('is-open');
                document.body.style.overflow = 'hidden';
                const focusEl = target.querySelector('input:not([type=hidden]), textarea, select, button');
                if (focusEl) setTimeout(() => focusEl.focus(), 50);
            }
            function closeAll() {
                backdrops.forEach(b => b.classList.remove('is-open'));
                document.body.style.overflow = '';
            }

            function setStatusPills(modalEl, value) {
                const pills = modalEl.querySelectorAll('[data-status-pills] .status-pill');
                pills.forEach(p => p.classList.toggle('is-active', p.dataset.pill === value));
                const input = modalEl.querySelector('[data-status-input]');
                if (input) input.value = value;
            }

            // Status pill click handlers
            document.querySelectorAll('.modal-backdrop').forEach(modal => {
                modal.querySelectorAll('[data-status-pills] .status-pill').forEach(pill => {
                    pill.addEventListener('click', () => setStatusPills(modal, pill.dataset.pill));
                });
                // Initialize from hidden input
                const input = modal.querySelector('[data-status-input]');
                if (input) setStatusPills(modal, input.value || 'todo');
            });

            // Open create
            document.querySelectorAll('[data-open-modal="create"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById('modal-create');
                    const form = modal.querySelector('form');
                    form.reset();
                    setStatusPills(modal, 'todo');
                    openModal('create');
                });
            });

            // Open edit (prefill from data attributes)
            document.querySelectorAll('[data-open-modal="edit"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById('modal-edit');
                    const id     = btn.dataset.id;
                    modal.querySelector('[data-task-id]').value = id;
                    editForm.action = editFormBaseUrl + '/' + id;
                    modal.querySelector('#e_title').value       = btn.dataset.title || '';
                    modal.querySelector('#e_description').value = btn.dataset.description || '';
                    modal.querySelector('#e_category').value    = btn.dataset.category || '';
                    modal.querySelector('#e_due').value         = btn.dataset.due || '';
                    setStatusPills(modal, btn.dataset.status || 'todo');
                    openModal('edit');
                });
            });

            // Open delete
            document.querySelectorAll('[data-open-modal="delete"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const id    = btn.dataset.id;
                    const title = btn.dataset.title || '';
                    deleteForm.action = editFormBaseUrl + '/' + id;
                    deleteTitleEl.textContent = '"' + title + '"';
                    openModal('delete');
                });
            });

            // Close handlers
            document.querySelectorAll('[data-close-modal]').forEach(el => {
                el.addEventListener('click', closeAll);
            });
            backdrops.forEach(b => {
                b.addEventListener('click', e => { if (e.target === b) closeAll(); });
            });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAll(); });

            // If validation error reopened a modal, lock body scroll
            const reopened = document.querySelector('.modal-backdrop.is-open');
            if (reopened) {
                document.body.style.overflow = 'hidden';
                @if($oldModal === 'edit' && old('_task_id'))
                    editForm.action = editFormBaseUrl + '/' + "{{ old('_task_id') }}";
                @endif
            }
        })();

        document.querySelectorAll('[data-status-wrap]').forEach(wrap => {
            const toggle = wrap.querySelector('[data-status-toggle]');
            const menu   = wrap.querySelector('[data-status-menu]');
            const card   = wrap.closest('.task-card');
            toggle.addEventListener('click', e => {
                e.stopPropagation();
                document.querySelectorAll('[data-status-menu]').forEach(m => { if (m !== menu) m.hidden = true; });
                document.querySelectorAll('.task-card.menu-open').forEach(c => { if (c !== card) c.classList.remove('menu-open'); });
                menu.hidden = !menu.hidden;
                if (card) card.classList.toggle('menu-open', !menu.hidden);
            });
        });
        document.addEventListener('click', () => {
            document.querySelectorAll('[data-status-menu]').forEach(m => m.hidden = true);
            document.querySelectorAll('.task-card.menu-open').forEach(c => c.classList.remove('menu-open'));
        });
    </script>
</x-app-layout>
