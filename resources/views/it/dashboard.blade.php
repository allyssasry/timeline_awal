{{-- resources/views/dig/dashboard.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard DIG</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html { scrollbar-gutter: stable; }
    body { overflow-x: hidden; }
    .scroll-thin::-webkit-scrollbar { width: 6px; }
    .scroll-thin::-webkit-scrollbar-thumb { background: #c89898; border-radius: 9999px; }
    .scroll-thin::-webkit-scrollbar-track { background: transparent; }
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
        <a href="{{ route('dig.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
        <a href="{{ route('dig.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
        <a href="{{ route('arsip.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">Developer</span>
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

  {{-- SECTION HEADER --}}
  <header class="sticky top-0 z-30 bg-[#F3DCDC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <span class="font-semibold text-gray-700 text-lg">Project</span>
    </div>
  </header>

  {{-- DAFTAR PROJECT --}}
  @if ($projects->isNotEmpty())
    <div class="max-w-6xl mx-auto px-5 mt-8 space-y-8">
      @foreach ($projects as $project)
        @php
          // Ring rata-rata dari update terbaru tiap progress
          $latestPercents = [];
          foreach ($project->progresses as $pr) {
            $last = $pr->updates->sortByDesc('update_date')->first();
            $latestPercents[] = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
          }
          $realization = count($latestPercents)
            ? (int) round(array_sum($latestPercents) / max(count($latestPercents), 1))
            : 0;
          $size = 110; $stroke = 12; $r = $size/2 - $stroke; $circ = 2 * M_PI * $r; $off = $circ * (1 - $realization/100);

          // Selesai jika semua progress sudah memenuhi target dan dikonfirmasi
          $allMetAndConfirmed = $project->progresses->every(function ($pr) {
            $last = $pr->updates->sortByDesc('update_date')->first();
            $realisasi = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
            return $realisasi >= (int)$pr->desired_percent && !is_null($pr->confirmed_at);
          });

          $isProjectDev = (int)($project->developer_id ?? 0) === (int)auth()->id() || auth()->user()?->role === 'it';
        @endphp

        <div class="rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5">
          {{-- HEADER PROJECT --}}
          <div class="grid md:grid-cols-[auto,1fr,auto] items-start gap-4">
            <div class="text-xs font-semibold {{ $allMetAndConfirmed ? 'text-green-700' : 'text-[#7A1C1C]' }}">
              {{ $allMetAndConfirmed ? 'Project Selesai' : 'Dalam Proses' }}
            </div>

            <div class="flex items-center gap-5">
              <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
                <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none" opacity=".5"/>
                <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                        stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                        transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
                <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="18" font-weight="700" fill="#7A1C1C">
                  {{ $realization }}%
                </text>
              </svg>

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

            {{-- Tombol Tambah Progress (khusus IT/dev project) --}}
            @if ($isProjectDev)
              <div class="shrink-0 flex justify-end">
                <button type="button"
                        class="btn-toggle-progress inline-flex items-center gap-2 rounded-xl bg-[#7A1C1C] text-white px-4 py-2 text-sm font-semibold shadow hover:bg-[#6a1717]"
                        data-target="progressForm-{{ $project->id }}">
                  <span class="grid place-items-center w-6 h-6 rounded-full bg-white/20 text-white">+</span>
                  Tambah Progress
                </button>
              </div>
            @endif
          </div>

          {{-- FORM TAMBAH PROGRESS (HIDDEN) --}}
          @if ($isProjectDev)
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
          @endif

          {{-- LIST PROGRESS --}}
          <div class="mt-4">
            <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[280px] overflow-y-auto pr-1">
              @forelse ($project->progresses as $pr)
                @php
                  $last      = $pr->updates->sortByDesc('update_date')->first();
                  $realisasi = $last ? (int)($last->percent ?? ($last->progress_percent ?? 0)) : 0;

                  $isOwner           = (int)($pr->created_by ?? 0) === (int)auth()->id();  // hanya pembuat
                  $alreadyConfirmed  = !is_null($pr->confirmed_at);
                  $canUpdate         = $isOwner && !$alreadyConfirmed;
                  $canConfirmBase    = $realisasi >= (int)$pr->desired_percent && !$alreadyConfirmed;
                  $canConfirm        = $isOwner && $canConfirmBase;
                @endphp

                <div class="rounded-2xl bg-[#E6CACA] p-4">
                  <div class="flex items-start justify-between">
                    <div class="font-semibold">Progress {{ $loop->iteration }} — {{ $pr->name }}</div>

                    {{-- Edit/Hapus hanya milik owner progress, dan dimatikan jika sudah dikonfirmasi --}}
                    @if ($isOwner)
                      <div class="flex gap-2">
                        <a href="{{ route('progresses.edit', $pr->id) }}"
                           class="px-3 py-1 rounded-xl bg-white text-gray-800 text-sm shadow {{ $alreadyConfirmed ? 'pointer-events-none opacity-50' : '' }}"
                           @if($alreadyConfirmed) aria-disabled="true" @endif>
                          Edit
                        </a>
                        <form method="POST" action="{{ route('progresses.destroy', $pr->id) }}"
                              onsubmit="return {{ $alreadyConfirmed ? 'false' : 'confirm(\'Hapus progress ini?\')' }};">
                          @csrf @method('DELETE')
                          <button class="px-3 py-1 rounded-xl bg-white text-gray-800 text-sm shadow"
                                  @if($alreadyConfirmed) disabled title="Sudah dikonfirmasi" @endif>
                            Hapus
                          </button>
                        </form>
                      </div>
                    @endif
                  </div>

                  {{-- INFO --}}
                  <div class="mt-2 text-sm">
                    <div class="grid grid-cols-[auto,1fr] gap-x-4 gap-y-1">
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

                      <span>Target Progress</span>    <span>: {{ (int)$pr->desired_percent }}%</span>
                      <span>Realisasi Progress</span> <span>: {{ $realisasi }}%</span>
                    </div>
                  </div>

                  {{-- AKSI: Update & Konfirmasi --}}
                  <div class="mt-3 flex flex-wrap gap-3 items-center">
                    <form method="POST" action="{{ route('progresses.updates.store', $pr->id) }}" class="flex flex-wrap gap-3 items-center">
                      @csrf
                      <input type="date" name="update_date" value="{{ now()->toDateString() }}"
                             class="rounded-xl border px-3 py-2 text-sm" @unless($canUpdate) disabled @endunless>
                      <input type="number" name="percent" min="0" max="100" placeholder="%"
                             class="rounded-xl border px-3 py-2 text-sm w-28" @unless($canUpdate) disabled @endunless>
                      <button class="rounded-xl bg-[#7A1C1C] text-white px-4 py-2 text-sm font-semibold disabled:opacity-50"
                              @unless($canUpdate) disabled title="{{ $isOwner ? 'Sudah dikonfirmasi' : 'Bukan pembuat progress' }}" @endunless>
                        Update Progress
                      </button>
                    </form>

                    @if(!$alreadyConfirmed)
                      <form method="POST" action="{{ route('progresses.confirm', $pr->id) }}">
                        @csrf
                        <button class="rounded-xl bg-green-700 text-white px-4 py-2 text-sm font-semibold disabled:opacity-50"
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

                  {{-- Catatan kecil untuk non-owner --}}
                  @unless ($isOwner)
                    <p class="mt-2 text-xs text-gray-600">
                      *Progress ini dibuat oleh {{ $pr->creator->name ?? '—' }}. Anda hanya dapat melihat tanpa mengubah.
                    </p>
                  @endunless
                </div>
              @empty
                <div class="rounded-xl bg-[#E6CACA] p-4 text-sm text-gray-700">Belum ada progress.</div>
              @endforelse
            </div>
          </div>

          {{-- DETAIL PROJECT --}}
          <div class="mt-4 flex justify-end">
            <a href="{{ route('projects.show', $project->id) }}"
               class="inline-flex items-center rounded-xl border-2 border-[#7A1C1C] text-[#7A1C1C] px-4 py-2 text-sm font-semibold bg-white hover:bg-[#FFF2F2]">
              Detail Informasi
            </a>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="max-w-6xl mx-auto px-5 mt-6">
      <div class="bg-[#EBD0D0] rounded-2xl px-6 py-8 flex items-center justify-center">
        <div class="rounded-2xl bg-[#CFA8A8] px-5 py-3 text-white/95">Anda belum memiliki project</div>
      </div>
    </div>
  @endif

  {{-- SCRIPTS --}}
  <script>
    // dropdown
    const menuBtn = document.getElementById('menuBtn');
    const menuPanel = document.getElementById('menuPanel');
    menuBtn?.addEventListener('click', (e) => {
      e.stopPropagation();
      menuPanel.classList.toggle('hidden');
    });
    menuPanel?.addEventListener('click', (e) => e.stopPropagation());
    document.addEventListener('click', () => menuPanel?.classList.add('hidden'));

    // toggle form "Tambah Progress" per project
    document.querySelectorAll('.btn-toggle-progress').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-target');
        document.getElementById(id)?.classList.toggle('hidden');
      });
    });
  </script>
</body>
</html>
