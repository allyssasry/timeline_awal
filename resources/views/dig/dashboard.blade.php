<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard IT (Awal)</title>
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
      <h1 class="text-white text-2xl md:text-3xl font-bold">
        Selamat Datang di Timeline Progress
      </h1>
    </div>
  </section>

  <div class="max-w-6xl mx-auto px-5">

    {{-- SECTION: PROJECT --}}
    <div class="flex items-center justify-between mt-6">
      <h2 class="text-xl font-semibold">Project</h2>
      <button id="openNewProject"
              class="flex items-center gap-2 rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF]
                     text-black font-semibold w-[138px] h-[36px] justify-center transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/>
        </svg>
        Tambah
      </button>
    </div>

    {{-- Empty-state awal --}}
    @if($projects->isEmpty())
      <div class="mt-6">
        <div class="bg-[#EBD0D0] rounded-2xl px-6 py-8 flex items-center justify-center">
          <div class="rounded-2xl bg-[#CFA8A8] px-5 py-3 text-white/95">
            Anda belum memiliki project
          </div>
        </div>
      </div>
    @endif

    {{-- SECTION: PROGRESS TERCAKAI (ring info sederhana) --}}
    <div class="mt-8">
      <h2 class="text-xl font-semibold mb-3">Progress Tercapai</h2>
      @php
        $percent = 0; // sementara (isi dari perhitunganmu nanti)
        $size = 110; $stroke = 12;
        $r = $size/2 - $stroke; $circ = 2 * M_PI * $r;
        $off = $circ * (1 - $percent/100);
      @endphp
      <div class="bg-[#F1DADA] rounded-2xl p-5 md:p-6 flex items-center gap-6">
        <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}">
          <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#E0BEBE" stroke-width="{{ $stroke }}" fill="none" opacity=".35"/>
          <circle cx="{{ $size/2 }}" cy="{{ $size/2 }}" r="{{ $r }}" stroke="#7A1C1C" stroke-width="{{ $stroke }}"
                  stroke-linecap="round" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $off }}"
                  transform="rotate(-90 {{ $size/2 }} {{ $size/2 }})" fill="none"/>
          <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="16" font-weight="700" fill="#222">
            {{ $percent }}%
          </text>
        </svg>
        <div class="flex-1">
          <p class="text-base md:text-lg font-medium">Ikhtisar progres (awal)</p>
          <p class="text-sm text-gray-700">Bagian ini nanti menampilkan ringkasan progres proyek—angka di ring berasal dari rata-rata/terbaru.</p>
        </div>
      </div>
    </div>

  </div>

