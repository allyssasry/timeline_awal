{{-- resources/views/dig/dashboard.blade.php --}}
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Dashboard DIG</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-[#F8ECEC] text-gray-900">

    {{-- NAVBAR --}}
    <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
        <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
                    class="h-8" alt="Bank Jakarta" />
            </div>
            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="#beranda" class="font-semibold">Beranda</a>
                <a href="#" class="text-gray-600 hover:text-red-600">Progress</a>
                <a href="#" class="text-gray-600 hover:text-red-600">Notifikasi</a>
                <a href="#" class="text-gray-600 hover:text-red-600">Arsip</a>
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

    {{-- BANNER --}}
    <section id="beranda" class="relative h-[260px] md:h-[320px] overflow-hidden">
        <img src="https://i.pinimg.com/736x/c5/43/71/c543719c97d9efa97da926387fa79d1f.jpg"
            class="w-full h-full object-cover" alt="Banner" />
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <h1 class="text-white text-2xl md:text-3xl font-bold">Selamat Datang di Timeline Progress</h1>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-5">

        {{-- HEADER + TOMBOL TAMBAH PROJECT --}}
        <div class="flex items-center justify-between mt-6">
            <h2 class="text-xl font-semibold">Project</h2>

            <button id="openNewProject" type="button"
                class="inline-flex items-center gap-3 rounded-full border-2 border-[#7A1C1C] bg-white hover:bg-[#FFF2F2]
                     text-[#7A1C1C] font-semibold h-[44px] pl-2 pr-4 transition shadow-sm">
                <span class="grid place-items-center w-8 h-8 rounded-full bg-[#7A1C1C] text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z" />
                    </svg>
                </span>
                Tambah Project
            </button>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- EMPTY STATE --}}
        @if ($projects->isEmpty())
            <div class="mt-6">
                <div class="bg-[#EBD0D0] rounded-2xl px-6 py-8 flex items-center justify-center">
                    <div class="rounded-2xl bg-[#CFA8A8] px-5 py-3 text-white/95">Anda belum memiliki project</div>
                </div>
            </div>
        @endif

        {{-- IKHTISAR CINCIN (rata-rata desired_percent) --}}
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-3">Progress Tercapai</h2>
            @php
                $total = 0;
                $count = 0;
                foreach ($projects as $proj) {
                    foreach ($proj->progresses ?? [] as $g) {
                        $total += (int) ($g->desired_percent ?? 0);
                        $count++;
                    }
                }
                $percent = $count ? (int) round($total / $count) : 0;
                $size = 110;
                $stroke = 12;
                $r = $size / 2 - $stroke;
                $circ = 2 * M_PI * $r;
                $off = $circ * (1 - $percent / 100);
            @endphp
            <div class="bg-[#F1DADA] rounded-2xl p-5 md:p-6 flex items-center gap-6">
                <svg width="{{ $size }}" height="{{ $size }}"
                    viewBox="0 0 {{ $size }} {{ $size }}">
                    <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" stroke="#E0BEBE"
                        stroke-width="{{ $stroke }}" fill="none" opacity=".35" />
                    <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}"
                        stroke="#7A1C1C" stroke-width="{{ $stroke }}" stroke-linecap="round"
                        stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                        transform="rotate(-90 {{ $size / 2 }} {{ $size / 2 }})" fill="none" />
                    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16"
                        font-weight="700" fill="#222">{{ $percent }}%</text>
                </svg>
                <div class="flex-1">
                    <p class="text-base md:text-lg font-medium">Ikhtisar progres</p>
                    <p class="text-sm text-gray-700">Menampilkan rata-rata <em>keinginan awal</em> dari seluruh progress
                        di semua project.</p>
                </div>
            </div>
        </div>

        {{-- DAFTAR PROJECT + PROGRESS --}}
        @if ($projects->isNotEmpty())
            <div class="mt-8 space-y-5">
                @foreach ($projects as $project)
                    @php
                        // rata-rata realisasi dari update TERBARU setiap progress
                        $latestPercents = [];
                        foreach ($project->progresses as $pr) {
                            $last = $pr->updates->first(); // sudah di-with latest() dari controller
                            $latestPercents[] = $last ? (int) ($last->progress_percent ?? ($last->percent ?? 0)) : 0;
                        }
                        $realization = count($latestPercents)
                            ? (int) round(array_sum($latestPercents) / count($latestPercents))
                            : 0;
                        // cincin per project
                        $size = 110;
                        $stroke = 12;
                        $r = $size / 2 - $stroke;
                        $circ = 2 * M_PI * $r;
                        $off = $circ * (1 - $realization / 100);
                    @endphp

                    <div class="rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-4 md:p-5">
                        <div class="grid md:grid-cols-[auto,1fr,auto] items-start gap-4">
                            <div class="flex items-center gap-3">
                                <div class="text-xs font-semibold text-[#7A1C1C]">Dalam Proses</div>
                            </div>

                            <div class="flex items-center gap-5">
                                <svg width="{{ $size }}" height="{{ $size }}"
                                    viewBox="0 0 {{ $size }} {{ $size }}">
                                    <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}"
                                        stroke="#D9B2B2" stroke-width="{{ $stroke }}" fill="none"
                                        opacity=".5" />
                                    <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}"
                                        r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                                        stroke-linecap="round" stroke-dasharray="{{ $circ }}"
                                        stroke-dashoffset="{{ $off }}"
                                        transform="rotate(-90 {{ $size / 2 }} {{ $size / 2 }})"
                                        fill="none" />
                                    <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                                        font-size="18" font-weight="700" fill="#7A1C1C">{{ $realization }}%</text>
                                </svg>

                                <div class="grid gap-1 text-sm">
                                    <div><span class="w-40 inline-block text-gray-600">Nama Project</span>: <span
                                            class="font-semibold">{{ $project->name }}</span></div>
                                    <div><span class="w-40 inline-block text-gray-600">Penanggung Jawab (Digital
                                            Banking)</span>: {{ optional($project->digitalBanking)->name ?? '-' }}
                                    </div>
                                    <div><span class="w-40 inline-block text-gray-600">Penanggung Jawab
                                            (Developer)
                                        </span>: {{ optional($project->developer)->name ?? '-' }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-2 justify-end">
                                <button class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Edit">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM22.61 5.64c.39-.39.39-1.02 0-1.41l-2.83-2.83a.9959.9959 0 0 0-1.41 0L16.13 3.04l3.75 3.75 2.73-2.73z" />
                                    </svg>
                                </button>
                                <button class="p-2 rounded-lg bg-white/60 hover:bg-white border" title="Hapus">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M6 7h12v12a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6l1 1h4v2H4V4h4l1-1z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- PROGRESS LIST + UPDATE --}}
                        <div class="mt-4 grid md:grid-cols-2 gap-4">
                            @forelse($project->progresses as $pr)
                                @php
                                    $last = $pr->updates->first();
                                    $realisasi = $last ? (int) ($last->progress_percent ?? ($last->percent ?? 0)) : 0;
                                @endphp
                                <div class="rounded-xl bg-[#E6CACA] p-4">
                                    <div class="font-semibold mb-2">Progress {{ $loop->iteration }} —
                                        {{ $pr->name }}</div>
                                    <div class="text-sm grid gap-1 mb-3">
                                        <div><span class="inline-block w-32 text-gray-700">Timeline Mulai</span>:
                                            {{ $pr->start_date }}</div>
                                        <div><span class="inline-block w-32 text-gray-700">Timeline Selesai</span>:
                                            {{ $pr->end_date }}</div>
                                        <div><span class="inline-block w-32 text-gray-700">Target Progress</span>:
                                            {{ $pr->desired_percent }}%</div>
                                        <div><span class="inline-block w-32 text-gray-700">Realisasi Progress</span>:
                                            {{ $realisasi }}%</div>
                                    </div>

                                    {{-- Form update progress --}}
                                    <form method="POST" action="{{ route('progresses.updates.store', $pr->id) }}"
                                        class="grid grid-cols-1 md:grid-cols-5 gap-2">
                                        @csrf
                                        <input type="date" name="update_date" value="{{ now()->toDateString() }}"
                                            required
                                            class="rounded-xl bg-white/80 border border-[#C89898] px-3 py-2 outline-none md:col-span-2">
                                        <input type="number" name="percent" min="0" max="100"
                                            placeholder="%" required
                                            class="rounded-xl bg-white/80 border border-[#C89898] px-3 py-2 outline-none">
                                        <input type="text" name="note" placeholder="Catatan (opsional)"
                                            class="rounded-xl bg-white/80 border border-[#C89898] px-3 py-2 outline-none md:col-span-2">
                                        <button
                                            class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] px-4 py-2 font-semibold hover:bg-[#D9AFAF]">Simpan
                                            Update</button>
                                    </form>
                                </div>
                            @empty
                                <div class="col-span-2 text-sm text-gray-600">Belum ada progress.</div>
                            @endforelse
                        </div>

                        {{-- TOMBOL + FORM TAMBAH PROGRESS (HIDE by default) --}}
                        <div class="mt-3 flex justify-end">
                            <button type="button"
                                class="btn-toggle-progress inline-flex items-center gap-2 rounded-xl bg-[#7A1C1C] text-white px-3 py-2 text-sm shadow hover:opacity-95"
                                data-target="progressForm-{{ $project->id }}" aria-expanded="false"
                                aria-controls="progressForm-{{ $project->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24"
                                    fill="currentColor">
                                    <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z" />
                                </svg>
                                Tambah Progress
                            </button>
                        </div>

                        {{-- FORM (Hidden) --}}
                        <div id="progressForm-{{ $project->id }}"
                            class="hidden mt-4 rounded-xl bg-white p-4 border border-[#E7C9C9]">
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
                                        <option value="{{ $i }}" {{ $i == 50 ? 'selected' : '' }}>
                                            {{ $i }}%</option>
                                    @endfor
                                </select>
                                <button
                                    class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] px-4 py-2 font-semibold hover:bg-[#D9AFAF]">
                                    Tambah
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

    {{-- MODAL: TAMBAH PROJECT (HANYA MUNCUL SAAT TOMBOL DIKLIK) --}}
    <div id="newProjectModal" class="hidden fixed inset-0 z-40">
        <div id="modalBackdrop" class="absolute inset-0 bg-black/40"></div>

        <div
            class="relative max-w-4xl mx-auto mt-10 mb-6 bg-[#FFF5F5] rounded-2xl shadow-xl border border-[#E7C9C9] overflow-hidden max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 bg-[#F6E4E4] border-b border-[#E7C9C9]">
                <h3 class="text-lg font-semibold">Tambah Project</h3>
                <button id="closeNewProject" class="text-[#7A1C1C] text-2xl leading-none"
                    type="button">&times;</button>
            </div>

            <form method="POST" action="{{ route('projects.store') }}"
                class="overflow-y-auto px-6 pt-5 pb-7 space-y-6">
                @csrf

                {{-- Nama Project --}}
                <section>
                    <h4 class="text-base font-semibold mb-2">Nama Project</h4>
                    <input name="name" required value="{{ old('name') }}"
                        class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                        placeholder="Tulis nama project..." />
                </section>

                {{-- Progress pertama --}}
                <section class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                    <div>
                        <h4 class="text-base font-semibold mb-2">Progress 1</h4>
                        <input name="progresses[0][name]" required value="{{ old('progresses.0.name') }}"
                            class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                            placeholder="Nama Progress" />
                    </div>
                    <div>
                        <h4 class="text-base font-semibold mb-2">Timeline Progress 1</h4>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="date" name="progresses[0][start_date]" required
                                value="{{ old('progresses.0.start_date') }}"
                                class="rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none">
                            <input type="date" name="progresses[0][end_date]" required
                                value="{{ old('progresses.0.end_date') }}"
                                class="rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none">
                        </div>
                        <label class="block text-sm font-semibold mt-3 mb-1">Keinginan Awal (Progress 1)</label>
                        <select name="progresses[0][desired_percent]" required
                            class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
                            @for ($i = 0; $i <= 100; $i += 5)
                                <option value="{{ $i }}"
                                    {{ (int) old('progresses.0.desired_percent', 75) === $i ? 'selected' : '' }}>
                                    {{ $i }}%</option>
                            @endfor
                        </select>
                    </div>

                    {{-- tambah progress dinamis --}}
                    <button type="button" id="addProgressRow"
                        class="md:absolute md:-right-2 md:-bottom-2 w-9 h-9 rounded-full border border-[#C89898] bg-white text-[#7A1C1C] grid place-content-center shadow">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z" />
                        </svg>
                    </button>
                </section>

                <div id="moreProgress" class="space-y-4"></div>

                {{-- PJ & Deskripsi --}}
                @php $me = auth()->user(); @endphp
                <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <h4 class="text-base font-semibold mb-2">Penanggung Jawab (Digital Banking)</h4>
                            <select name="digital_banking_id" required
                                class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
                                <option value="">Pilih Nama</option>
                                @if ($me && $me->role === 'digital_banking')
                                    <optgroup label="Saya">
                                        <option value="{{ $me->id }}"
                                            {{ (string) old('digital_banking_id', $me->id) === (string) $me->id ? 'selected' : '' }}>
                                            {{ $me->name }} {{ $me->username ? '(' . $me->username . ')' : '' }}
                                        </option>
                                    </optgroup>
                                @endif
                                <optgroup label="Semua User Digital Banking">
                                    @forelse(($digitalUsers ?? collect()) as $u)
                                        @continue($me && $me->role === 'digital_banking' && (string) $u->id === (string) $me->id)
                                        <option value="{{ $u->id }}"
                                            {{ (string) old('digital_banking_id', $me && $me->role === 'digital_banking' ? $me->id : '') === (string) $u->id ? 'selected' : '' }}>
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
                                @if ($me && $me->role === 'it')
                                    <optgroup label="Saya">
                                        <option value="{{ $me->id }}"
                                            {{ (string) old('developer_id', $me->id) === (string) $me->id ? 'selected' : '' }}>
                                            {{ $me->name }} {{ $me->username ? '(' . $me->username . ')' : '' }}
                                        </option>
                                    </optgroup>
                                @endif
                                <optgroup label="Semua User IT">
                                    @forelse(($itUsers ?? collect()) as $u)
                                        @continue($me && $me->role === 'it' && (string) $u->id === (string) $me->id)
                                        <option value="{{ $u->id }}"
                                            {{ (string) old('developer_id', $me && $me->role === 'it' ? $me->id : '') === (string) $u->id ? 'selected' : '' }}>
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
                        class="w-[140px] h-[40px] rounded-full border-2 border-[#7A1C1C] bg-white text-[#7A1C1C] font-semibold">
                        Batal
                    </button>
                    <button type="submit"
                        class="w-[140px] h-[40px] rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-black font-semibold">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        (function() {
            // dropdown
            const btn = document.getElementById('menuBtn');
            const panel = document.getElementById('menuPanel');
            btn?.addEventListener('click', () => panel.classList.toggle('hidden'));
            document.addEventListener('click', (e) => {
                if (!btn?.contains(e.target) && !panel?.contains(e.target)) panel?.classList.add('hidden');
            });

            // modal open/close
            const modal = document.getElementById('newProjectModal');
            const openBtn = document.getElementById('openNewProject');
            const closeBtn = document.getElementById('closeNewProject');
            const cancel = document.getElementById('cancelNewProject');
            const backdrop = document.getElementById('modalBackdrop');

            const openModal = () => modal.classList.remove('hidden');
            const closeModal = () => modal.classList.add('hidden');

            openBtn?.addEventListener('click', openModal);
            closeBtn?.addEventListener('click', closeModal);
            cancel?.addEventListener('click', closeModal);
            backdrop?.addEventListener('click', closeModal);
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape') closeModal();
            });

            // tambah progress dinamis
            const addBtn = document.getElementById('addProgressRow');
            const wrap = document.getElementById('moreProgress');
            let idx = 1;
            addBtn?.addEventListener('click', () => {
                const options = Array.from({
                    length: 21
                }).map((_, k) =>
                    `<option value="${k*5}" ${k===15?'selected':''}>${k*5}%</option>`).join('');
                const row = document.createElement('div');
                row.className = 'grid grid-cols-1 md:grid-cols-2 gap-4';
                row.innerHTML = `
        <div>
          <h4 class="text-base font-semibold mb-2">Progress ${idx+1}</h4>
          <input name="progresses[${idx}][name]" required
                 class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                 placeholder="Nama Progress" />
        </div>
        <div>
          <h4 class="text-base font-semibold mb-2">Timeline Progress ${idx+1}</h4>
          <div class="grid grid-cols-2 gap-2">
            <input type="date" name="progresses[${idx}][start_date]" required
                   class="rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none">
            <input type="date" name="progresses[${idx}][end_date]" required
                   class="rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none">
          </div>
          <label class="block text-sm font-semibold mt-3 mb-1">Keinginan Awal (Progress ${idx+1})</label>
          <select name="progresses[${idx}][desired_percent]" required
                  class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
            ${options}
          </select>
        </div>`;
                wrap.appendChild(row);
                idx++;
            });
        })();

        // toggle form "Tambah Progress" per project
        document.querySelectorAll('.btn-toggle-progress').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-target');
                const panel = document.getElementById(id);
                if (!panel) return;
                panel.classList.toggle('hidden');
                const expanded = panel.classList.contains('hidden') ? 'false' : 'true';
                btn.setAttribute('aria-expanded', expanded);
            });
        });
    </script>
</body>

</html>
