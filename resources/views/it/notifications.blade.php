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
        <a href="{{ url('/it/dashboard') }}" class="text-gray-600 hover:text-red-600">Beranda</a>
        <a href="{{ route('semua.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
        <a href="{{ route('it.notifications') }}" class="font-semibold">Notifikasi</a>
        <a href="{{ route('semua.arsip') }}" class="text-gray-600 hover:text-red-600">Arsip</a>
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

  {{-- LIST NOTIFIKASI HARI INI --}}
  <main class="max-w-5xl mx-auto px-5 py-6">
    @php
      $todayUnread = ($today ?? collect())->whereNull('read_at')->count();
    @endphp

    <div class="flex items-center justify-between mb-3">
      <h2 class="text-base font-semibold">Hari Ini</h2>
      @if($todayUnread > 0)
        <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 rounded-full bg-[#7A1C1C] text-white text-xs px-2">
          {{ $todayUnread }}
        </span>
      @endif
    </div>

    <div class="space-y-3">
      @forelse(($today ?? collect()) as $n)
        @php
          $d        = $n->data ?? [];
          $type     = strtolower($d['type'] ?? '');
          $pName    = $d['project_name'] ?? 'Project';
          $pId      = $d['project_id']   ?? null;
          $message  = $d['message']      ?? '';
          $decision = strtolower($d['decision'] ?? ''); // 'memenuhi' | 'tidak_memenuhi'
          $isUnread = is_null($n->read_at);

          // Waktu notifikasi -> WIB
          $created  = optional($n->created_at)->timezone('Asia/Jakarta');
          $dateText = $created ? $created->format('d M Y') : '-';
          $timeText = $created ? $created->format('H.i')   : '-';
        @endphp

        <div class="rounded-xl px-5 py-4 border {{ $isUnread ? 'border-[#7A1C1C] bg-[#F2DCDC]' : 'border-[#E7C9C9] bg-white' }}">
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">

              {{-- 1) Project baru dibuat --}}
              @if($type === 'dig_project_created')
                <div class="text-[15px] font-semibold">Project Baru Dibuat</div>
                <div class="mt-1 text-sm">
                  <div><span class="font-semibold">Nama Project</span>: {{ $pName }}</div>
                  <div class="mt-1"><span class="font-semibold">Tanggal</span>: {{ $dateText }} • {{ $timeText }} WIB</div>
                  @if($message)
                    <div class="text-gray-700 mt-1">{{ $message }}</div>
                  @endif
                </div>

              {{-- 2) Keputusan MEMENUHI / TIDAK MEMENUHI --}}
              @elseif($type === 'dig_completion_decision')
                @php
                  $statusLabel = $d['status_label'] ?? (
                    $decision === 'memenuhi' ? 'Project Selesai, Memenuhi' : 'Project Selesai, Tidak Memenuhi'
                  );
                  $isMeet = ($decision === 'memenuhi');
                @endphp

                <div class="text-[15px] font-semibold">Keputusan Penyelesaian Project</div>
                <div class="mt-1 text-sm">
                  <div><span class="font-semibold">Nama Project</span>: {{ $pName }}</div>
                  <div class="mt-1">
                    <span class="font-semibold">Status</span>:
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                 {{ $isMeet ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                      {{ $statusLabel }}
                    </span>
                  </div>
                  <div class="mt-1"><span class="font-semibold">Tanggal</span>: {{ $dateText }} • {{ $timeText }} WIB</div>
                  @if($message)
                    <div class="text-gray-700 mt-1">{{ $message }}</div>
                  @endif
                </div>

              {{-- Fallback --}}
              @else
                <div class="text-[15px] font-semibold">Notifikasi</div>
                <div class="mt-1 text-sm">
                  <div class="text-gray-700">{{ $message ?: 'Ada pembaruan.' }}</div>
                  <div class="mt-1 text-xs text-gray-600">{{ $dateText }} • {{ $timeText }} WIB</div>
                </div>
              @endif

            </div>

            {{-- Sisi kanan: waktu & aksi --}}
            <div class="text-right shrink-0">
              <div class="text-xs text-gray-600">{{ $timeText }}</div>
              <div class="mt-2 flex items-center gap-2 justify-end">
                @if($pId)
                  {{-- Jika detail project IT ada route khusus, ganti 'dig.projects.show' ke route IT --}}
                  <a href="{{ route('dig.projects.show', $pId) }}" class="text-xs underline text-[#7A1C1C]">Lihat Project</a>
                @endif
                @if($isUnread)
                  <form method="POST" action="{{ route('it.notifications.read', $n->id) }}">
                    @csrf
                    <button class="text-xs underline text-[#7A1C1C]">Tandai terbaca</button>
                  </form>
                @endif
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="text-sm text-gray-600">Belum ada notifikasi hari ini.</div>
      @endforelse
    </div>
  </main>

  <script>
    // Dropdown menu
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
