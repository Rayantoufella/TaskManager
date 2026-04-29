@php
    use Carbon\Carbon;

    $today = Carbon::now();
    setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'French_France', 'fra');
    Carbon::setLocale('fr');
    $dateStr = $today->translatedFormat('l j F Y');

    $total = $tasks->count();
    $todoCount = $tasks->where('status', 'todo')->count();
    $inProgressCount = $tasks->where('status', 'in_progress')->count();
    $doneCount = $tasks->where('status', 'done')->count();

    $lateTasks = $tasks->filter(fn($t) => $t->due_date && $t->status !== 'done' && Carbon::parse($t->due_date)->lt(Carbon::today()));

    $progressPct = $total > 0 ? round(($inProgressCount / $total) * 100) : 0;

    $recentTasks = $tasks->sortByDesc('created_at')->take(5);

    // Category color map (Fresh Mint palette)
    $catColors = [
        'Travail'        => ['bg' => '#CCFBF1', 'color' => '#14B8A6'],
        'Personnel'      => ['bg' => '#FCE7F3', 'color' => '#F472B6'],
        'Urgent'         => ['bg' => '#FFE4E6', 'color' => '#F43F5E'],
        'Idées'          => ['bg' => '#CFFAFE', 'color' => '#06B6D4'],
        'Apprentissage'  => ['bg' => '#F3E8FF', 'color' => '#A855F7'],
    ];
    $defaultCat = ['bg' => '#CCFBF1', 'color' => '#14B8A6'];

    $statusMap = [
        'todo'        => ['class' => 'badge-todo', 'label' => 'À faire'],
        'in_progress' => ['class' => 'badge-inprogress', 'label' => 'En cours'],
        'done'        => ['class' => 'badge-done', 'label' => 'Terminé'],
    ];

    // Sample weekly data — bars in % (could be wired to real data later)
    $weekBars = [42, 68, 55, 82, 47, 90, 63];
    $weekDays = ['L', 'M', 'M', 'J', 'V', 'S', 'D'];
    $bestIndex = array_search(max($weekBars), $weekBars);
    $bestDayLabels = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
@endphp

<x-app-layout>
    <div class="fade-in">
        {{-- ── Header ── --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Bonjour {{ Auth::user()->name }} 👋</h1>
                <p class="page-subtitle">{{ $dateStr }}</p>
            </div>
            <a href="{{ route('tasks.create') }}" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Nouvelle tâche
            </a>
        </div>

        {{-- ── Stat cards ── --}}
        <div class="stat-grid">
            {{-- Total --}}
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: linear-gradient(135deg,#A855F7,#C084FC); box-shadow: 0 4px 12px rgba(168,85,247,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </div>
                <div class="stat-value">{{ $total }}</div>
                <div class="stat-label">Total tâches</div>
                <div class="stat-trend" style="color: #A855F7;">+3 cette semaine</div>
            </div>

            {{-- À faire --}}
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: linear-gradient(135deg,#0EA5E9,#38BDF8); box-shadow: 0 4px 12px rgba(14,165,233,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="stat-value">{{ $todoCount }}</div>
                <div class="stat-label">À faire</div>
                <div class="stat-trend" style="color: #0EA5E9;">+12% cette semaine</div>
            </div>

            {{-- En cours --}}
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: linear-gradient(135deg,#F59E0B,#FBBF24); box-shadow: 0 4px 12px rgba(245,158,11,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                </div>
                <div class="stat-value">{{ $inProgressCount }}</div>
                <div class="stat-label">En cours</div>
                <div class="progress-bar" style="background: #FEF3C7;">
                    <div class="progress-fill" style="width: {{ $progressPct }}%;"></div>
                </div>
                <div class="stat-trend" style="color: #F59E0B; margin-top: 4px;">{{ $progressPct }}% en progression</div>
            </div>

            {{-- Terminées --}}
            <div class="stat-card">
                <div class="stat-icon-wrap" style="background: linear-gradient(135deg,#10B981,#14B8A6); box-shadow: 0 4px 12px rgba(16,185,129,0.2);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="stat-value">{{ $doneCount }}</div>
                <div class="stat-label">Terminées</div>
                <div class="stat-trend" style="color: #10B981;">✓ Bien joué !</div>
            </div>
        </div>

        {{-- ── Late tasks ── --}}
        @if($lateTasks->count() > 0)
            <div class="late-block">
                <div class="late-header">
                    <span class="late-icon-wrap">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </span>
                    <div>
                        <h3 class="late-title">⚠️ Tâches en retard</h3>
                        <p class="late-sub">{{ $lateTasks->count() }} tâche{{ $lateTasks->count() > 1 ? 's' : '' }} nécessite{{ $lateTasks->count() > 1 ? 'nt' : '' }} votre attention</p>
                    </div>
                </div>
                @foreach($lateTasks as $task)
                    @php
                        $catName = $task->category?->name ?? 'Travail';
                        $catColor = $catColors[$catName] ?? $defaultCat;
                    @endphp
                    <div class="late-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div style="flex: 1;">
                            <div class="late-task-title">{{ $task->title }}</div>
                            <div class="late-task-due">Échéance : {{ Carbon::parse($task->due_date)->translatedFormat('d/m/Y') }}</div>
                        </div>
                        <span class="cat-badge" style="background: {{ $catColor['bg'] }}; color: {{ $catColor['color'] }};">{{ $catName }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- ── Bottom grid ── --}}
        <div class="bottom-grid">
            {{-- Recent tasks --}}
            <div class="panel">
                <div class="panel-header">
                    <h3 class="panel-title">📋 Tâches récentes</h3>
                    <a href="{{ route('tasks.index') }}" class="link-btn">Voir tout →</a>
                </div>
                <div class="recent-list">
                    @forelse($recentTasks as $task)
                        @php
                            $catName = $task->category?->name ?? 'Travail';
                            $catColor = $catColors[$catName] ?? $defaultCat;
                            $st = $statusMap[$task->status] ?? $statusMap['todo'];
                        @endphp
                        <div class="recent-row">
                            <div class="recent-icon-wrap" style="background: {{ $catColor['bg'] }};">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="{{ $catColor['color'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                            </div>
                            <div class="recent-info">
                                <div class="recent-title">{{ $task->title }}</div>
                                <div class="recent-cat">{{ $catName }}</div>
                            </div>
                            <span class="badge {{ $st['class'] }}">
                                <span class="badge-dot"></span>
                                {{ $st['label'] }}
                            </span>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 32px 16px; color: var(--text3); font-size: 13px;">
                            Aucune tâche récente
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Weekly chart --}}
            <div class="panel">
                <h3 class="panel-title">Activité hebdomadaire</h3>
                <p class="chart-sub">Tâches complétées cette semaine</p>
                <div class="chart-bars">
                    @foreach($weekBars as $i => $v)
                        <div class="chart-col">
                            <div class="chart-bar {{ $i === $bestIndex ? 'best' : '' }}" style="height: {{ $v }}%;"></div>
                            <span class="chart-day {{ $i === $bestIndex ? 'best' : '' }}">{{ $weekDays[$i] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="chart-footer">
                    Meilleur jour: <strong>{{ $bestDayLabels[$bestIndex] }}</strong> — {{ max($weekBars) }}% accompli
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
