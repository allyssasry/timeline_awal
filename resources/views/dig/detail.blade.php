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
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta" />
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
        <div><span class="inline-block w-40 text-gray-700">Penanggung Jawab (DIG)</span> : {{ optional($project->digitalBanking)->name ?? '-' }}</div>
        <div><span class="inline-block w-40 text-gray-700"> Penanggung Jawab (Developer)</span> : {{ optional($project->developer)->name ?? '-' }}</div>
        <div><span class="inline-block w-40 text-gray-700"> Deskripsi</span> : {{ $project->description ?: '-' }}</div>
      </div>

      {{-- AKSI PROJECT + CTA TAMBAH PROGRESS --}}
      <div class="flex flex-col items-end gap-2">
        <div class="flex items-center gap-2">
          {{-- EDIT PROJECT --}}
          <a href="{{ route('projects.edit', $project->id) }}"
             class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Edit Project">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM22.61 5.64c.39-.39.39-1.02 0-1.41l-2.83-2.83a.9959.9959 0 0 0-1.41 0L16.13 3.04l3.75 3.75 2.73-2.73z"/>
            </svg>
          </a>
          {{-- HAPUS PROJECT --}}
          <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                onsubmit="return confirm('Yakin ingin menghapus project ini? Aksi ini tidak bisa dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Hapus Project">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6 7h12v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6l1 1h4v2H4V4h4l1-1z"/>
              </svg>
            </button>
          </form>
        </div>

        <button type="button" id="btnToggleProgressForm"
                class="inline-flex items-center gap-2 rounded-[12px] px-4 py-2 text-white text-[13px] font-semibold bg-[#7A1C1C] shadow"
                data-target="progressForm-{{ $project->id }}">
          <span class="grid place-content-center w-6 h-6 rounded-full bg-white/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
          </span>
          Tambah Progress
        </button>
      </div>
    </div>

    {{-- FORM TAMBAH PROGRESS (HIDDEN) --}}
    <div id="progressForm-{{ $project->id }}"
         class="hidden mt-3 rounded-xl bg-white p-4 border border-[#E7C9C9]">
      <div class="font-semibold mb-2">Tambah Progress untuk Project ini</div>
      <form method="POST" action="{{ route('projects.progresses.store', $project->id) }}"
            class="grid grid-cols-1 md:grid-cols-5 gap-2">
        @csrf
        <input name="name" required placeholder="Nama Progress"
               class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none md:col-span-2">
        <input type="date" name="start_date" required
               class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none">
        <input type="date" name="end_date" required
               class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none">
        <select name="desired_percent" required
                class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none">
          @for ($i = 0; $i <= 100; $i += 5)
            <option value="{{ $i }}">{{ $i }}%</option>
          @endfor
        </select>
        <button
          class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] px-4 py-2 font-semibold hover:bg-[#D9AFAF]">
          Tambah
        </button>
      </form>
    </div>

    {{-- KARTU PROGRESS --}}
    <div class="mt-6 space-y-5">
      @forelse($project->progresses as $i => $pr)
        @php
          $last = $pr->updates->sortByDesc('update_date')->first();
          $realisasi = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;
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

            <div class="flex items-center gap-2">
              {{-- TOGGLE EDIT PROGRESS --}}
              <button type="button"
                      class="px-3 py-1.5 text-xs rounded-lg border bg-white/70 hover:bg-white btn-edit-progress"
                      data-target="editProgress-{{ $pr->id }}">
                Edit
              </button>
              {{-- HAPUS PROGRESS --}}
              <form method="POST" action="{{ route('progresses.destroy', $pr->id) }}"
                    onsubmit="return confirm('Hapus progress ini? Tindakan tidak bisa dibatalkan.');">
                @csrf
                @method('DELETE')
                <button class="px-3 py-1.5 text-xs rounded-lg border bg-white/70 hover:bg-white">
                  Hapus
                </button>
              </form>

              {{-- KONFIRMASI SELESAI --}}
              @if($pr->confirmed_at)
                <span class="rounded-full bg-[#7A1C1C] text-white px-4 py-1 text-[12px] font-semibold">Selesai</span>
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

            {{-- PANEL KANAN: UPDATE & CATATAN --}}
            <div class="rounded-[14px] bg-white border border-[#E0BEBE] p-4">
              <div class="flex items-center justify-end mb-2">
                @if($pr->confirmed_at)
                  <span class="inline-block text-[11px] rounded-full bg-green-100 text-green-700 px-2 py-0.5">Project Selesai</span>
                @endif
              </div>

              {{-- FORM EDIT PROGRESS (INLINE, HIDDEN) --}}
              <div id="editProgress-{{ $pr->id }}" class="hidden mb-4">
                <div class="text-[12px] text-gray-700 font-semibold mb-1">Edit Progress</div>
                <form method="POST" action="{{ route('progresses.update', $pr->id) }}" class="grid grid-cols-1 gap-2">
                  @csrf
                  @method('PUT')
                  <input name="name" value="{{ old('name', $pr->name) }}" required
                         class="rounded-xl bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]" placeholder="Nama progress">
                  <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" value="{{ old('start_date', $pr->start_date) }}" required
                           class="rounded-xl bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]">
                    <input type="date" name="end_date" value="{{ old('end_date', $pr->end_date) }}" required
                           class="rounded-xl bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]">
                  </div>
                  <select name="desired_percent" required
                          class="rounded-xl bg-[#F6EAEA] border border-[#C89898] px-3 py-2 text-[13px]">
                    @for ($x = 0; $x <= 100; $x += 5)
                      <option value="{{ $x }}" @selected((int)old('desired_percent', $pr->desired_percent) === $x)>{{ $x }}%</option>
                    @endfor
                  </select>

                  <div class="flex justify-end">
                    <button
                      class="inline-flex items-center justify-center
                             h-[40px] min-w-[160px] px-5
                             rounded-full border-2 border-[#7A1C1C]
                             bg-[#E2B9B9] hover:bg-[#D9AFAF]
                             font-semibold text-sm whitespace-nowrap">
                      Simpan Perubahan
                    </button>
                  </div>
                </form>
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

  {{-- Toggle script untuk form Tambah Progress & Edit Progress --}}
  <script>
    // toggle form tambah progress (project)
    const btn = document.getElementById('btnToggleProgressForm');
    btn?.addEventListener('click', () => {
      const id = btn.getAttribute('data-target');
      document.getElementById(id)?.classList.toggle('hidden');
    });

    // toggle setiap form edit progress (inline)
    document.querySelectorAll('.btn-edit-progress').forEach(b=>{
      b.addEventListener('click', ()=>{
        const target = b.getAttribute('data-target');
        document.getElementById(target)?.classList.toggle('hidden');
      });
    });
  </script>
</body>
</html>
