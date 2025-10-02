{{-- resources/views/account/settings.blade.php --}}
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pengaturan Akun</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { background: #F8ECEC; }
    .ring-theme { box-shadow: 0 0 0 2px #7A1C1C inset; }
  </style>
</head>
<body class="min-h-screen text-gray-900">
 <header class="sticky top-0 z-20 bg-[#F8ECEC]/90 backdrop-blur border-b">
  <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png" class="h-7" alt="Bank Jakarta">
    </div>

    @php
      $user = auth()->user();
      $role = $user?->role;

      // Tentukan URL Beranda per role, dengan fallback ke '/' jika rute belum ada.
      $homeUrl = url('/'); // default

      if ($role === 'digital_banking' && Route::has('dig.dashboard')) {
          $homeUrl = route('dig.dashboard');
      } elseif ($role === 'it' && Route::has('it.dashboard')) {
          $homeUrl = route('it.dashboard');
      } elseif ($role === 'supervisor' && Route::has('supervisor.dashboard')) {
          $homeUrl = route('supervisor.dashboard');
      }
    @endphp>

    <nav class="hidden md:flex items-center gap-6 text-sm">
      {{-- Beranda sesuai role --}}
      <a href="{{ $homeUrl }}" class="text-gray-600 hover:text-red-600">Beranda</a>

      {{-- Menu lain (opsional: tampilkan yang tersedia saja) --}}
      @if(Route::has('semua.progresses'))
        <a href="{{ route('semua.progresses') }}" class="text-gray-600 hover:text-red-600">Progress</a>
      @endif

      @if(Route::has('dig.notifications') && $role === 'digital_banking')
        <a href="{{ route('dig.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
      @endif
      @if(Route::has('it.notifications') && $role === 'it')
        <a href="{{ route('it.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
      @endif
      @if(Route::has('supervisor.notifications') && $role === 'supervisor')
        <a href="{{ route('supervisor.notifications') }}" class="text-gray-600 hover:text-red-600">Notifikasi</a>
      @endif
    </nav>
  </div>
</header>


  <main class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-lg font-semibold mb-4">Informasi Personal</h1>

    {{-- Alert --}}
    @if (session('success'))
      <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
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

    <form action="{{ route('account.update') }}" method="POST" enctype="multipart/form-data"
          class="rounded-2xl border border-[#C89898] bg-[#FFF5F5] p-5 space-y-5">
      @csrf
      @method('PUT')

      {{-- Avatar + Gender --}}
      <div class="flex items-start gap-6">
        {{-- Avatar --}}
        <div class="flex flex-col items-center">
          <div class="w-20 h-20 rounded-full ring-theme overflow-hidden bg-white grid place-items-center">
            @if(!empty($avatarUrl))
              <img id="avatarPreview" src="{{ $avatarUrl }}" alt="Avatar" class="w-full h-full object-cover">
            @else
              <img id="avatarPreview" alt="Avatar" class="w-full h-full object-cover hidden">
              <span id="avatarInitial" class="text-[#7A1C1C] font-semibold text-2xl">
                {{ strtoupper(substr($user->name ?? $user->username ?? 'U',0,1)) }}
              </span>
            @endif
          </div>
          <label class="mt-2 inline-flex items-center gap-2 text-xs cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#7A1C1C]" viewBox="0 0 24 24" fill="currentColor"><path d="M5 7h2l2-2h6l2 2h2v12H5z"/></svg>
            <span>Ganti Foto</span>
            <input id="avatarInput" type="file" name="avatar" accept="image/*" class="hidden">
          </label>
        </div>

        {{-- Gender + Names --}}
        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
            <div class="flex items-center gap-6 text-sm">
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="gender" value="female" class="accent-[#7A1C1C]"
                  {{ old('gender', $user->gender ?? null) === 'female' ? 'checked' : '' }}>
                <span>Perempuan</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" name="gender" value="male" class="accent-[#7A1C1C]"
                  {{ old('gender', $user->gender ?? null) === 'male' ? 'checked' : '' }}>
                <span>Laki-laki</span>
              </label>
            </div>
          </div>

          <div>
            <label class="text-xs block mb-1">First Name</label>
            <input type="text" name="first_name"
              value="{{ old('first_name', $user->first_name ?? $first_guess) }}"
              class="w-full rounded-md border border-[#C89898] bg-white px-3 py-2 outline-none">
          </div>

          <div>
            <label class="text-xs block mb-1">Last Name</label>
            <input type="text" name="last_name"
              value="{{ old('last_name', $user->last_name ?? $last_guess) }}"
              class="w-full rounded-md border border-[#C89898] bg-white px-3 py-2 outline-none">
          </div>
        </div>
      </div>

      {{-- Address --}}
      <div>
        <label class="text-xs block mb-1">Alamat</label>
        <input type="text" name="address"
          value="{{ old('address', $user->address ?? '') }}"
          class="w-full rounded-md border border-[#C89898] bg-white px-3 py-2 outline-none">
      </div>

      {{-- Email --}}
      <div>
        <label class="text-xs block mb-1">Email</label>
        <input type="email" name="email"
          value="{{ old('email', $user->email ?? '') }}"
          class="w-full rounded-md border border-[#C89898] bg-white px-3 py-2 outline-none">
      </div>

      {{-- Username + Phone --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-xs block mb-1">Username</label>
          <input type="text" name="username"
            value="{{ old('username', $user->username ?? '') }}"
            class="w-full rounded-md border border-[#C89898] bg-white px-3 py-2 outline-none">
        </div>
        <div>
          <label class="text-xs block mb-1">Nomor Telepon</label>
          <input type="text" name="phone"
            value="{{ old('phone', $user->phone ?? '') }}"
            class="w-full rounded-md border border-[#C89898] bg-white px-3 py-2 outline-none">
        </div>
      </div>

      <div class="flex items-center gap-4 pt-2">
        <a href="{{ url()->previous() }}"
           class="inline-flex justify-center w-52 h-9 items-center rounded-md border border-[#7A1C1C] bg-[#7A1C1C] text-white text-sm">
          Batalkan Perubahan
        </a>
        <button type="submit"
          class="inline-flex justify-center w-52 h-9 items-center rounded-md border border-[#C89898] bg-white text-sm hover:bg-[#FFF2F2]">
          Simpan Perubahan
        </button>
      </div>
    </form>
  </main>

  <script>
    // Preview foto saat dipilih
    const input = document.getElementById('avatarInput');
    const img   = document.getElementById('avatarPreview');
    const init  = document.getElementById('avatarInitial');
    input?.addEventListener('change', e => {
      const file = e.target.files?.[0];
      if (!file) return;
      const url = URL.createObjectURL(file);
      if (img) {
        img.src = url;
        img.classList.remove('hidden');
      }
      init?.classList.add('hidden');
    });
  </script>
</body>
</html>
