{{-- resources/views/it/dashboard.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard IT</title>
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
        <a href="{{ route('semua.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
        <a href="{{ route('it.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">IT</span>
      </nav>

      <div class="relative">
        <button id="menuBtn" class="p-2 rounded-xl border border-red-200 text-red-700 hover:bg-red-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h6v6h-6v-6z" />
          </svg>
        </button>
        <div id="menuPanel" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-[#7A1C1C] text-white overflow-hidden">
          <a href="{{ route('account.setting') }}" class="block px-4 py-3 hover:bg-[#6a1717]">Pengaturan Akun</a>
          <a href="/logout" class="block px-4 py-3 hover:bg-[#6a1717]">Log Out</a>
        </div>
      </div>
    </div>
  </header>

  {{-- BANNER --}}
  <section id="beranda" class="relative h-[200px] md:h-[260px] overflow-hidden">
    <img src="https://i.pinimg.com/736x/c5/43/71/c543719c97d9efa97da926387fa79d1f.jpg" class="w-full h-full object-cover" alt="Banner" />
    <div class="absolute inset-0 bg-black/30"></div>
    <div class="absolute inset-0 flex items-center justify-center">
      <h1 class="text-white text-2xl md:text-3xl font-bold">Dashboard IT</h1>
    </div>
  </section>

  {{-- SECTION HEADER + TOMBOL BUAT PROJECT (modal) --}}
  <header class="sticky top-0 z-30 bg-[#F3DCDC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <span class="font-semibold text-gray-700 text-lg">Project</span>

      <div class="flex justify-end">
        <button id="openNewProject" type="button"
          class="inline-flex items-center gap-2 rounded-full border border-[#7A1C1C] bg-white hover:bg-[#FFF2F2] text-[#7A1C1C] font-medium h-[32px] px-3 text-sm shadow-sm">
          <span class="grid place-items-center w-6 h-6 rounded-full bg-[#7A1C1C] text-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
              <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z" />
            </svg>
          </span>
          Buat Project
        </button>
      </div>
    </div>
  </header>

  {{-- NOTIFIKASI --}}
  <div class="max-w-6xl mx-auto px-5">
    @if (session('success'))
      <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
      <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
      <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Terjadi kesalahan:</div>
        <ul class="list-disc pl-5 space-y-1">
          @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
      </div>
    @endif
  </div>

  {{-- DAFTAR PROJECT --}}
  @if ($projects->isNotEmpty())
    <div class="max-w-6xl mx-auto px-5 mt-8 space-y-8">
      @foreach ($projects as $project)
        @php
          // Ring progress: rata-rata progress terakhir per progress
          $latestPercents = [];
          foreach ($project->progresses as $pr) {
            $last = $pr->updates->sortByDesc('update_date')->first();
            $latestPercents[] = $last ? (int)($last->progress_percent ?? ($last->percent ?? 0)) : 0;
          }
          $realization = count($latestPercents) ? (int) round(array_sum($latestPercents) / max(count($latestPercents), 1)) : 0;

          $size=110; $stroke=12; $r=$size/2-$stroke; $circ=2*M_PI*$r; $off=$circ*(1-$realization/100);

          // STATUS (termasuk "Menunggu Finalisasi") – finalisasi tetap hak DIG
          $finalized = !is_null($project->meets_requirement) || !is_null($project->completed_at);
          $progressCount = $project->progresses->count();
          $allConfirmedAndMet = $progressCount > 0 && $project->progresses->every(function($p) {
            $last = $p->updates->sortByDesc('update_date')->first();
            $real = $last ? (int)($last->percent ?? ($last->progress_percent ?? 0)) : 0;
            return !is_null($p->confirmed_at) && $real >= (int)$p->desired_percent;
          });

          if ($finalized) {
            $statusText  = $project->meets_requirement ? 'Project Selesai, Memenuhi' : 'Project Selesai, Tidak Memenuhi';
            $statusColor = $project->meets_requirement ? '#166534' : '#7A1C1C';
          } elseif ($allConfirmedAndMet) {
            $statusText  = 'Menunggu Finalisasi (DIG)';
            $statusColor = '#7A1C1C';
          } else {
            $statusText  = 'Dalam Proses';
            $statusColor = '#7A1C1C';
          }

          $authId = (int) auth()->id();
          $isProjectOwnerIT = (int)($project->created_by ?? 0) === $authId; // hanya pemilik yg bisa edit/hapus
        @endphp

        <div class="rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5">
          {{-- HEADER PROJECT --}}
          <div class="grid md:grid-cols-[1fr,auto] items-start gap-4">
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

            {{-- BADGE + AKSI OWNER --}}
            <div class="flex items-start justify-end gap-2">
              @if($isProjectOwnerIT)
                <a href="{{ route('projects.edit', $project->id) }}"
                   class="px-3 py-1 rounded-xl bg-white text-gray-800 text-xs shadow hover:bg-[#FFF2F2]">
                  Edit Project
                </a>
                <form method="POST" action="{{ route('projects.destroy', $project->id) }}"
                      onsubmit="return confirm('Yakin ingin menghapus project ini? Aksi ini tidak bisa dibatalkan.');">
                  @csrf @method('DELETE')
                  <button class="px-3 py-1 rounded-xl bg-white text-gray-800 text-xs shadow hover:bg-[#FFF2F2]">
                    Hapus
                  </button>
                </form>
              @endif

              <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                    style="color: {{ $statusColor }}; background-color: {{ $finalized ? '#DCFCE7' : '#FEE2E2' }};">
                {{ $statusText }}
              </span>
            </div>
          </div>

          {{-- LIST PROGRESS --}}
          <div class="mt-4">
            <div class="scroll-thin grid md:grid-cols-2 gap-4 max-h-[280px] overflow-y-auto pr-1">
              @forelse ($project->progresses as $pr)
                @php
                  $last      = $pr->updates->sortByDesc('update_date')->first();
                  $realisasi = $last ? (int)($last->percent ?? ($last->progress_percent ?? 0)) : 0;

                  $isOwner          = (int)($pr->created_by ?? 0) === $authId;  // owner progress (bisa IT/DIG)
                  $alreadyConfirmed = !is_null($pr->confirmed_at);

                  // Aturan IT: boleh update progress bila PEMILIK progress (IT) & belum dikonfirmasi
                  $canUpdate        = $isOwner && !$alreadyConfirmed;

                  // Boleh konfirmasi selesai bila PEMILIK (IT) & realisasi >= target & belum dikonfirmasi
                  $canConfirmBase   = $realisasi >= (int)$pr->desired_percent && !$alreadyConfirmed;
                  $canConfirmIT     = $isOwner && $canConfirmBase;

                  // (Opsional) izinkan konfirmasi juga bila sudah lewat timeline (meski < target) -> uncomment kalau mau
                  $endDate   = $pr->end_date ? \Illuminate\Support\Carbon::parse($pr->end_date)->startOfDay() : null;
                  $isOverdue = $endDate ? $endDate->lt(now()->startOfDay()) : false;
                  // $canConfirmIT = $canConfirmIT || ($isOwner && $isOverdue && !$alreadyConfirmed);
                @endphp

                <div class="rounded-2xl bg-[#E6CACA] p-4">
                  <div class="flex items-start justify-between">
                    <div class="font-semibold">Progress {{ $loop->iteration }} — {{ $pr->name }}</div>

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

                  {{-- UPDATE + KONFIRMASI (IT) --}}
                  <div class="mt-3">
                    {{-- UPDATE --}}
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

                    {{-- KONFIRMASI SELESAI (IT OWNER) --}}
                    <div class="mt-2">
                      @if(!$alreadyConfirmed)
                        <form method="POST" action="{{ route('progresses.confirm', $pr->id) }}">
                          @csrf
                          <button
                            class="rounded-xl bg-green-700 text-white px-4 py-2 text-sm font-semibold disabled:opacity-50"
                            {{ $canConfirmIT ? '' : 'disabled' }}
                            title="{{ $isOwner ? 'Belum mencapai target' : 'Hanya pembuat progress (IT) yang dapat konfirmasi' }}">
                            Konfirmasi Selesai
                          </button>
                        </form>
                      @else
                        <span class="inline-flex items-center rounded-full bg-green-100 text-green-700 px-3 py-1 text-xs font-semibold">
                          Sudah dikonfirmasi
                        </span>
                      @endif
                    </div>
                  </div>

                  @unless ($isOwner)
                    <p class="mt-2 text-xs text-gray-600">
                      *Progress ini dibuat oleh {{ $pr->creator->name ?? '—' }}. Anda hanya dapat melihat tanpa mengubah.
                    </p>
                  @endunless
                </div>
              @empty
                <div class="rounded-2xl bg-[#E6CACA] p-4 text-sm text-gray-700">Belum ada progress.</div>
              @endforelse
            </div>
          </div>

          {{-- DETAIL --}}
          <div class="mt-4 flex justify-end">
            <a href="{{ route('semua.progresses', $project->id) }}"
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

  {{-- MODAL BUAT PROJECT (versi IT) --}}
  <div id="newProjectModal" class="hidden fixed inset-0 z-40">
    <div id="modalBackdrop" class="absolute inset-0 bg-black/40"></div>
    <div class="relative max-w-4xl mx-auto my-10 bg-[#FFF5F5] rounded-2xl shadow-xl border border-[#E7C9C9] max-h-[90vh] flex flex-col overflow-hidden">
      {{-- HEADER --}}
      <div class="flex items-center justify-between px-6 py-4 bg-[#F6E4E4] border-b border-[#E7C9C9]">
        <h3 class="text-lg font-semibold">Buat Project</h3>
        <button id="closeNewProject" class="text-[#7A1C1C] text-2xl leading-none" type="button">&times;</button>
      </div>

      {{-- FORM --}}
      <form method="POST" action="{{ route('projects.store') }}" class="flex-1 overflow-y-auto px-6 pt-5 pb-7 space-y-6">
        @csrf

        <section>
          <h4 class="text-base font-semibold mb-2">Nama Project</h4>
          <input name="name" required value="{{ old('name') }}"
                 class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                 placeholder="Tulis nama project..." />
        </section>

        {{-- PROGRESSES DINAMIS --}}
        <section class="space-y-3">
          <div class="flex items-center justify-between">
            <h4 class="text-base font-semibold">Daftar Progress</h4>
            <button type="button" id="addProgressBtn"
              class="inline-flex items-center gap-2 rounded-full border-2 border-[#7A1C1C] bg-white hover:bg-[#FFF2F2] text-[#7A1C1C] font-semibold h-[36px] px-3">
              <span class="grid place-items-center w-6 h-6 rounded-full bg-[#7A1C1C] text-white leading-none">+</span>
              Tambah Progress
            </button>
          </div>

          <div id="progressList" class="space-y-4">
            <div class="progress-row rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] p-4" data-index="0">
              <div class="flex items-center justify-between mb-3">
                <div class="font-semibold text-sm">Progress <span class="progress-number">1</span></div>
                <button type="button" class="removeProgressBtn text-xs px-2 py-1 rounded-lg border border-red-300 text-red-700 hover:bg-red-50" disabled>Hapus</button>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-semibold mb-1 block">Nama Progress</label>
                  <input name="progresses[0][name]" required value="{{ old('progresses.0.name') }}"
                         class="w-full rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none"
                         placeholder="Nama Progress" />
                </div>

                <div>
                  <label class="text-sm font-semibold mb-1 block">Timeline</label>
                  <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="progresses[0][start_date]" required
                           value="{{ old('progresses.0.start_date') }}"
                           class="rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none">
                    <input type="date" name="progresses[0][end_date]" required
                           value="{{ old('progresses.0.end_date') }}"
                           class="rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none">
                  </div>
                  <label class="block text-sm font-semibold mt-3 mb-1">Target (%)</label>
                  <select name="progresses[0][desired_percent]" required
                          class="w-full rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
                    @for ($i = 0; $i <= 100; $i += 5)
                      <option value="{{ $i }}" {{ (int) old('progresses.0.desired_percent', 75) === $i ? 'selected' : '' }}>
                        {{ $i }}%
                      </option>
                    @endfor
                  </select>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- TEMPLATE ROW PROGRESS --}}
        <template id="progressRowTemplate">
          <div class="progress-row rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] p-4" data-index="__INDEX__">
            <div class="flex items-center justify-between mb-3">
              <div class="font-semibold text-sm">Progress <span class="progress-number">__NUMBER__</span></div>
              <button type="button" class="removeProgressBtn text-xs px-2 py-1 rounded-lg border border-red-300 text-red-700 hover:bg-red-50">Hapus</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-semibold mb-1 block">Nama Progress</label>
                <input name="progresses[__INDEX__][name]" required
                       class="w-full rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none"
                       placeholder="Nama Progress" />
              </div>

              <div>
                <label class="text-sm font-semibold mb-1 block">Timeline</label>
                <div class="grid grid-cols-2 gap-2">
                  <input type="date" name="progresses[__INDEX__][start_date]" required
                         class="rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none">
                  <input type="date" name="progresses[__INDEX__][end_date]" required
                         class="rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none">
                </div>
                <label class="block text-sm font-semibold mt-3 mb-1">Target (%)</label>
                <select name="progresses[__INDEX__][desired_percent]" required
                        class="w-full rounded-xl bg-white/80 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
                  @for ($i = 0; $i <= 100; $i += 5)
                    <option value="{{ $i }}">{{ $i }}%</option>
                  @endfor
                </select>
              </div>
            </div>
          </div>
        </template>

        {{-- PJ & Deskripsi --}}
        @php $me = auth()->user(); @endphp
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="grid grid-cols-1 gap-5">
            <div>
              <h4 class="text-base font-semibold mb-2">Penanggung Jawab (Digital Banking)</h4>
              <select name="digital_banking_id" required
                      class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
                <option value="">Pilih Nama</option>
                <optgroup label="Semua User Digital Banking">
                  @forelse(($digUsers ?? collect()) as $u)
                    <option value="{{ $u->id }}" {{ (string)old('digital_banking_id','') === (string)$u->id ? 'selected' : '' }}>
                      {{ $u->name }} {{ $u->username ? '(' . $u->username . ')' : '' }}
                    </option>
                  @empty
                    <option value="" disabled>Belum ada user role Digital Banking</option>
                  @endforelse
                </optgroup>
              </select>
            </div>

            <div>
              <h4 class="text-base font-semibold mb-2">Penanggung Jawab (Developer / IT)</h4>
              <select name="developer_id" required
                      class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
                <option value="">Pilih Nama</option>
                {{-- Preselect diri sendiri (IT) --}}
                <optgroup label="Saya">
                  <option value="{{ $me->id }}" {{ (string)old('developer_id', $me->id) === (string)$me->id ? 'selected' : '' }}>
                    {{ $me->name }} {{ $me->username ? '(' . $me->username . ')' : '' }}
                  </option>
                </optgroup>

                <optgroup label="Semua User IT">
                  @forelse(($itUsers ?? collect()) as $u)
                    @continue((string)$u->id === (string)$me->id)
                    <option value="{{ $u->id }}" {{ (string)old('developer_id', $me->id) === (string)$u->id ? 'selected' : '' }}>
                      {{ $u->name }} {{ $u->username ? '(' . $u->username . ')' : '' }}
                    </option>
                  @empty
                    <option value="" disabled>Belum ada user role IT</option>
                  @endforelse
                </optgroup>
              </select>
            </div>
          </div>

          <div>
            <h4 class="text-base font-semibold mb-2">Deskripsi (Opsional)</h4>
            <textarea name="description" rows="5"
                      class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                      placeholder="Tuliskan deskripsi project...">{{ old('description') }}</textarea>
          </div>
        </section>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" id="cancelNewProject"
                  class="w-[140px] h-[40px] rounded-full border-2 border-[#7A1C1C] bg-white text-[#7A1C1C] font-semibold">Batal</button>
          <button type="submit"
                  class="w-[140px] h-[40px] rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-black font-semibold">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  {{-- SCRIPTS --}}
  <script>
    // dropdown
    const menuBtn = document.getElementById('menuBtn');
    const menuPanel = document.getElementById('menuPanel');
    menuBtn?.addEventListener('click', () => menuPanel.classList.toggle('hidden'));
    document.addEventListener('click', (e) => {
      if (!menuBtn?.contains(e.target) && !menuPanel?.contains(e.target)) menuPanel?.classList.add('hidden');
    });

    // modal open/close
    const modal = document.getElementById('newProjectModal');
    const openNewProject = document.getElementById('openNewProject');
    const closeNewProject = document.getElementById('closeNewProject');
    const cancelNewProject = document.getElementById('cancelNewProject');
    const backdrop = document.getElementById('modalBackdrop');
    const openModal = () => modal.classList.remove('hidden');
    const closeModal = () => modal.classList.add('hidden');
    openNewProject?.addEventListener('click', openModal);
    closeNewProject?.addEventListener('click', closeModal);
    cancelNewProject?.addEventListener('click', closeModal);
    backdrop?.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    // progress rows (dinamis)
    (function() {
      const list = document.getElementById('progressList');
      const addBtn = document.getElementById('addProgressBtn');
      const tpl = document.getElementById('progressRowTemplate');
      if (!list || !addBtn || !tpl) return;

      const renumber = () => {
        const rows = list.querySelectorAll('.progress-row');
        rows.forEach((row, i) => {
          row.querySelector('.progress-number').textContent = i + 1;
          const removeBtn = row.querySelector('.removeProgressBtn');
          if (removeBtn) removeBtn.disabled = rows.length <= 1;
        });
      };

      const reindexNames = () => {
        const rows = list.querySelectorAll('.progress-row');
        rows.forEach((row, idx) => {
          row.dataset.index = idx;
          row.querySelectorAll('input[name], select[name]').forEach(el => {
            el.name = el.name.replace(/progresses\[\d+\]/, `progresses[${idx}]`);
          });
        });
      };

      const addRow = () => {
        const currentIndex = list.querySelectorAll('.progress-row').length;
        const html = tpl.innerHTML
          .replaceAll('__INDEX__', currentIndex)
          .replaceAll('__NUMBER__', currentIndex + 1);
        const container = document.createElement('div');
        container.innerHTML = html.trim();
        const row = container.firstElementChild;

        row.querySelector('.removeProgressBtn').addEventListener('click', () => {
          row.remove();
          renumber();
          reindexNames();
        });

        list.appendChild(row);
        renumber();
      };

      addBtn.addEventListener('click', addRow);

      const firstRemove = list.querySelector('.removeProgressBtn');
      if (firstRemove) {
        firstRemove.addEventListener('click', (e) => {
          const rows = list.querySelectorAll('.progress-row');
          if (rows.length > 1) {
            e.currentTarget.closest('.progress-row').remove();
            renumber();
            reindexNames();
          }
        });
      }
    })();
  </script>
</body>
</html>
