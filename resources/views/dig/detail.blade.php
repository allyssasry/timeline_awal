{{-- resources/views/dig/dashboard.blade.php --}}
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Dashboard DIG</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
      <style>
    /* Cegah “geser ke kanan” saat scrollbar muncul/hilang */
    html { scrollbar-gutter: stable; }           /* Chrome/Edge/Firefox modern */
    body { overflow-x: hidden; }                 /* Antisipasi overflow horizontal */
  </style>
</head>

<body class="min-h-screen bg-[#F8ECEC] text-gray-900">

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
        <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
                    class="h-8" alt="Bank Jakarta" />
            </div>
        </div>
    </header>

  <div class="max-w-6xl mx-auto px-5 py-6">

    {{-- BARIS JUDUL/AKSI --}}
    <div class="flex items-center justify-between">
      <h1 class="text-[15px] md:text-[16px] font-semibold text-[#7A1C1C]">Dalam Proses</h1>
      <a href="{{ route('dig.dashboard') }}"
         class="px-3 py-2 rounded-lg border border-[#7A1C1C] text-[#7A1C1C] bg-white hover:bg-[#FFF2F2] text-[12px] font-medium">
        Kembali
      </a>
    </div>

    {{-- RING + INFO HEADER + CTA TAMBAH PROGRESS --}}
    @php
      $latest=[]; foreach($project->progresses as $p){ $u=$p->updates->sortByDesc('update_date')->first(); $latest[]=$u?(int)($u->percent??$u->progress_percent??0):0; }
      $realization = count($latest)? (int) round(array_sum($latest)/max(count($latest),1)) : 0;
      $size=84; $stroke=10; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);
    @endphp

    <div class="mt-2 grid grid-cols-1 md:grid-cols-[auto,1fr,auto] gap-6 items-center">
      {{-- RING --}}
      <div class="flex items-center gap-4">
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
          <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#E5B9B9" stroke-width="{{ $stroke }}" fill="none" opacity=".65"/>
          <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                  stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                  transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
          <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                class="fill-[#222]" font-size="16" font-weight="700">{{ $realization }}%</text>
        </svg>
      </div>

      {{-- INFO UTAMA --}}
      <div class="grid sm:grid-cols-2 gap-y-1 gap-x-10 text-[13px] leading-5">
        <div><span class="inline-block w-40 text-gray-700">Nama Project</span> : <span class="font-semibold">{{ $project->name }}</span></div>
        <div><span class="inline-block w-40 text-gray-700">Penanggung Jawab</span> : Allyssa</div>
        <div><span class="inline-block w-40 text-gray-700">(Digital Banking)</span> : {{ optional($project->digitalBanking)->name ?? '-' }}</div>
        <div><span class="inline-block w-40 text-gray-700">(Developer)</span> : {{ optional($project->developer)->name ?? '-' }}</div>
      </div>

      {{-- CTA TAMBAH PROGRESS --}}
      <div class="flex justify-end">
        <button type="button"
                class="inline-flex items-center gap-2 rounded-[12px] px-4 py-2 text-white text-[13px] font-semibold bg-[#7A1C1C] shadow">
          <span class="grid place-content-center w-6 h-6 rounded-full bg-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
          </span>
          Tambah Progress
        </button>
      </div>
    </div>

    {{-- KARTU PROGRESS --}}
    <div class="mt-6 space-y-5">
      @forelse($project->progresses as $i => $pr)
        @php
          $last = $pr->updates->sortByDesc('update_date')->first();
          $realisasi = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;
          $today = now()->toDateString();
          $canConfirm = $realisasi >= (int)$pr->desired_percent && !$pr->confirmed_at;
        @endphp

        <section class="rounded-[16px] bg-[#E3BDBD]/60 border border-[#C99E9E] px-5 py-4">
          {{-- HEAD ROW --}}
          <div class="flex items-center justify-between mb-3">
            <div class="text-[14px] font-semibold text-[#2d2d2d]">
              Progress {{ $i+1 }} — <span class="{{ $pr->confirmed_at ? 'text-green-700' : 'text-[#7A1C1C]' }}">
                {{ $pr->confirmed_at ? 'Selesai' : 'Dalam Proses' }}
              </span>
            </div>

            <div>
              @if($pr->confirmed_at)
                <span class="rounded-full bg-[#7A1C1C] text-white px-4 py-1 text-[12px] font-semibold">Project Selesai</span>
              @else
                <form method="POST" action="{{ route('progresses.confirm', $pr->id) }}">
                  @csrf
                  <button class="rounded-full bg-[#7A1C1C] text-white px-4 py-1 text-[12px] font-semibold disabled:opacity-50"
                          {{ $canConfirm ? '' : 'disabled' }}>
                    Konfirmasi
                  </button>
                </form>
              @endif
            </div>
          </div>

          {{-- BODY GRID (info / riwayat / formulir) --}}
          <div class="grid md:grid-cols-[1fr,1fr,340px] gap-6">
            {{-- INFO TIMELINE + RIWAYAT CHIP --}}
            <div class="text-[13px] leading-6">
              <div><span class="inline-block w-40 text-gray-700">Timeline Mulai</span> : {{ $pr->start_date }}</div>
              <div><span class="inline-block w-40 text-gray-700">Timeline Selesai</span> : {{ $pr->end_date }}</div>
              <div><span class="inline-block w-40 text-gray-700">Target Progress</span> : {{ $pr->desired_percent }}%</div>
              <div><span class="inline-block w-40 text-gray-700">Realisasi Progress</span> : {{ $realisasi }}%</div>

              <div class="mt-3">
                <div class="text-[12px] font-semibold text-gray-700 mb-1">Riwayat Progress Harian</div>
                <div class="flex flex-wrap gap-2">
                  @forelse($pr->updates->sortByDesc('update_date')->take(6) as $up)
                    <span class="px-3 py-1 rounded-full border border-[#C89898] bg-white/80 text-[11px]">
                      {{ \Illuminate\Support\Carbon::parse($up->update_date)->format('d M') }} : {{ $up->percent ?? $up->progress_percent }}%
                    </span>
                  @empty
                    <span class="text-[12px] text-gray-500">Belum ada riwayat.</span>
                  @endforelse
                </div>
              </div>
            </div>

            {{-- CATATAN DB & IT --}}
            <div>
              <div class="text-[12px] font-semibold text-gray-700 mb-1">Catatan Digital Banking & IT</div>
              <div class="rounded-[12px] bg-white/80 border border-[#C89898] p-3 h-[140px] overflow-auto text-[13px]">
                @forelse($pr->notes as $note)
                  <div class="mb-2">
                    <div class="text-[11px] text-gray-500">
                      {{ strtoupper($note->role) }} • {{ \Illuminate\Support\Carbon::parse($note->created_at)->format('d M Y H:i') }}
                    </div>
                    <div>{{ $note->body }}</div>
                  </div>
                @empty
                  <div class="text-gray-500">Belum ada catatan.</div>
                @endforelse
              </div>
            </div>

            {{-- FORM UPDATE & CATATAN (panel kanan) --}}
            <div class="rounded-[14px] bg-white border border-[#E0BEBE] p-4">
              <div class="flex items-center justify-end mb-2">
                @if($pr->confirmed_at)
                  <span class="inline-block text-[11px] rounded-full bg-green-100 text-green-700 px-2 py-0.5">Project Selesai</span>
                @endif
              </div>

              {{-- UPDATE PROGRESS (tgl otomatis harian) --}}
              <div class="text-[12px] text-gray-700 font-semibold mb-1">Update Progress</div>
              <form method="POST" action="{{ route('progresses.updates.store', $pr->id) }}" class="space-y-2">
                @csrf
                <input type="date" name="update_date" value="{{ now()->toDateString() }}" readonly
                       class="w-full rounded-[10px] bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]">
                <div class="flex gap-2">
                  <input type="number" name="percent" min="0" max="100" placeholder="Progress %"
                         class="w-full rounded-[10px] bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]" required>
                  <button class="rounded-[10px] bg-[#7A1C1C] text-white px-4 py-2 text-[12px] font-semibold">Update</button>
                </div>
              </form>

              {{-- TAMBAH CATATAN --}}
              <div class="mt-4 text-[12px] text-gray-700 font-semibold mb-1">Tambah Catatan</div>
              <form method="POST" action="{{ route('progresses.notes.store', $pr->id) }}" class="space-y-2">
                @csrf
                <textarea name="body" rows="3" placeholder="Catatan"
                          class="w-full rounded-[10px] bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]"></textarea>
                <button class="w-full rounded-[12px] border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-[13px] py-2 font-semibold">
                  Simpan
                </button>
              </form>
            </div>
          </div>
        </section>
      @empty
        <div class="text-sm text-gray-600">Belum ada progress.</div>
      @endforelse
    </div>
  </div>
</body>
</html>
