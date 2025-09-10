<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Login • Bank Jakarta</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#FBEFEF] text-gray-800 flex items-start md:items-center justify-center p-6">

  <div class="w-full max-w-xl">
    <!-- Logo -->
    <div class="text-center mb-6">
      <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
           alt="Bank DKI" class="mx-auto h-14 md:h-16">
    </div>

    <!-- Card -->
    <div class="bg-[#E2B9B9]/80 backdrop-blur rounded-2xl shadow-md p-5 md:p-7">
      <h1 class="text-center text-base md:text-lg font-semibold mb-4">Selamat Datang! Silakan Masuk.</h1>

      <form method="POST" action="/login" class="space-y-4">
        @csrf

        <!-- Username -->
        <div class="bg-[#E9C8C8] rounded-xl border border-[#C89898] focus-within:ring-2 focus-within:ring-rose-300">
          <div class="flex items-center px-4 py-3 gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
            </svg>
            <input name="username" type="text" placeholder="Username" required
                   class="w-full bg-transparent outline-none placeholder-gray-600" />
          </div>
        </div>

        <!-- Password -->
        <div class="bg-[#E9C8C8] rounded-xl border border-[#C89898] focus-within:ring-2 focus-within:ring-rose-300">
          <div class="flex items-center px-4 py-3 gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17 8V7a5 5 0 0 0-10 0v1H5v14h14V8h-2zm-8 0V7a3 3 0 0 1 6 0v1H9zm3 5a2 2 0 1 1 0 4 2 2 0 0 1 0-4z"/>
            </svg>
            <input name="password" type="password" placeholder="Password" required
                   class="w-full bg-transparent outline-none placeholder-gray-600" />
          </div>
        </div>

        <!-- Link daftar -->
        <p class="text-right text-sm">
          Jika belum memiliki akun, <a href="{{ route('Auth.register') }}" class="font-semibold hover:underline">Daftar Sekarang!</a>
        </p>

        <!-- Tombol Login -->
        <button type="submit"
                class="w-full rounded-2xl border-2 border-[#C89898] bg-[#F6E4E4] hover:bg-[#f2d8d8] text-[#9B1C1C] font-extrabold py-3 transition">
          Login
        </button>
      </form>

      @if ($errors->any())
        <div class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3">
          @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif

      @if(session('error'))
        <div class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3">
          {{ session('error') }}
        </div>
      @endif
    </div>
  </div>

</body>
</html>
