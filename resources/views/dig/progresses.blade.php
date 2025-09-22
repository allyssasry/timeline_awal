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

  {{-- NAVBAR --}}
  <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta" />
      </div>

      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="{{ url('/dig/dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
        <a href="{{ route('dig.progresses') }}" class="font-semibold">Progress</a>
        <a href="{{ route('dig.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">DIG</span>
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
      $q = request('status','all'); // all|in_progress|done
      $tab = fn($v) => $q===$v ? 'bg-[#7A1C1C] text-white' : 'bg-white text-[#7A1C1C] hover:bg-[#FFF2F2]';
    @endphp
    <div class="flex gap-3">
      <a href="{{ route('dig.progresses',['status'=>'all']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('all') }} grid place-items-center">Semua</a>
      <a href="{{ route('dig.progresses',['status'=>'in_progress']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('in_progress') }} grid place-items-center">Dalam Proses</a>
      <a href="{{ route('dig.progresses',['status'=>'done']) }}"
         class="rounded-[12px] h-9 px-5 text-sm font-semibold border-2 border-[#7A1C1C] {{ $tab('done') }} grid place-items-center">Telah Selesai</a>
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
        // rata2 realisasi dari update terbaru tiap progress (untuk cincin)
        $latestPercents = [];
        foreach ($project->progresses as $pr) {
          $last = $pr->updates->sortByDesc('update_date')->first();
          $latestPercents[] = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;
        }
        $realization = count($latestPercents) ? (int) round(array_sum($latestPercents)/max(count($latestPercents),1)) : 0;
        $size=88; $stroke=10; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);

        $allMetAndConfirmed = $project->progresses->every(function ($p) {
          $u = $p->updates->sortByDesc('update_date')->first();
          $real = $u ? (int)($u->percent ?? $u->progress_percent ?? 0) : 0;
          return $real >= (int)$p->desired_percent && !is_null($p->confirmed_at);
        });
      @endphp

      <section class="mt-6 rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5">
        {{-- HEADER PROJECT --}}
        <div class="grid md:grid-cols-[auto,1fr,auto] items-start gap-4">
          <div class="text-xs font-semibold {{ $allMetAndConfirmed ? 'text-green-700' : 'text-[#7A1C1C]' }}">
            {{ $allMetAndConfirmed ? 'Project Selesai' : 'Dalam Proses' }}
          </div>

          <div class="flex items-center gap-5">
            {{-- cincin --}}
            <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
              <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                      stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                      transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
              <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
            </svg>

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

          {{-- Aksi Project --}}
          <div class="flex items-start gap-2 justify-end">
            <a href="{{ route('projects.edit', $project->id) }}"
               class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Edit Project">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM22.61 5.64c.39-.39.39-1.02 0-1.41l-2.83-2.83a.9959.9959 0 0 0-1.41 0L16.13 3.04l3.75 3.75 2.73-2.73z"/>
              </svg>
            </a>
            <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus project ini? Aksi ini tidak bisa dibatalkan.');">
              @csrf @method('DELETE')
              <button type="submit" class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Hapus Project">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M6 7h12v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6l1 1h4v2H4V4h4l1-1z"/>
                </svg>
              </button>
            </form>
          </div>
        </div>

        {{-- TOMBOL TAMBAH PROGRESS --}}
        <div class="mt-4 flex justify-end">
          <button type="button"
            class="btn-toggle-progress inline-flex items-center gap-2 rounded-xl bg-[#7A1C1C] text-white px-3 py-2 text-sm shadow hover:opacity-95"
            data-target="progressForm-{{ $project->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/>
            </svg>
            Tambah Progress
          </button>
        </div>

        {{-- FORM TAMBAH PROGRESS (HIDDEN) --}}
        <div id="progressForm-{{ $project->id }}" class="hidden mt-3 rounded-xl bg-white p-4 border border-[#E7C9C9]">
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
            <button class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] px-4 py-2 font-semibold hover:bg-[#D9AFAF]">
              Tambah
            </button>
          </form>
        </div>

        {{-- LIST PROGRESS --}}
        <div class="mt-4">
          <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[280px] overflow-y-auto pr-1">
            @forelse($project->progresses as $pr)
              @php
                $last              = $pr->updates->sortByDesc('update_date')->first();
                $realisasi         = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;

                // hanya owner progress
                $isOwner           = (int)($pr->created_by ?? 0) === (int)auth()->id();

                // status konfirmasi
                $alreadyConfirmed  = !is_null($pr->confirmed_at);

                // role user sekarang
                $isDig             = auth()->user()?->role === 'digital_banking';

                // aturan: Update hanya DIG + owner + belum confirmed
                $canUpdate         = $isOwner && $isDig && !$alreadyConfirmed;

                // aturan: Konfirmasi hanya owner, capai target, belum confirmed
                $canConfirmBase    = $realisasi >= (int)$pr->desired_percent && !$alreadyConfirmed;
                $canConfirm        = $isOwner && $canConfirmBase;

                // label pembuat
                $creator           = $pr->creator ?? null;
                $role              = $creator?->role;
                $ownerRoleLabel    = $role === 'digital_banking' ? 'DIG' : ($role === 'it' ? 'IT' : '—');
              @endphp

              <div class="rounded-xl bg-[#E6CACA] p-4">
                <div class="flex items-start justify-between mb-2">
                  <div class="font-semibold">Progress {{ $loop->iteration }} — {{ $pr->name }}</div>

                  {{-- Edit/Hapus hanya owner --}}
                  @if($isOwner)
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
                  @endif
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
                      class="rounded-xl bg-white border px-3 py-2 outline-none md:col-span-2" placeholder="Nama progress"
                      @unless($isOwner) disabled @endunless>
                    <input type="date" name="start_date" value="{{ old('start_date', $pr->start_date) }}" required
                      class="rounded-xl bg-white border px-3 py-2 outline-none"
                      @unless($isOwner) disabled @endunless>
                    <input type="date" name="end_date" value="{{ old('end_date', $pr->end_date) }}" required
                      class="rounded-xl bg-white border px-3 py-2 outline-none"
                      @unless($isOwner) disabled @endunless>
                    <select name="desired_percent" class="rounded-xl bg-white border px-3 py-2 outline-none" required
                      @unless($isOwner) disabled @endunless>
                      @for($i=0;$i<=100;$i+=5)
                        <option value="{{ $i }}" @selected((int)old('desired_percent',$pr->desired_percent)===$i)>{{ $i }}%</option>
                      @endfor
                    </select>
                    <button class="h-[40px] min-w-[140px] px-4 rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-xs font-semibold"
                      @unless($isOwner) disabled @endunless>
                      Simpan Perubahan
                    </button>
                  </form>
                </div>

                {{-- UPDATE + KONFIRMASI (Konfirmasi di bawah) --}}
                <div class="mt-3">
                  {{-- UPDATE --}}
                  <form method="POST" action="{{ route('progresses.updates.store', $pr->id) }}" class="flex flex-wrap gap-3 items-center">
                    @csrf
                    <input type="date" name="update_date" value="{{ now()->toDateString() }}"
                      class="rounded-xl border px-3 py-2 text-sm" @unless($canUpdate) disabled @endunless>
                    <input type="number" name="percent" min="0" max="100" placeholder="%"
                      class="rounded-xl border px-3 py-2 text-sm w-28" @unless($canUpdate) disabled @endunless required>
                    <button
                      class="rounded-xl bg-[#7A1C1C] text-white px-4 py-2 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                      @unless($canUpdate) disabled title="{{ $alreadyConfirmed ? 'Sudah dikonfirmasi' : ($isOwner ? 'Hanya DIG yang bisa update' : 'Bukan pembuat progress') }}" @endunless>
                      Update Progress
                    </button>
                  </form>

                  {{-- KONFIRMASI --}}
                  <div class="mt-2">
                    @if(!$alreadyConfirmed)
                      <form method="POST" action="{{ route('progresses.confirm', $pr->id) }}">
                        @csrf
                        <button
                          class="rounded-xl bg-green-700 text-white px-4 py-2 text-sm font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
                          {{ $canConfirm ? '' : 'disabled' }}
                          title="{{ $isOwner ? 'Belum mencapai target' : 'Hanya pembuat progress yang dapat konfirmasi' }}">
                          Konfirmasi
                        </button>
                      </form>
                    @else
                      <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-semibold">
                        Sudah dikonfirmasi
                      </span>
                    @endif
                  </div>
                </div>

                @unless($isOwner)
                  <p class="mt-2 text-xs text-gray-600">
                    *Progress ini dibuat oleh <strong>{{ $ownerRoleLabel }}</strong> — {{ $creator?->name ?? '—' }}. Anda hanya dapat melihat tanpa mengubah.
                  </p>
                @endunless
              </div>
            @empty
              <div class="col-span-2 text-sm text-gray-600">Belum ada progress.</div>
            @endforelse
          </div>
        </div>

        {{-- DETAIL INFO --}}
        <div class="mt-4 flex justify-end">
          <a href="{{ route('dig.projects.show', $project->id) }}"
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

    // Toggle form "Tambah Progress" per project
    document.querySelectorAll('.btn-toggle-progress').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-target');
        document.getElementById(id)?.classList.toggle('hidden');
      });
    });
  </script>
</body>
</html>
