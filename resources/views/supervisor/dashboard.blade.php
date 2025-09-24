{{-- resources/views/supervisor/dashboard.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard Supervisor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html { scrollbar-gutter: stable; }
    body { overflow-x: hidden; }
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
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta" />
      </div>

      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="#beranda" class="font-semibold">Beranda</a>
        <a href="{{ route('supervisor.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
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

  {{-- BANNER --}}
  <section id="beranda" class="relative h-[260px] md:h-[320px] overflow-hidden">
    <img src="https://i.pinimg.com/736x/c5/43/71/c543719c97d9efa97da926387fa79d1f.jpg" class="w-full h-full object-cover" alt="Banner" />
    <div class="absolute inset-0 bg-black/30"></div>
    <div class="absolute inset-0 flex items-center justify-center">
      <h1 class="text-white text-2xl md:text-3xl font-bold">Selamat Datang di Timeline Progress</h1>
    </div>
  </section>

  {{-- SUB HEADER --}}
  <header class="sticky top-0 z-20 bg-[#F3DCDC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
      <span class="font-semibold text-gray-700 text-lg">Project</span>
      {{-- Supervisor: tidak ada tombol tambah --}}
    </div>
  </header>

  <main class="max-w-6xl mx-auto px-5 pb-10">
    @if ($projects->isEmpty())
      <div class="mt-6">
        <div class="bg-[#EBD0D0] rounded-2xl px-6 py-8 flex items-center justify-center">
          <div class="rounded-2xl bg-[#CFA8A8] px-5 py-3 text-white/95">Belum ada project</div>
        </div>
      </div>
    @endif

    @foreach ($projects as $project)
      @php
        // Cincin: rata-rata realisasi dari update terbaru tiap progress
        $latestPercents = [];
        foreach ($project->progresses as $pr) {
          $u = $pr->updates->first(); // sudah latest()
          $latestPercents[] = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
        }
        $realization = count($latestPercents) ? (int) round(array_sum($latestPercents)/max(count($latestPercents),1)) : 0;

        $size=110; $stroke=12; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);

        // Selesai jika SEMUA progress sudah terkonfirmasi & mencapai target
        $allMetAndConfirmed = $project->progresses->every(function($pr){
          $last = $pr->updates->first();
          $realisasi = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
          return $realisasi >= (int)$pr->desired_percent && !is_null($pr->confirmed_at);
        });

        // Tanggal selesai = tanggal konfirmasi paling akhir (Asia/Jakarta)
        $finishedAt = null;
        if ($allMetAndConfirmed) {
          $max = $project->progresses->max('confirmed_at');
          $finishedAt = $max ? \Illuminate\Support\Carbon::parse($max)->timezone('Asia/Jakarta') : null;
        }
      @endphp

      <section class="mt-6 rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5">
        {{-- Header kartu: badge kiri & tanggal selesai kanan --}}
        <div class="flex items-center justify-between text-xs font-semibold mb-3">
          <span class="{{ $allMetAndConfirmed ? 'text-green-700' : 'text-[#7A1C1C]' }}">
            {{ $allMetAndConfirmed ? 'Project telah selesai' : 'Dalam Proses' }}
          </span>
          <span class="text-gray-600">
            {{ $finishedAt ? $finishedAt->translatedFormat('d F Y') : '' }}
          </span>
        </div>

        <div class="grid md:grid-cols-[auto,1fr,auto] items-start gap-5">
          {{-- Cincin --}}
          <div class="flex items-center gap-4">
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                      stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                      transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="18" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
            </svg>
          </div>

          {{-- Info project --}}
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

        {{-- List progress 2 kolom, scroll jika banyak --}}
        <div class="mt-4">
          <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[280px] overflow-y-auto pr-1">
            @forelse($project->progresses as $i => $pr)
              @php
                $last      = $pr->updates->first();
                $realisasi = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
                $creator   = $pr->creator;
                $makerRole = $creator?->role === 'digital_banking' ? 'DIG' : ($creator?->role === 'it' ? 'IT' : '—');
              @endphp
              <div class="rounded-xl bg-[#E6CACA] p-4">
                <div class="font-semibold mb-2">Progress {{ $i+1 }} — {{ $pr->name }}</div>
                <div class="text-sm grid grid-cols-[auto,1fr] gap-x-4 gap-y-1">
                   <span>Timeline Mulai</span>
                      <span>:
                        {{ $pr->start_date
                           ? \Illuminate\Support\Carbon::parse($pr->start_date)->timezone('Asia/Jakarta')->format('d M Y')
                           : '-' }}
                      </span>

                      <span>Timeline Selesai</span>
                      <span>:
                        {{ $pr->end_date
                           ? \Illuminate\Support\Carbon::parse($pr->end_date)->timezone('Asia/Jakarta')->format('d M Y')
                           : '-' }}
                      </span>
                  <span class="inline-block w-36 text-gray-700">Target Progress</span> <span> : {{ (int)$pr->desired_percent }}% </span>
                  <span class="inline-block w-36 text-gray-700">Realisasi Progress</span> <span> : {{ $realisasi }}% </span>
                  <span class="inline-block w-36 text-gray-700">Pembuat Progress</span> <span> : {{ $makerRole }} — {{ $creator?->name ?? '—' }}</span>
                </div>
              </div>
            @empty
              <div class="col-span-2 text-sm text-gray-600">Belum ada progress.</div>
            @endforelse
          </div>

           {{-- CTA detail (opsional) --}}
          <div class=" mt-4 flex items-start justify-end gap-x-2">
            <a href="{{ route('dig.projects.show', $project->id) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-[#7A1C1C] px-3 py-1.5 text-xs font-semibold text-[#7A1C1C] bg-white hover:bg-[#FFF2F2]">
              Detail Informasi
            </a>
          </div>
        </div>
      </section>
    @endforeach
  </main>

  <script>
    // dropdown
    (function(){
      const btn = document.getElementById('menuBtn');
      const panel = document.getElementById('menuPanel');
      btn?.addEventListener('click', (e)=>{ e.stopPropagation(); panel?.classList.toggle('hidden'); });
      document.addEventListener('click', ()=> panel?.classList.add('hidden'));
    })();
  </script>
</body>
</html>
