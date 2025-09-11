<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard IT</title>
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
            {{-- menu kanan (kebab) --}}
     
            <div class="relative">
                <button id="menuBtn" class="p-2 rounded-xl border border-red-200 text-red-700 hover:bg-red-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h6v6h-6v-6z" />
                    </svg>
                </button>
                <div id="menuPanel"
                    class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-[#7A1C1C] text-white overflow-hidden">
                    <a href="#" class="block px-4 py-3 hover:bg-[#6a1717]">
                        Pengaturan Akun
                    </a>
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
      <h1 class="text-white text-2xl md:text-3xl font-bold">
        Selamat Datang di Timeline Progress
      </h1>
    </div>
  </section>

  <main class="max-w-6xl mx-auto px-5 py-6">
    @if(session('success'))
      <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
        {{ session('success') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Terjadi kesalahan:</div>
        <ul class="list-disc pl-5 space-y-1">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @forelse($projects as $project)
      <section class="mb-6 rounded-2xl border border-[#E7C9C9] bg-[#FFF5F5] p-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">{{ $project->name }}</h2>
            <p class="text-sm text-gray-600">Digital Banking: {{ optional($project->digitalBanking)->name ?? '-' }}</p>
          </div>
        </div>

        {{-- List Progress --}}
        <div class="mt-4 grid gap-3">
          @forelse($project->progresses as $pr)
            <div class="rounded-xl border bg-white p-4">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <div class="font-semibold">{{ $pr->name }}</div>
                  <div class="text-xs text-gray-500">({{ $pr->start_date }} – {{ $pr->end_date }}) • Keinginan awal: {{ $pr->desired_percent }}%</div>
                </div>
                <div class="text-right">
                  <div class="text-sm">Terbaru: <span class="font-semibold">{{ $pr->latest_percent }}%</span></div>
                  @php $last = $pr->updates->first(); @endphp
                  <div class="text-xs text-gray-500">
                    @if($last)
                      {{ $last->update_date }} • {{ Str::limit($last->note, 40) }}
                    @else
                      Belum ada update
                    @endif
                  </div>
                </div>
              </div>

              {{-- Form Update Harian --}}
              <form class="mt-3 grid grid-cols-1 md:grid-cols-5 gap-2"
                    method="POST" action="{{ route('progresses.updates.store', $pr->id) }}">
                @csrf
                <input type="date" name="update_date" required
                       value="{{ now()->toDateString() }}"
                       class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none md:col-span-2">
                <input type="number" name="percent" required min="0" max="100" placeholder="%"
                       class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none">
                <input type="text" name="note" placeholder="Catatan (opsional)"
                       class="rounded-xl bg-[#E2B9B9]/40 border border-[#C89898] px-3 py-2 outline-none md:col-span-2">
                <button class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] px-4 py-2 font-semibold hover:bg-[#D9AFAF]">
                  Simpan Update
                </button>
              </form>
            </div>
          @empty
            <div class="rounded-xl border bg-white p-4 text-gray-600">Belum ada progress.</div>
          @endforelse
        </div>

        {{-- Form Tambah Progress (IT) --}}
        <div class="mt-4 rounded-xl border bg-white p-4">
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
              @for($i=0;$i<=100;$i+=5)
                <option value="{{ $i }}" {{ $i==50 ? 'selected':'' }}>{{ $i }}%</option>
              @endfor
            </select>
            <button class="rounded-xl border-2 border-[#7A1C1C] bg-[#E2B9B9] px-4 py-2 font-semibold hover:bg-[#D9AFAF]">
              Tambah
            </button>
          </form>
        </div>
      </section>
    @empty
      <div class="rounded-2xl border bg-white p-6 text-gray-600">Belum ada project dari Digital Banking.</div>
    @endforelse
  </main>

      <script>
        const btn = document.getElementById('menuBtn');
        const panel = document.getElementById('menuPanel');
        btn?.addEventListener('click', () => panel.classList.toggle('hidden'));
        document.addEventListener('click', (e) => {
            if (!btn.contains(e.target) && !panel.contains(e.target)) panel.classList.add('hidden');
        });
    </script>
</body>
</html>
