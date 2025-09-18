{{-- resources/views/dig/dashboard.blade.php --}}
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Dashboard DIG</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html {
            scrollbar-gutter: stable;
        }

        body {
            overflow-x: hidden;
        }

        .progress-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .progress-scroll::-webkit-scrollbar-thumb {
            background: #c89898;
            border-radius: 9999px;
        }

        .progress-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
    <style>
        /* Cegah “geser ke kanan” saat scrollbar muncul/hilang */
        html {
            scrollbar-gutter: stable;
        }

        /* Chrome/Edge/Firefox modern */
        body {
            overflow-x: hidden;
        }

        /* Antisipasi overflow horizontal */
    </style>
</head>

<body class="min-h-screen bg-[#F8ECEC] text-gray-900">

    {{-- NAVBAR (tanpa tombol Tambah Project) --}}
    <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
        <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
                    class="h-8" alt="Bank Jakarta" />
            </div>

            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="#beranda" class="font-semibold">Beranda</a>
                <a href="{{ route('dig.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a> <a
                    href="{{ route('dig.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
                <a href="{{ route('arsip.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
                <span class="font-semibold text-red-600">Developer</span>
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

     <header class="sticky top-0 z-30 bg-[#F3DCDC]/90 backdrop-blur border-b">
        <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
 <span class="font-semibold text-grey-600 text-xl">Project</span>        </div></header>

{{-- LIST PROJECT --}}
<div class="max-w-6xl mx-auto px-5 py-6">
  @foreach ($projects as $project)
    <div class="rounded-2xl border-2 border-[#7A1C1C] bg-[#F2DCDC] p-5 mb-6">
      <div class="font-semibold mb-2">{{ $project->name }}</div>
      <div class="text-sm text-gray-700 mb-4">
        Penanggung Jawab DIG: {{ $project->digitalBanking->name ?? '-' }} <br>
        Penanggung Jawab IT : {{ $project->developer->name ?? '-' }}
      </div>

      {{-- Progress --}}
      <div class="space-y-4">
        @foreach ($project->progresses as $pr)
          @php
            $last = $pr->updates->sortByDesc('update_date')->first();
            $realisasi = $last ? (int)($last->percent ?? $last->progress_percent ?? 0) : 0;
            $baseCanConfirm = $realisasi >= (int)$pr->desired_percent && !$pr->confirmed_at;
            $isOwner = (int)($pr->created_by ?? 0) === (int)auth()->id();
            $canManage = $isOwner || auth()->user()?->role === 'digital_banking';
            $canConfirm = $baseCanConfirm && $canManage;
          @endphp

          <div class="rounded-xl bg-[#E6CACA] p-4">
            <div class="font-semibold">Progress {{ $loop->iteration }} — {{ $pr->name }}</div>
            <div class="text-sm">Target: {{ $pr->desired_percent }}% | Realisasi: {{ $realisasi }}%</div>

            {{-- Update progress hanya untuk owner --}}
            <form method="POST" action="{{ route('progresses.updates.store', $pr->id) }}" class="mt-2 flex gap-2">
              @csrf
              <input type="date" name="update_date" value="{{ now()->toDateString() }}"
                     class="rounded-xl border px-3 py-2 text-sm"
                     @if(!$canManage) disabled @endif>
              <input type="number" name="percent" min="0" max="100" placeholder="%"
                     class="rounded-xl border px-3 py-2 text-sm"
                     @if(!$canManage) disabled @endif>
              <button class="rounded-xl bg-[#7A1C1C] text-white px-4 py-2 text-xs font-semibold disabled:opacity-50"
                      @if(!$canManage) disabled @endif>
                Update
              </button>
            </form>

            {{-- Konfirmasi --}}
            <form method="POST" action="{{ route('progresses.confirm', $pr->id) }}" class="mt-2">
              @csrf
              <button class="rounded-xl bg-green-600 text-white px-4 py-2 text-xs font-semibold disabled:opacity-50"
                      {{ $canConfirm ? '' : 'disabled' }}>
                Konfirmasi
              </button>
            </form>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</div>
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
</script>
</body>
</html>
