{{-- resources/views/dig/notifications.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Notifikasi DIG</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html { scrollbar-gutter: stable; }
    body { overflow-x: hidden; }
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
        <a href="{{ route('dig.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
        <a href="{{ route('dig.notifications') }}" class="font-semibold">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">DIG</span>
      </nav>

      <div class="relative">
        <button id="menuBtn" class="p-2 rounded-xl border border-red-200 text-red-700 hover:bg-red-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 3h6v6H3V3zm12 0h6v6h-6V3zM3 15h6v6H3v-6zm12 0h6v6h-6v-6z" />
          </svg>
        </button>
        <div id="menuPanel" class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-[#7A1C1C] text-white overflow-hidden z-50">
          <a href="#" class="block px-4 py-3 hover:bg-[#6a1717]">Pengaturan Akun</a>
          <a href="/logout" class="block px-4 py-3 hover:bg-[#6a1717]">Log Out</a>
        </div>
      </div>
    </div>
  </header>

  {{-- HEADER LIST --}}
  <header class="sticky top-0 bg-[#F8ECEC]/90 backdrop-blur border-b" style="z-index:10;">
    <div class="max-w-5xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="font-semibold">Notifikasi</div>
        @php
          $totalUnread = ($unreadCount ?? null) ?: (auth()->user()?->unreadNotifications()->count() ?? 0);
        @endphp
        @if($totalUnread > 0)
          <div class="flex items-center gap-1">
            @for($i=1; $i<=min($totalUnread,9); $i++)
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#7A1C1C] text-white text-xs">{{ $i }}</span>
            @endfor
            @if($totalUnread > 9)
              <span class="inline-flex items-center justify-center h-6 px-2 rounded-full bg-[#7A1C1C] text-white text-[11px]">+{{ $totalUnread-9 }}</span>
            @endif
          </div>
        @endif
      </div>

      <form method="POST" action="{{ route('dig.notifications.readAll') }}">
        @csrf
        <button class="text-sm rounded-lg border px-3 py-1 bg-white hover:bg-red-50 border-red-200 text-[#7A1C1C]">
          Tandai semua terbaca
        </button>
      </form>
    </div>
  </header>

  <main class="max-w-5xl mx-auto px-5 py-6">
    @php
      // Safety
      $today = $today ?? collect();

      // helper normalisasi
      $norm = fn($v) => strtolower(trim((string)$v));

      // Filter fleksibel: progress_confirmed, target ke DIG (atau kosong), oleh IT ATAU developer IT
      $filteredToday = $today->filter(function($n) use ($norm) {
        $d   = $n->data ?? [];
        $typ = $norm($d['type'] ?? '');
        $by  = $norm($d['by_role'] ?? '');
        $dev = $norm($d['developer_role'] ?? '');
        $tgt = $norm($d['target_role'] ?? '');

        $isProgress = ($typ === 'progress_confirmed');
        $isForDIG   = ($tgt === '' || $tgt === 'digital_banking');
        $isIT       = ($by === 'it') || ($dev === 'it');

        return $isProgress && $isForDIG && $isIT;
      });

      // Fallback: kalau filter di atas kosong tapi masih ada progress_confirmed hari ini, tampilkan semuanya
      if ($filteredToday->isEmpty() && $today->count()) {
        $maybe = $today->filter(function($n) use ($norm){
          return $norm(data_get($n->data,'type')) === 'progress_confirmed';
        });
        if ($maybe->count()) $filteredToday = $maybe;
      }

      $filteredUnreadCount = $filteredToday->whereNull('read_at')->count();
    @endphp

    {{-- SECTION "HARI INI" --}}
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-base font-semibold">Hari Ini</h2>
      @if($filteredUnreadCount > 0)
        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 rounded-full bg-[#7A1C1C] text-white text-xs px-2">
          {{ $filteredUnreadCount }}
        </span>
      @endif
    </div>

    <div class="space-y-3">
      @forelse($filteredToday as $n)
        @php
          $d         = $n->data ?? [];
          $late      = (bool)($d['late'] ?? false);
          $pId       = $d['project_id']    ?? null;
          $pName     = $d['project_name']  ?? 'Project';
          $progId    = $d['progress_id']   ?? null;
          $progName  = $d['progress_name'] ?? 'Progress';
          $headline  = $d['message']       ?? 'IT Telah mengonfirmasi';
        @endphp

        <div class="rounded-xl bg-[#F2DCDC] px-5 py-4 border border-[#E7C9C9]">
          <div class="flex items-center justify-between">
            <div class="text-[15px] font-semibold">
              {{ $n->created_at?->timezone('Asia/Jakarta')->format('H.i') }}
              <span class="text-[12px] font-normal text-gray-600">(jam dikonfirmasi)</span>
            </div>
            <div class="text-[14px] font-semibold text-right">
              {{ $headline }} {{ $progName }}
            </div>
          </div>

          <div class="mt-3 text-[14px] leading-6">
            <div><span class="font-semibold">Nama Project :</span> {{ $pName }}</div>
            <div>
              @if(function_exists('route') && Route::has('dig.projects.show') && $pId)
                <a class="underline text-[#0a58ca]" href="{{ route('dig.projects.show', $pId) }}">{{ $progName }}</a>
              @else
                <span class="underline">{{ $progName }}</span>
              @endif
            </div>
          </div>

          <div class="mt-3 flex items-center justify-between">
            <div>
              @if($late)
                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                  IT Tidak Memenuhi Target
                </span>
              @else
                <span class="inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                  IT Telah Mengonfirmasi
                </span>
              @endif
            </div>
            <form method="POST" action="{{ route('dig.notifications.read', $n->id) }}">
              @csrf
              <button class="text-xs underline text-[#7A1C1C]">
                {{ $n->read_at ? 'Terbaca' : 'Tandai terbaca' }}
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="text-sm text-gray-600">Belum ada notifikasi hari ini.</div>
      @endforelse
    </div>
  </main>

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
