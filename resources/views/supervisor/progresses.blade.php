{{-- resources/views/supervisor/progresses.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Progress | Supervisor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html { scrollbar-gutter: stable; } body { overflow-x: hidden; }
    .scroll-thin::-webkit-scrollbar{width:6px;height:6px}
    .scroll-thin::-webkit-scrollbar-thumb{background:#cda7a7;border-radius:9999px}
    .scroll-thin::-webkit-scrollbar-track{background:transparent}
  </style>
</head>
<body class="min-h-screen bg-[#F8ECEC] text-gray-900">

  {{-- NAVBAR --}}
  <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta"/>
      </div>
      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="{{ route('supervisor.dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
        <a href="{{ route('supervisor.progresses') }}" class="font-semibold">Progress</a>
        <a href="{{ route('supervisor.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">Supervisor</span>
      </nav>

      <div class="relative">
        <button id="menuBtn" class="p-2 rounded-xl border border-red-200 text-red-700 hover:bg-red-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h6v6h-6v-6z" />
          </svg>
        </button>
        <div id="menuPanel" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-[#7A1C1C] text-white overflow-hidden">
          <a href="#" class="block px-4 py-3 hover:bg-[#6a1717]">Pengaturan Akun</a>
          <a href="/logout" class="block px-4 py-3 hover:bg-[#6a1717]">Log Out</a>
        </div>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-5 py-6">
    {{-- FILTER TAB --}}
    @php
      $q = $status ?? request('status','all'); // all|in_progress|done
      $tab = fn($v) => $q===$v ? 'bg-[#7A1C1C] text-white' : 'bg-white text-[#7A1C1C] hover:bg-[#FFF2F2]';
    @endphp
    <div class="flex gap-3">
      <a href="{{ route('supervisor.progresses',['status'=>'all']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('all') }} grid place-items-center">Semua</a>
      <a href="{{ route('supervisor.progresses',['status'=>'in_progress']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('in_progress') }} grid place-items-center">Dalam Proses</a>
      <a href="{{ route('supervisor.progresses',['status'=>'done']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('done') }} grid place-items-center">Telah Selesai</a>
    </div>

    {{-- LIST PROJECT --}}
    @forelse($projects as $project)
      @php
        // cincin: rata-rata realisasi dari update terbaru tiap progress
        $latestPercents = [];
        foreach ($project->progresses as $pr) {
          $u = $pr->updates->sortByDesc('update_date')->first();
          $latestPercents[] = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
        }
        $realization = count($latestPercents) ? (int) round(array_sum($latestPercents)/max(count($latestPercents),1)) : 0;
        $size=88; $stroke=10; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);

        // selesai jika semua progress >= target & confirmed_at terisi
        $allMetAndConfirmed = $project->progresses->every(function ($p) {
          $u = $p->updates->sortByDesc('update_date')->first();
          $real = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
          return $real >= (int)$p->desired_percent && !is_null($p->confirmed_at);
        });

        // tanggal semua progress selesai (maks confirmed_at)
        $finishedAt = $allMetAndConfirmed
          ? optional($project->progresses->max('confirmed_at'))->timezone('Asia/Jakarta')
          : null;
      @endphp

      <section class="mt-6 rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5">
        {{-- HEADER PROJECT (status kiri & tanggal selesai kanan) --}}
        <div class="flex items-center justify-between text-xs font-semibold mb-2">
          <span class="{{ $allMetAndConfirmed ? 'text-green-700' : 'text-[#7A1C1C]' }}">
            {{ $allMetAndConfirmed ? 'Project Telah Selesai' : 'Dalam Proses' }}
          </span>
          <span class="text-gray-600">
            {{ $finishedAt ? $finishedAt->translatedFormat('d F Y') : '' }}
          </span>
        </div>

        {{-- ISI: ring + info project --}}
        <div class="grid md:grid-cols-[auto,1fr] items-start gap-5">
          {{-- ring --}}
          <div class="flex items-center gap-4">
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                      stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                      transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
            </svg>
          </div>

          {{-- info project --}}
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-1 text-sm">
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Nama Project</span><span>:</span>
              <span class="font-semibold">{{ $project->name }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Penanggung Jawab (DIG)</span><span>:</span>
              <span>{{ $project->digitalBanking->name ?? '-' }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Penanggung Jawab (IT)</span><span>:</span>
              <span>{{ $project->developer->name ?? '-' }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-600">Deskripsi</span><span>:</span>
              <span>{{ $project->description ?: '-' }}</span>
            </div>
          </div>
        </div>

        {{-- LIST PROGRESS (scrollable) --}}
        <div class="mt-4">
          <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[240px] overflow-y-auto pr-1">
            @forelse($project->progresses as $idx => $pr)
              @php
                $last    = $pr->updates->sortByDesc('update_date')->first();
                $real    = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;
                $creator = $pr->creator;
                $ownerRoleLabel = $creator?->role === 'digital_banking' ? 'DIG' : ($creator?->role === 'it' ? 'IT' : '—');
              @endphp
              <div class="rounded-xl bg-white/80 border border-[#C89898] p-4">
                <div class="font-semibold mb-2">Progress {{ $idx+1 }} — {{ $pr->name }}</div>
                <div class="text-sm grid gap-1">
                  <div><span class="inline-block w-36 text-gray-700">Timeline Mulai</span>: {{ $pr->start_date }}</div>
                  <div><span class="inline-block w-36 text-gray-700">Timeline Selesai</span>: {{ $pr->end_date }}</div>
                  <div><span class="inline-block w-36 text-gray-700">Target Progress</span>: {{ $pr->desired_percent }}%</div>
                  <div><span class="inline-block w-36 text-gray-700">Realisasi Progress</span>: {{ $real }}%</div>
                  <div class="text-xs text-gray-600 mt-1">*Dibuat oleh <strong>{{ $ownerRoleLabel }}</strong> — {{ $creator?->name ?? '—' }}</div>
                </div>
              </div>
            @empty
              <div class="col-span-2 text-sm text-gray-600">Belum ada progress.</div>
            @endforelse
          </div>
        </div>

        {{-- CTA (POSISI DI KANAN-BAWAH KARTU) --}}
        <div class="mt-4 flex justify-end">
          <a href="{{ route('supervisor.projects.show', $project->id) }}"
   class="inline-flex items-center gap-2 rounded-lg border border-[#7A1C1C] px-3 py-1.5 text-xs font-semibold text-[#7A1C1C] bg-white hover:bg-[#FFF2F2]">
  Detail Informasi
</a>

        </div>
      </section>
    @empty
      <div class="mt-6">
        <div class="bg-[#EBD0D0] rounded-2xl px-6 py-8 flex items-center justify-center">
          <div class="rounded-2xl bg-[#CFA8A8] px-5 py-3 text-white/95">Tidak ada project untuk ditampilkan</div>
        </div>
      </div>
    @endforelse
  </main>
   <script>
    // Dropdown menu
    (function(){
      const btn = document.getElementById('menuBtn');
      const panel = document.getElementById('menuPanel');
      btn?.addEventListener('click', (e)=>{ e.stopPropagation(); panel?.classList.toggle('hidden'); });
      panel?.addEventListener('click', (e)=> e.stopPropagation());
      document.addEventListener('click', ()=> panel?.classList.add('hidden'));
    })();
</script>
</body>
</html>
