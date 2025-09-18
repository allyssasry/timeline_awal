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

{{-- NAVBAR --}}
<header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
                class="h-8" alt="Bank Jakarta" />
        </div>

        <nav class="hidden md:flex items-center gap-6 text-sm">
            <a href="{{ url('/dig/dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
            <a href="{{ route('dig.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a> 
            <a href="{{ route('dig.notifications') }}" class="font-semibold">Notifikasi</a>
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
                class="hidden absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-[#7A1C1C] text-white overflow-hidden z-50">
                <a href="#" class="block px-4 py-3 hover:bg-[#6a1717]">Pengaturan Akun</a>
                <a href="/logout" class="block px-4 py-3 hover:bg-[#6a1717]">Log Out</a>
            </div>
        </div>
    </div>
</header>

<body class="min-h-screen bg-[#F8ECEC] text-gray-900">
<header class="sticky top-0 bg-[#F8ECEC]/90 backdrop-blur border-b" style="z-index: 10;">
    <div class="max-w-5xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="font-semibold">Notifikasi</div>
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
    // Filter hanya notifikasi yang pesannya dari IT
    $filteredToday = $today->filter(function($n){
        $msg = strtolower($n->data['message'] ?? '');
        return str_contains($msg, 'dikonfirmasi oleh it');
    });
    $filteredUnreadCount = $filteredToday->whereNull('read_at')->count();
  @endphp

  <div class="flex items-center gap-4 mb-4">
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
        $d   = $n->data;
        $late = $d['late'] ?? false;
      @endphp
      <div class="rounded-xl border border-[#E7C9C9] bg-white px-4 py-3 flex items-start gap-4 {{ $n->read_at ? '' : 'ring-2 ring-[#7A1C1C]/30' }}">
        <div class="text-xs text-gray-500 w-28 shrink-0">{{ $n->created_at->format('H.i') }}</div>
        <div class="flex-1">
          <div class="text-sm">
            <span class="font-semibold">{{ $d['project_name'] ?? 'Project' }}</span> — 
            {{ $d['message'] ?? 'Notifikasi' }}
            (<span class="font-medium">{{ $d['progress_name'] ?? 'Progress' }}</span>)
            @if($late)
              <span class="ml-2 inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                IT Tidak Memenuhi Target
              </span>
            @else
              <span class="ml-2 inline-flex items-center text-[11px] px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                IT Telah Mengonfirmasi
              </span>
            @endif
          </div>

          <form method="POST" action="{{ route('dig.notifications.read', $n->id) }}" class="mt-1">
            @csrf
            <button class="text-xs underline text-[#7A1C1C]">{{ $n->read_at ? 'Terbaca' : 'Tandai terbaca' }}</button>
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
