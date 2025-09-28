{{-- resources/views/arsip/arsip.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Arsip Project</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html { scrollbar-gutter: stable; }
    body { overflow-x: hidden; }
    .scroll-thin::-webkit-scrollbar{width:6px;height:6px}
    .scroll-thin::-webkit-scrollbar-thumb{background:#c89898;border-radius:9999px}
    .scroll-thin::-webkit-scrollbar-track{background:transparent}
  </style>
</head>
<body class="min-h-screen bg-[#F8ECEC] text-gray-900">
@php
  $user = auth()->user();
  $role = $user?->role; // 'digital_banking' | 'it' | 'supervisor'

  // Label yang rapi per role
  $roleLabel = match ($role) {
      'it'             => 'Developer',
      'digital_banking'=> 'DIG',
      'supervisor'     => 'Supervisor',
      default          => 'User',
  };

  // Tentukan rute "Beranda" sesuai role, dengan fallback aman
  if ($role === 'it' && \Illuminate\Support\Facades\Route::has('it.dashboard')) {
      $homeUrl = route('it.dashboard');
  } elseif ($role === 'supervisor' && \Illuminate\Support\Facades\Route::has('supervisor.dashboard')) {
      $homeUrl = route('supervisor.dashboard');
  } else {
      // default ke dashboard DIG bila ada, kalau tidak ya ke root
      $homeUrl = \Illuminate\Support\Facades\Route::has('dig.dashboard')
          ? route('dig.dashboard') : url('/');
  }

  // Progress: untuk supervisor arahkan ke dashboard supervisor jika ada
  if ($role === 'it' && \Illuminate\Support\Facades\Route::has('it.progresses')) {
      $progressUrl = route('it.progresses');
  } elseif ($role === 'supervisor' && \Illuminate\Support\Facades\Route::has('supervisor.dashboard')) {
      $progressUrl = route('supervisor.dashboard');
  } else {
      $progressUrl = \Illuminate\Support\Facades\Route::has('semua.progresses')
          ? route('semua.progresses')
          : ($homeUrl ?? url('/'));
  }

  // Notifikasi: masing-masing role kalau ada, kalau tidak fallback ke DIG
  if ($role === 'it' && \Illuminate\Support\Facades\Route::has('it.notifications')) {
      $notifUrl = route('it.notifications');
  } elseif ($role === 'supervisor' && \Illuminate\Support\Facades\Route::has('supervisor.notifications')) {
      $notifUrl = route('supervisor.notifications');
  } else {
      $notifUrl = \Illuminate\Support\Facades\Route::has('dig.notifications')
          ? route('dig.notifications') : url()->current();
  }

  // Arsip – pakai 'semua.arsip' bila ada, kalau tidak 'arsip.arsip', jika tidak ada ya current
  $arsipUrl = \Illuminate\Support\Facades\Route::has('semua.arsip')
      ? route('semua.arsip')
      : (\Illuminate\Support\Facades\Route::has('arsip.arsip') ? route('arsip.arsip') : url()->current());

  // Helper: menandai link aktif
  $isActive = function (string $url) {
      return url()->current() === $url ? 'font-semibold' : 'text-gray-600 hover:text-red-600';
  };
@endphp

  <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
             class="h-8" alt="Bank Jakarta" />
      </div>

      {{-- NAVBAR – semua link selalu terlihat, diarahkan sesuai role --}}
      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="{{ $homeUrl }}" class="{{ $isActive($homeUrl) }}">Beranda</a>
        <a href="{{ $progressUrl }}" class="{{ $isActive($progressUrl) }}">Progress</a>
        <a href="{{ $notifUrl }}" class="{{ $isActive($notifUrl) }}">Notifikasi</a>
        <a href="{{ $arsipUrl }}" class="{{ $isActive($arsipUrl) }}">Arsip</a>
        <span class="font-semibold text-red-600">{{ $roleLabel }}</span>
      </nav>

      <div class="relative">
        <button id="menuBtn" class="p-2 rounded-xl border border-red-200 text-red-700 hover:bg-red-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h6v6h-6v-6z" />
          </svg>
        </button>
        <div id="menuPanel"
             class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-[#7A1C1C] text-white overflow-hidden">
          <a href="#" class="block px-4 py-3 hover:bg-[#6a1717]">Pengaturan Akun</a>
          <a href="/logout" class="block px-4 py-3 hover:bg-[#6a1717]">Log Out</a>
        </div>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-5 py-6">
    <h1 class="text-lg font-semibold mb-3">Arsip</h1>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap items-end gap-3 border-b pb-4 mb-6">
      <div>
        <label class="text-xs text-gray-600">Kata Kunci</label>
        <input type="text" name="q" value="{{ request('q') }}"
               class="block w-[220px] rounded-xl bg-white border border-[#C89898] px-3 py-2 outline-none"
               placeholder="Cari nama/deskripsi project">
      </div>
      <div>
        <label class="text-xs text-gray-600">Tanggal Mulai</label>
        <input type="date" name="from" value="{{ request('from') }}"
               class="block rounded-xl bg-white border border-[#C89898] px-3 py-2 outline-none">
      </div>
      <div>
        <label class="text-xs text-gray-600">Tanggal Selesai</label>
        <input type="date" name="to" value="{{ request('to') }}"
               class="block rounded-xl bg-white border border-[#C89898] px-3 py-2 outline-none">
      </div>
      <div>
        <label class="text-xs text-gray-600">Urutkan</label>
        <select name="sort" class="block rounded-xl bg-white border border-[#C89898] px-3 py-2 outline-none">
          <option value="finished_desc" @selected(request('sort','finished_desc')==='finished_desc')>Terbaru selesai</option>
          <option value="finished_asc"  @selected(request('sort')==='finished_asc')>Terlama selesai</option>
          <option value="name_asc"      @selected(request('sort')==='name_asc')>Nama A-Z</option>
          <option value="name_desc"     @selected(request('sort')==='name_desc')>Nama Z-A</option>
        </select>
      </div>
      <button class="h-[38px] px-4 rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-sm font-semibold">
        Terapkan
      </button>
    </form>

    {{-- Daftar Arsip --}}
    @forelse ($projects as $project)
      @php
        // ring progress
        $latestPercents = [];
        foreach ($project->progresses as $pr) {
          $last = $pr->updates->first();
          $latestPercents[] = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
        }
        $realization = count($latestPercents) ? (int) round(array_sum($latestPercents)/max(count($latestPercents),1)) : 0;
        $size=90; $stroke=12; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);

        // tanggal selesai final → utamakan completed_at
        $finishedAt = $project->completed_at ?? ($project->finished_at_calc ?? $project->updated_at);

        // STATUS AKHIR (Memenuhi / Tidak Memenuhi)
        $isMeet      = (bool) $project->meets_requirement;
        $statusText  = $isMeet ? 'Memenuhi' : 'Tidak Memenuhi';
        $statusClass = $isMeet
            ? 'bg-green-100 text-green-700'
            : 'bg-red-100 text-red-700';
        $statusBarText = $isMeet ? 'Project Selesai, Memenuhi' : 'Project Selesai, Tidak Memenuhi';
      @endphp

      <section class="rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5 mb-6">
        {{-- Bar atas: status & tanggal keputusan --}}
        <div class="flex items-center justify-between text-xs font-semibold mb-2">
          <div class="inline-flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-full {{ $statusClass }}">
              {{ $statusBarText }}
            </span>
          </div>
          <span class="text-gray-600">
            {{ optional($finishedAt)->timezone('Asia/Jakarta')->translatedFormat('d F Y • H.i') }} WIB
          </span>
        </div>

        <div class="grid md:grid-cols-[auto,1fr,auto] items-start gap-5">
          {{-- Ring --}}
          <div class="flex items-center gap-4">
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                      stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                      transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
            </svg>
          </div>

          {{-- Info Project --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-1 text-sm">
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Nama Project</span><span>:</span>
              <span class="font-semibold">{{ $project->name }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Penanggung Jawab (Digital Banking)</span><span>:</span>
              <span>{{ $project->digitalBanking->name ?? '-' }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Penanggung Jawab (Developer)</span><span>:</span>
              <span>{{ $project->developer->name ?? '-' }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Deskripsi</span><span>:</span>
              <span>{{ $project->description ?: '-' }}</span>
            </div>

            {{-- ====== Tambahan: Status Akhir ====== --}}
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Status Akhir</span><span>:</span>
              <span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                  {{ $statusText }}
                </span>
              </span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Tanggal Keputusan</span><span>:</span>
              <span>{{ optional($finishedAt)->timezone('Asia/Jakarta')->translatedFormat('d F Y • H.i') }} WIB</span>
            </div>
            {{-- ====== /Tambahan ====== --}}
          </div>
        </div>

        {{-- List Progress (scrollable bila banyak) --}}
        <div class="mt-4">
          <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[240px] overflow-y-auto pr-1">
            @forelse($project->progresses as $idx => $pr)
              @php
                $last = $pr->updates->first();
                $realisasi = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
              @endphp
              <div class="rounded-xl bg-white/80 border border-[#C89898] p-4">
                <div class="font-semibold mb-2">Progress {{ $idx+1 }}</div>
                <div class="text-sm grid gap-1">
                  <div><span class="inline-block w-36 text-gray-700">Timeline Mulai</span>: {{ $pr->start_date }}</div>
                  <div><span class="inline-block w-36 text-gray-700">Timeline Selesai</span>: {{ $pr->end_date }}</div>
                  <div><span class="inline-block w-36 text-gray-700">Target Progress</span>: {{ $pr->desired_percent }}%</div>
                  <div><span class="inline-block w-36 text-gray-700">Realisasi Progress</span>: {{ $realisasi }}%</div>
                </div>
              </div>
            @empty
              <div class="col-span-2 text-sm text-gray-600">Tidak ada progress.</div>
            @endforelse
          </div>
        </div>

        {{-- CTA (POSISI DI KANAN-BAWAH KARTU) --}}
        <div class="mt-4 flex justify-end">
          <a href="{{ route('dig.projects.show', $project->id) }}"
             class="inline-flex items-center gap-2 rounded-lg border border-[#7A1C1C] px-3 py-1.5 text-xs font-semibold text-[#7A1C1C] bg-white hover:bg-[#FFF2F2]">
            Detail Informasi
          </a>
        </div>
      </section>
    @empty
      <div class="rounded-2xl bg-[#EBD0D0] px-6 py-8 text-center text-sm text-gray-700">
        Belum ada project yang diarsipkan.
      </div>
    @endforelse

    {{-- Pagination (opsional) --}}
    @if(method_exists($projects,'links'))
      <div class="mt-6">{{ $projects->withQueryString()->links() }}</div>
    @endif
  </main>

  <script>
    // dropdown
    const menuBtn = document.getElementById('menuBtn');
    const menuPanel = document.getElementById('menuPanel');
    menuBtn?.addEventListener('click', () => menuPanel.classList.toggle('hidden'));
    document.addEventListener('click', (e) => {
      if (!menuBtn?.contains(e.target) && !menuPanel?.contains(e.target)) menuPanel?.classList.add('hidden');
    });
  </script>
</body>
</html>
