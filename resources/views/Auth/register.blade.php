<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Sign Up • Bank Jakarta</title>
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
      <h1 class="text-center text-base md:text-lg font-semibold mb-4">
        Silakan daftarkan akun terlebih dahulu, jika belum memiliki akun.
      </h1>

      <form method="POST" action="/register" class="space-y-4">
        @csrf

        <!-- Username -->
        <div class="bg-[#E9C8C8] rounded-xl border border-[#C89898] focus-within:ring-2 focus-within:ring-rose-300">
          <div class="flex items-center px-4 py-3 gap-3">
            <!-- user icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
            </svg>
            <input name="username" value="{{ old('username') }}" type="text" placeholder="Username" required
                   class="w-full bg-transparent outline-none placeholder-gray-600" />
          </div>
        </div>

        <!-- Name -->
        <div class="bg-[#E9C8C8] rounded-xl border border-[#C89898] focus-within:ring-2 focus-within:ring-rose-300">
          <div class="flex items-center px-4 py-3 gap-3">
            <!-- pencil/person icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="currentColor">
              <path d="m3 17.25 6.52-1.4L19.44 5.9a1.75 1.75 0 0 0-2.47-2.47L7.05 13.38 5.6 19.9 3 17.25zM20 21H4a1 1 0 1 1 0-2h16a1 1 0 1 1 0 2z"/>
            </svg>
            <input name="name" value="{{ old('name') }}" type="text" placeholder="Name" required
                   class="w-full bg-transparent outline-none placeholder-gray-600" />
          </div>
        </div>

        <!-- Role -->
        <div class="bg-[#E9C8C8] rounded-xl border border-[#C89898] focus-within:ring-2 focus-within:ring-rose-300">
          <div class="flex items-center px-4 py-3 gap-3">
            <!-- bag icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="currentColor">
              <path d="M7 7V6a5 5 0 1 1 10 0v1h2a2 2 0 0 1 2 2v10a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9a2 2 0 0 1 2-2h2Zm2 0h6V6a3 3 0 0 0-6 0v1Z"/>
            </svg>
            <select name="role" required
                    class="w-full bg-transparent outline-none appearance-none cursor-pointer">
              <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih Role</option>
              <option value="it" {{ old('role')=='it' ? 'selected' : '' }}>IT</option>
              <option value="digital_banking" {{ old('role')=='digital_banking' ? 'selected' : '' }}>
                Digital Banking
              </option>
              <option value="supervisor" {{ old('role')=='supervisor' ? 'selected' : '' }}>Supervisor</option>
            </select>
            <!-- chevron -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 opacity-70" viewBox="0 0 24 24" fill="currentColor">
              <path d="M8.12 9.29 12 13.17l3.88-3.88 1.41 1.41L12 16l-5.29-5.29 1.41-1.41z"/>
            </svg>
          </div>
        </div>

        <!-- Password -->
        <div class="bg-[#E9C8C8] rounded-xl border border-[#C89898] focus-within:ring-2 focus-within:ring-rose-300">
          <div class="flex items-center px-4 py-3 gap-3">
            <!-- key icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 opacity-80" viewBox="0 0 24 24" fill="currentColor">
              <path d="M14 3a7 7 0 1 0 5.61 11.2L22 16.59V20h-3v-2h-2v-2h-2.59l-.62-.62A7 7 0 0 0 14 3Zm-3 7a2 2 0 1 1 0-4 2 2 0 0 1 0 4Z"/>
            </svg>
            <input name="password" type="password" placeholder="Password" required
                   class="w-full bg-transparent outline-none placeholder-gray-600" />
          </div>
        </div>

        <!-- Tombol -->
        <button type="submit"
                class="w-full rounded-2xl border-2 border-[#C89898] bg-[#F6E4E4] hover:bg-[#f2d8d8] text-[#9B1C1C] font-extrabold py-3 transition">
          Sign Up
        </button>
      </form>

      <!-- Error handling -->
      @if ($errors->any())
        <div class="mt-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg p-3">
          @foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach
        </div>
      @endif
    </div>

    <p class="text-center mt-4 text-sm">
      Sudah punya akun?
      <a href="{{ route('login') }}" class="font-semibold hover:underline">Login</a>
    </p>
  </div>

</body>
</html>
