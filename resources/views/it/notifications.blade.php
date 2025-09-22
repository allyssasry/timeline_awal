<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Notifikasi | IT</title>
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
        <a href="{{ url('/it/dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
        <a href="{{ route('dig.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
        <a href="{{ route('it.notifications') }}" class="font-semibold">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold text-red-600">IT</span>
      </nav>
      <div></div>
    </div>
  </header>

  {{-- HEADER LIST --}}
  <header class="sticky top-0 bg-[#F8ECEC]/90 backdrop-blur border-b" style="z-index:10;">
    <div class="max-w-5xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="font-semibold">Notifikasi</div>
        @if(($unreadCount ?? 0) > 0)
          <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 rounded-full bg-[#7A1C1C] text-white text-xs px-2">
            {{ $unreadCount }}
          </span>
        @endif
      </div>

      <form method="POST" action="{{ route('it.notifications.readAll') }}">
        @csrf
        <button class="text-sm rounded-lg border px-3 py-1 bg-white hover:bg-red-50 border-red-200 text-[#7A1C1C]">
          Tandai semua terbaca
        </button>
      </form>
    </div>
  </header>

  <main class="max-w-5xl mx-auto px-5 py-6">
    {{-- SECTION "HARI INI" --}}
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-base font-semibold">Hari Ini</h2>
      @php $todayUnread = $today?->whereNull('read_at')->count() ?? 0; @endphp
      @if($todayUnread > 0)
        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 rounded-full bg-[#7A1C1C] text-white text-xs px-2">
          {{ $todayUnread }}
        </span>
      @endif
    </div>

    <div class="space-y-3">
      @forelse(($today ?? collect()) as $n)
     @php
  $filteredToday = ($today ?? collect())->filter(function($n){
    $d = $n->data ?? [];
    return strtolower($d['type'] ?? '') === 'dig_marked_read'
        && strtolower($d['target_role'] ?? '') === 'it';
  });
@endphp

@forelse($filteredToday as $n)
  @php
    $d = $n->data ?? [];
    $pName = $d['project_name'] ?? 'Project';
    $time  = $n->created_at?->timezone('Asia/Jakarta')->format('H.i');
  @endphp
  <div class="rounded-xl bg-[#F2DCDC] px-5 py-4 border border-[#E7C9C9]">
    <div class="flex items-center justify-between">
      <div class="text-[15px] font-semibold">{{ $time }} <span class="text-xs text-gray-600">(jam)</span></div>
      <div class="text-[14px] font-semibold">Digital Banking Telah Membaca</div>
    </div>
    <div class="mt-2 text-sm">
      <div><span class="font-semibold">Nama Project :</span> {{ $pName }}</div>
    </div>
  </div>
@empty
  <div class="text-sm text-gray-500">Belum ada notifikasi hari ini.</div>
@endforelse

      @empty
        <div class="text-sm text-gray-600">Belum ada notifikasi hari ini.</div>
      @endforelse
    </div>

    {{-- OPSIONAL: daftar lainnya (selain hari ini), bisa ditambah kalau perlu --}}
  </main>
</body>
</html>