{{-- MODAL: TAMBAH PROJECT (sesuai desain) --}}
<div id="newProjectModal" class="hidden fixed inset-0 z-40 bg-black/40 p-4">
  <div class="max-w-4xl mx-auto bg-[#FFF5F5] rounded-2xl shadow-xl border border-[#E7C9C9] overflow-hidden">
    <div class="flex items-center justify-between px-6 py-4 bg-[#F6E4E4] border-b border-[#E7C9C9]">
      <h3 class="text-lg font-semibold">Project</h3>
      <button id="closeNewProject" class="text-[#7A1C1C] text-2xl leading-none">&times;</button>
    </div>

    <form method="POST" action="/projects" class="px-6 pt-5 pb-7 space-y-6">
      @csrf

      {{-- Nama Project --}}
      <section>
        <h4 class="text-base font-semibold mb-2">Nama Project</h4>
        <input name="title" required
               class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
               placeholder="Tulis nama project..." />
      </section>

      {{-- Progress 1 + Timeline --}}
      <section class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <h4 class="text-base font-semibold mb-2">Progress 1</h4>
          <input name="progresses[0][name]" required
                 class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                 placeholder="Nama Progress" />
        </div>

        <div class="relative">
          <h4 class="text-base font-semibold mb-2">Timeline Progress 1</h4>
          <div class="grid grid-cols-2 gap-2">
            <input type="date" name="progresses[0][start_date]" required
                   class="rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                   placeholder="Tanggal awal">
            <input type="date" name="progresses[0][end_date]" required
                   class="rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none"
                   placeholder="Tanggal akhir">
          </div>

          {{-- tombol tambah progress --}}
          <button type="button" id="addProgressRow"
                  class="absolute -right-2 -bottom-2 w-9 h-9 rounded-full border border-[#C89898] bg-white text-[#7A1C1C] grid place-content-center shadow">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6z"/></svg>
          </button>
        </div>
      </section>

      {{-- Container progress dinamis --}}
      <div id="moreProgress" class="space-y-4"></div>

      {{-- Keinginan awal + persen --}}
      @php
        $ringSize=120; $stroke=14;
        $r=$ringSize/2-$stroke; $circ=2*M_PI*$r;
      @endphp
      <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="flex items-center gap-6">
          <div>
            <h4 class="text-base font-semibold mb-2">Keinginan Awal</h4>
            <svg width="{{ $ringSize }}" height="{{ $ringSize }}" viewBox="0 0 {{ $ringSize }} {{ $ringSize }}">
              <circle cx="{{ $ringSize/2 }}" cy="{{ $ringSize/2 }}" r="{{ $r }}" fill="none"
                      stroke="#D9B0B0" stroke-width="{{ $stroke }}" opacity=".5"/>
              <circle id="desired-ring" cx="{{ $ringSize/2 }}" cy="{{ $ringSize/2 }}" r="{{ $r }}" fill="none"
                      stroke="#7A1C1C" stroke-width="{{ $stroke }}" stroke-linecap="round"
                      stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ*(1-0.75) }}"
                      transform="rotate(-90 {{ $ringSize/2 }} {{ $ringSize/2 }})"/>
              <text id="desired-text" x="50%" y="50%" dominant-baseline="middle" text-anchor="middle"
                    font-size="18" font-weight="700" fill="#222">75%</text>
            </svg>
          </div>

          <div class="flex-1">
            <label class="block text-sm font-semibold mb-2">Pilihan Persen</label>
            <select id="desiredSelect" name="desired_percent"
                    class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
              @for($i=0;$i<=100;$i+=5)
                <option value="{{ $i }}" {{ $i==75?'selected':'' }}>{{ $i }}%</option>
              @endfor
            </select>
          </div>
        </div>

        {{-- Penanggung jawab --}}
        <div class="grid grid-cols-1 gap-5">
          <div>
            <h4 class="text-base font-semibold mb-2">Penanggung Jawab (Digital Banking)</h4>
            <select name="digital_banking_id" required
                    class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
              <option value="">Pilih Nama</option>
              @isset($digitalUsers)
                @foreach($digitalUsers as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                @endforeach
              @endisset
            </select>
          </div>
          <div>
            <h4 class="text-base font-semibold mb-2">Penanggung Jawab (Developer)</h4>
            <select name="developer_id" required
                    class="w-full rounded-xl bg-[#E2B9B9]/60 border border-[#C89898] px-4 py-3 outline-none cursor-pointer">
              <option value="">Pilih Nama</option>
              @isset($itUsers)
                @foreach($itUsers as $u)
                  <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                @endforeach
              @endisset
            </select>
          </div>
        </div>
      </section>

      {{-- Tombol aksi --}}
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" id="cancelNewProject"
                class="w-[140px] h-[40px] rounded-full border-2 border-[#7A1C1C] bg-white text-[#7A1C1C] font-semibold">
          Batal
        </button>
        <button
          class="w-[140px] h-[40px] rounded-full border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF] text-black font-semibold">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

{{-- JS untuk ring persen & tambah progress --}}
<script>
(function(){
  // Ring persen
  const sel = document.getElementById('desiredSelect');
  const ring = document.getElementById('desired-ring');
  const txt  = document.getElementById('desired-text');
  const R = {{ $r }}; const CIRC = {{ $circ }};
  function render(val){
    ring.setAttribute('stroke-dasharray', CIRC);
    ring.setAttribute('stroke-dashoffset', CIRC*(1 - (val/100)));
    txt.textContent = `${val}%`;
  }
  sel?.addEventListener('change', e => render(parseInt(e.target.value||'0',10)));
  render(parseInt(sel?.value||'75',10));

  // Tambah progress baris baru
  const addBtn = document.getElementById('addProgressRow');
  const wrap   = document.getElementById('moreProgress');
  let idx = 1;
  addBtn?.addEventListener('click', () => {
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
      </div>`;
    wrap.appendChild(row);
    idx++;
  });

  // open/close modal (pastikan tombol pemicu ada di halaman)
  const modal  = document.getElementById('newProjectModal');
  const close  = document.getElementById('closeNewProject');
  const cancel = document.getElementById('cancelNewProject');
  document.getElementById('openNewProject')?.addEventListener('click', ()=>modal.classList.remove('hidden'));
  close?.addEventListener('click', ()=>modal.classList.add('hidden'));
  cancel?.addEventListener('click', ()=>modal.classList.add('hidden'));
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') modal.classList.add('hidden'); });
})();
</script>


  <script>
    // dropdown
    const btn=document.getElementById('menuBtn'), panel=document.getElementById('menuPanel');
    btn?.addEventListener('click',()=>panel.classList.toggle('hidden'));
    document.addEventListener('click',e=>{ if(!btn.contains(e.target) && !panel.contains(e.target)) panel.classList.add('hidden'); });

    // modal
    const modal=document.getElementById('newProjectModal');
    document.getElementById('openNewProject')?.addEventListener('click',()=>modal.classList.remove('hidden'));
    document.getElementById('closeNewProject')?.addEventListener('click',()=>modal.classList.add('hidden'));
    document.getElementById('cancelNewProject')?.addEventListener('click',()=>modal.classList.add('hidden'));
    document.addEventListener('keydown',e=>{ if(e.key==='Escape') modal.classList.add('hidden'); });
  </script>
</body>
</html>
