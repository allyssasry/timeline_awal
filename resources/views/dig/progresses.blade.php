{{-- resources/views/dig/progresses.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Progress | DIG</title>
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

  {{-- NAVBAR RINGKAS --}}
  <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta" />
      </div>
      <nav class="hidden md:flex items-center gap-6 text-sm">
       <a href="{{ url('/dig/dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
        <a href="{{ route('dig.progresses') }}" class="font-semibold">Progress</a> 
        <a href="{{ route('dig.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
        <a href="{{ route('arsip.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">DIG</span>
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

    {{-- FILTER TAB --}}
    @php
      $q = request('status','all'); // all|in_progress|done
      $tab = fn($v) => $q===$v ? 'bg-[#7A1C1C] text-white' : 'bg-white text-[#7A1C1C] hover:bg-[#FFF2F2]';
    @endphp
    <div class="flex gap-3">
      <a href="{{ route('dig.progresses',['status'=>'all']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('all') }} grid place-items-center">
        Semua
      </a>
      <a href="{{ route('dig.progresses',['status'=>'in_progress']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('in_progress') }} grid place-items-center">
        Dalam Proses
      </a>
      <a href="{{ route('dig.progresses',['status'=>'done']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('done') }} grid place-items-center">
        Telah Selesai
      </a>
    </div>

    {{-- NOTIF --}}
    @if (session('success'))
      <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Terjadi kesalahan:</div>
        <ul class="list-disc pl-5 space-y-1">
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif

    {{-- LIST PROJECT --}}
    @forelse ($projects as $project)
      @php
        // hitung rata2 realisasi dari update terbaru masing2 progress
        $latest = [];
        foreach ($project->progresses as $p) {
          $u = $p->updates->sortByDesc('update_date')->first();
          $latest[] = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
        }
        $realization = count($latest) ? (int) round(array_sum($latest)/max(count($latest),1)) : 0;
        $size=88; $stroke=10; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);
      @endphp

      <section class="mt-6 rounded-[16px] border-2 border-[#7A1C1C] bg-[#F2DCDC] p-4">
        <div class="mb-2 text-xs font-semibold text-[#7A1C1C]">
          {{ $q==='done' ? 'Selesai' : 'Dalam Proses' }}
        </div>

        <div class="grid md:grid-cols-[auto,1fr,auto] gap-5 items-start">
          {{-- Ring --}}
          <div class="flex items-center gap-3">
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                      stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                      transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
            </svg>
          </div>

          {{-- Info project --}}
          <div class="grid sm:grid-cols-2 gap-x-10 gap-y-1 text-sm">
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-700">Nama Project</span><span>:</span>
              <span class="font-semibold">{{ $project->name }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-700">Penanggung Jawab (DIG)</span><span>:</span>
              <span>{{ optional($project->digitalBanking)->name ?? '-' }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-700">Penanggung Jawab (IT)</span><span>:</span>
              <span>{{ optional($project->developer)->name ?? '-' }}</span>
            </div>
            <div class="grid grid-cols-[auto_auto_1fr] gap-x-2">
              <span class="text-gray-700">Deskripsi</span><span>:</span>
              <span>{{ $project->description ?: '-' }}</span>
            </div>
          </div>

          {{-- Aksi kanan --}}
          <div class="flex items-start gap-2">
            <a href="{{ route('projects.edit', $project->id) }}" class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Edit Project">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM22.61 5.64c.39-.39.39-1.02 0-1.41l-2.83-2.83a.9959.9959 0 0 0-1.41 0L16.13 3.04l3.75 3.75 2.73-2.73z"/>
              </svg>
            </a>
            <form action="{{ route('projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Hapus project ini?')">
              @csrf @method('DELETE')
              <button class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Hapus Project">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M6 7h12v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6l1 1h4v2H4V4h4l1-1z"/>
                </svg>
              </button>
            </form>
          </div>
        </div>

        {{-- List progress per project (scrollable bila banyak) --}}
        <div class="mt-4">
          <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[280px] overflow-y-auto pr-1">
            @forelse($project->progresses as $pr)
              @php
                $last = $pr->updates->sortByDesc('update_date')->first();
                $realisasi = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;
                $canConfirm = $realisasi >= (int)$pr->desired_percent && !$pr->confirmed_at;
              @endphp

              <div class="rounded-xl bg-[#E6CACA] p-4">
                <div class="flex items-start justify-between mb-2">
                  <div class="font-semibold">Progress {{ $loop->iteration }} — {{ $pr->name }}</div>
                  <div class="flex gap-2">
                    <button type="button"
                            class="px-3 py-1.5 text-xs rounded-lg border bg-white/70 hover:bg-white"
                            onclick="document.getElementById('editProgress-{{ $pr->id }}').classList.toggle('hidden')">
                      Edit
                    </button>
                    <form method="POST" action="{{ route('progresses.destroy', $pr->id) }}"
                          onsubmit="return confirm('Hapus progress ini?');">
                      @csrf @method('DELETE')
                      <button class="px-3 py-1.5 text-xs rounded-lg border bg-white/70 hover:bg-white">
                        Hapus
                      </button>
                    </form>
                  </div>
                </div>

                <div class="text-sm grid gap-1 mb-3">
                  <div><span class="inline-block w-36 text-gray-700">Timeline Mulai</span>: {{ $pr->start_date }}</div>
                  <div><span class="inline-block w-36 text-gray-700">Timeline Selesai</span>: {{ $pr->end_date }}</div>
                  <div><span class="inline-block w-36 text-gray-700">Target Progress</span>: {{ $pr->desired_percent }}%</div>
                  <div><span class="inline-block w-36 text-gray-700">Realisasi Progress</span>: {{ $realisasi }}%</div>
                </div>

                {{-- EDIT INLINE (hidden) --}}
                <div id="editProgress-{{ $pr->id }}" class="hidden mb-3">
                  <form method="POST" action="{{ route('progresses.update', $pr->id) }}"
                        class="grid grid-cols-1 md:grid-cols-5 gap-2 bg-white/70 rounded-xl p-3 border">
                    @csrf @method('PUT')
                    <input name="name" value="{{ old('name', $pr->name) }}" required
                           class="rounded-xl bg-white border px-3 py-2 outline-none md:col-span-2" placeholder="Nama progress">
                    <input type="date" name="start_date" value="{{ old('start_date', $pr->start_date) }}" required
                           class="rounded-xl bg-white border px-3 py-2 outline-none">
                    <input type="date" name="end_date" value="{{ old('end_date', $pr->end_date) }}" required
                           class="rounded-xl bg-white border px-3 py-2 outline-none">
                    <select name="desired_percent" class="rounded-xl bg-white border px-3 py-2 outline-none" required>
                      @for($i=0;$i<=100;$i+=5)
                        <option value="{{ $i }}" @selected((int)old('desired_percent',$pr->desired_percent)===$i)>{{ $i }}%</option>
                      @endfor
                    </select>
                    <button class="h-[40px] min-w-[140px] px-4 rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-sm font-semibold">
                      Simpan Perubahan
                    </button>
                  </form>
                </div>

                {{-- UPDATE HARIAN + KONFIRM --}}
                <div class="flex items-center gap-2">
                  <form method="POST" action="{{ route('progresses.updates.store', $pr->id) }}" class="flex flex-wrap gap-2">
                    @csrf
                    <input type="date" name="update_date" value="{{ now()->toDateString() }}"
                           class="rounded-xl bg-white/80 border border-[#C89898] px-3 py-2 text-sm">
                    <input type="number" name="percent" min="0" max="100" placeholder="%" required
                           class="w-24 rounded-xl bg-white/80 border border-[#C89898] px-3 py-2 text-sm">
                    <button class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] px-3 py-2 text-xs font-semibold">
                      Update Progress
                    </button>
                  </form>

                  <form method="POST" action="{{ route('progresses.confirm', $pr->id) }}">
                    @csrf
                    <button class="rounded-xl bg-[#7A1C1C] text-white px-4 py-2 text-xs font-semibold disabled:opacity-50"
                            {{ $canConfirm ? '' : 'disabled' }}>
                      Konfirmasi
                    </button>
                  </form>
                </div>
              </div>
            @empty
              <div class="col-span-2 text-sm text-gray-600">Belum ada progress.</div>
            @endforelse
          </div>
        </div>

        <div class="mt-3 flex justify-end">
          <a href="{{ route('dig.projects.show', $project->id) }}"
             class="inline-flex items-center gap-2 rounded-lg border border-[#7A1C1C] px-3 py-1.5 text-xs font-semibold text-[#7A1C1C] bg-white hover:bg-[#FFF2F2]">
            Detail Informasi
          </a>
        </div>
      </section>
    @empty
      <div class="mt-8 rounded-2xl bg-[#EBD0D0] px-6 py-8 text-center text-gray-700">
        Belum ada project.
      </div>
    @endforelse
  </main>

  <script>
    // toggle setiap form edit progress (inline)
    document.querySelectorAll('[id^="editProgress-"]').forEach(()=>{}); // no-op (hanya agar bundler happy)
  </script>
</body>
</html>
