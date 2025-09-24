{{-- resources/views/supervisor/show.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Detail Informasi | Supervisor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
     <style>
    html { scrollbar-gutter: stable; }
    body { overflow-x: hidden; }
  </style>
  <style>
    .chip{display:inline-flex;align-items:center;gap:.4rem;border:1px solid #cda7a7;background:#f8eded;border-radius:9999px;padding:.25rem .55rem;font-size:.75rem}
    .scroll-thin::-webkit-scrollbar{width:6px;height:6px}
    .scroll-thin::-webkit-scrollbar-thumb{background:#d1b1b1;border-radius:9999px}
    .scroll-thin::-webkit-scrollbar-track{background:transparent}
  </style>
</head>
<body class="min-h-screen bg-[#F8ECEC] text-gray-900">

  {{-- NAVBAR ringkas --}}
  <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta"/>
      </div>
      <div></div>
    </div>
  </header>

 <main class="max-w-6xl mx-auto px-5 py-6">
  {{-- STATUS + TANGGAL (kiri) + TOMBOL KEMBALI (kanan) --}}
  <div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-3 text-sm">
      <span class="font-semibold {{ $allMetAndConfirmed ? 'text-green-700' : 'text-[#7A1C1C]' }}">
        {{ $allMetAndConfirmed ? 'Project Telah Selesai' : 'Dalam Proses' }}
      </span>
      <span class="text-gray-600">
        {{ $finishedAt ? $finishedAt->translatedFormat('d F Y') : '' }}
      </span>
    </div>

    <a href="{{ route('supervisor.progresses') }}"
       class="px-3 py-2 rounded-lg border border-[#7A1C1C] text-[#7A1C1C] bg-white hover:bg-[#FFF2F2] text-[12px] font-medium">
      Kembali
    </a>
  </div>
     

    {{-- KARTU ATAS: ring + info umum --}}
    @php
      $size=88; $stroke=10; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-($realization/100));
    @endphp
    <section class="rounded-2xl  p-5">
      <div class="grid md:grid-cols-[auto,1fr] gap-6">
        {{-- Ring progress total --}}
        <div class="flex items-center gap-4">
          <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
            <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
            <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                    stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                    transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
            <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
          </svg>
        </div>

        {{-- Info ringkas project --}}
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
    </section>

    {{-- LIST PROGRESS --}}
    <div class="mt-6 space-y-4">
      @forelse ($project->progresses as $i => $pr)
        @php
          $last = $pr->updates->first();
          $real = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;

          // chip riwayat harian: pisahkan per role (maks 4 chip/tampilan)
          $digChips = $pr->updates->filter(fn($u)=>($u->creator?->role ?? $u->role ?? null)==='digital_banking')
                                  ->take(4)
                                  ->map(fn($u)=>[
                                      'date'=> optional($u->update_date)->timezone('Asia/Jakarta')->format('j M'),
                                      'p'   => (int)($u->percent ?? $u->progress_percent ?? 0)
                                  ]);

          $itChips  = $pr->updates->filter(fn($u)=>($u->creator?->role ?? $u->role ?? null)==='it')
                                  ->take(4)
                                  ->map(fn($u)=>[
                                      'date'=> optional($u->update_date)->timezone('Asia/Jakarta')->format('j M'),
                                      'p'   => (int)($u->percent ?? $u->progress_percent ?? 0)
                                  ]);

          // catatan terakhir per role (kalau ada relasi notes)
          $noteDig = optional($pr->notes?->firstWhere('role','digital_banking'))->content ?? '—';
          $noteIt  = optional($pr->notes?->firstWhere('role','it'))->content ?? '—';
        @endphp

        <div class="rounded-2xl bg-[#F2DCDC] border border-[#E7C9C9] p-4">
          <div class="flex items-center justify-between mb-2">
            <div class="font-semibold">Progress {{ $i+1 }} – {{ $pr->name }}</div>
            <div class="text-xs font-semibold {{ $pr->confirmed_at ? 'text-green-700' : 'text-[#7A1C1C]' }}">
              {{ $pr->confirmed_at ? 'Telah Selesai' : 'Dalam Proses' }}
            </div>
          </div>

          <div class="grid md:grid-cols-[1fr,1fr,1fr] gap-4">
            {{-- info time/target --}}
            <div class="text-sm grid gap-1">
              <div><span class="inline-block w-36 text-gray-700">Timeline Mulai</span>: {{ $pr->start_date }}</div>
              <div><span class="inline-block w-36 text-gray-700">Timeline Selesai</span>: {{ $pr->end_date }}</div>
              <div><span class="inline-block w-36 text-gray-700">Target Progress</span>: {{ (int)$pr->desired_percent }}%</div>
              <div><span class="inline-block w-36 text-gray-700">Realisasi Progress</span>: {{ $real }}%</div>
            </div>

            {{-- riwayat harian DIG --}}
            <div>
              <div class="text-xs font-semibold mb-2">Riwayat Progress Harian (DIG)</div>
              <div class="flex flex-wrap gap-2">
                @forelse($digChips as $c)
                  <span class="chip">{{ $c['date'] }} · {{ $c['p'] }}%</span>
                @empty
                  <span class="text-xs text-gray-500">Belum ada riwayat.</span>
                @endforelse
              </div>
              <div class="mt-3 rounded-xl border border-[#C89898] bg-white/70 p-3 text-xs text-gray-700">
                <div class="mb-1 font-semibold">Catatan Digital Banking</div>
                <div class="min-h-[60px] whitespace-pre-line">{{ $noteDig }}</div>
              </div>
            </div>

            {{-- riwayat harian IT --}}
            <div>
              <div class="text-xs font-semibold mb-2">Riwayat Progress Harian (IT)</div>
              <div class="flex flex-wrap gap-2">
                @forelse($itChips as $c)
                  <span class="chip">{{ $c['date'] }} · {{ $c['p'] }}%</span>
                @empty
                  <span class="text-xs text-gray-500">Belum ada riwayat.</span>
                @endforelse
              </div>
              <div class="mt-3 rounded-xl border border-[#C89898] bg-white/70 p-3 text-xs text-gray-700">
                <div class="mb-1 font-semibold">Catatan IT</div>
                <div class="min-h-[60px] whitespace-pre-line">{{ $noteIt }}</div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="rounded-2xl bg-[#EBD0D0] px-6 py-8 text-center text-sm text-gray-700">
          Belum ada progress untuk project ini.
        </div>
      @endforelse

    </div>
  </main>
</body>
</html>
