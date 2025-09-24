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
    <div class="flex items-center justify-between">
      <h1 class="text-lg font-semibold">Notifikasi</h1>
      @if(($unreadCount ?? 0) > 0)
        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 rounded-full bg-[#7A1C1C] text-white text-xs px-2">
          {{ $unreadCount }}
        </span>
      @endif
    </div>

    <div class="relative my-4">
      <hr class="border-[#D7B9B9]">
      <div class="absolute left-1/2 -translate-x-1/2 -top-3 bg-[#F8ECEC] px-3 text-sm text-gray-600">Hari Ini</div>
    </div>

    {{-- HARI INI --}}
  @php
  use App\Notifications\SupervisorNotification as SN;

  $items = ($today ?? collect())->filter(function($n){
    return in_array(data_get($n->data,'type'), [
      SN::PROJECT_CREATED_BY_DIG, SN::PROJECT_DONE, SN::PROJECT_UNMET
    ], true);
  });
@endphp

<div class="space-y-3">
  @forelse($items as $n)
    @php
      $t    = data_get($n->data,'type');
      $name = data_get($n->data,'project_name','—');
      $ts   = $n->created_at?->timezone('Asia/Jakarta')->format('d M Y, H.i');

      [$title, $desc, $rightCls] = match($t){
        SN::PROJECT_CREATED_BY_DIG => ['Project Baru Dibuat oleh DIG', 'DIG membuat project baru.', 'text-[#7A1C1C]'],
        SN::PROJECT_DONE           => ['Project Telah Selesai',        'Seluruh progress terkonfirmasi & mencapai target.', 'text-[#C05454]'],
        SN::PROJECT_UNMET          => ['Project Tidak Memenuhi',       'Ada progress belum terkonfirmasi atau di bawah target.', 'text-[#7A1C1C]'],
        default                    => ['Status','', 'text-[#7A1C1C]'],
      };
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
    <div class="text-sm text-gray-600">Belum ada notifikasi hari ini.</div>
  @endforelse
</div>


    {{-- (Opsional) daftar semua notifikasi dengan pagination --}}
    {{-- @if(isset($notifications)) --}}
    {{--   <div class="mt-6">{{ $notifications->links() }}</div> --}}
    {{-- @endif --}}
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
