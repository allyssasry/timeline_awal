<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Notifikasi | Supervisor</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    html{scrollbar-gutter:stable} body{overflow-x:hidden}
  </style>
</head>
<body class="min-h-screen bg-[#F8ECEC] text-gray-900">

  {{-- NAVBAR ringan --}}
  <header class="sticky top-0 z-30 bg-[#F8ECEC]/90 backdrop-blur border-b">
    <div class="max-w-6xl mx-auto px-5 py-3 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-8" alt="Bank Jakarta" />
      </div>
      <nav class="hidden md:flex items-center gap-6 text-sm">
        <a href="{{ route('supervisor.dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
        <a href="{{ route('supervisor.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
        <a href="{{ route('supervisor.notifications') }}" class="font-semibold text-red-600">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
        <span class="font-semibold">Supervisor</span>
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
    @php
      use App\Notifications\SupervisorNotification as SN;

      // Fallback unreadCount jika belum dikirim dari controller
      $unreadCount = $unreadCount
        ?? auth()->user()?->unreadNotifications()?->count()
        ?? 0;

      // Ambil notifikasi terbaru langsung dari user (mis. 50 terakhir)
      $all = auth()->user()
            ?->notifications()
            ->latest()
            ->take(50)
            ->get() ?? collect();

      // Tampilkan HANYA 3 tipe yang diminta
      $items = $all->filter(function($n){
        $t = data_get($n->data,'type');
        return in_array($t, [
          SN::PROJECT_CREATED_BY_DIG,
          SN::PROJECT_DONE,
          SN::PROJECT_UNMET,
        ], true);
      });
    @endphp

    <div class="flex items-center justify-between">
      <h1 class="text-lg font-semibold">Notifikasi</h1>
      <div class="flex items-center gap-3">
        @if(($unreadCount ?? 0) > 0)
          <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 rounded-full bg-[#7A1C1C] text-white text-xs px-2">
            {{ $unreadCount }}
          </span>
          <form method="POST" action="{{ route('supervisor.notifications.readAll') }}">
            @csrf
            <button class="text-xs rounded-lg border px-3 py-1 bg-white hover:bg-red-50 border-red-200 text-[#7A1C1C]">
              Tandai semua terbaca
            </button>
          </form>
        @endif
      </div>
    </div>

    <div class="relative my-4">
      <hr class="border-[#D7B9B9]">
      <div class="absolute left-1/2 -translate-x-1/2 -top-3 bg-[#F8ECEC] px-3 text-sm text-gray-600">
        Terbaru
      </div>
    </div>

    <div class="space-y-3">
      @forelse($items as $n)
        @php
          $type = data_get($n->data,'type');
          $name = data_get($n->data,'project_name','—');
          $ts   = $n->created_at?->timezone('Asia/Jakarta')->format('d M Y, H.i');

          // Mapping judul & deskripsi sesuai permintaan
          if ($type === SN::PROJECT_CREATED_BY_DIG) {
            $title   = 'DIG membuat project';
            $desc    = 'DIG membuat project baru.';
            $rightCls= 'text-[#7A1C1C]';
          } elseif ($type === SN::PROJECT_DONE) {
            $title   = 'DIG mengonfirmasi';
            $desc    = 'Project selesai, Memenuhi.';
            $rightCls= 'text-[#166534]'; // hijau
          } elseif ($type === SN::PROJECT_UNMET) {
            $title   = 'DIG mengonfirmasi';
            $desc    = 'Project selesai, Tidak Memenuhi.';
            $rightCls= 'text-[#7A1C1C]'; // merah
          } else {
            $title   = 'Notifikasi';
            $desc    = '';
            $rightCls= 'text-[#7A1C1C]';
          }
        @endphp

        <div class="rounded-xl bg-[#F2DCDC] px-5 py-4 border border-[#E7C9C9]">
          <div class="flex items-center justify-between">
            <div class="text-[13px]">
              <div class="text-gray-700">Tanggal : {{ $ts }}</div>
              <div class="mt-1">Project : <span class="font-semibold">{{ $name }}</span></div>
            </div>
            <div class="text-[13px] font-semibold {{ $rightCls }}">{{ $title }}</div>
          </div>
          <div class="mt-2 text-sm text-gray-800">{{ $desc }}</div>
        </div>
      @empty
        <div class="text-sm text-gray-600">Belum ada notifikasi.</div>
      @endforelse
    </div>
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
</script>
</body>
</html>
