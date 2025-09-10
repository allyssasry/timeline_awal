<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bank Jakarta – Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#FBEFEF] text-gray-800">

    <!-- Navbar -->
    <header class="bg-grey-100 shadow">
        <div class="bg-grey-100 max-w-6xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
                    alt="Logo" class="h-9">
            </div>
            <nav class="hidden md:flex gap-6 font-medium">
                <a href="#beranda" class="hover:text-red-600">Beranda</a>
                <a href="#tentang" class="hover:text-red-600">Tentang Kami</a>
                <a href="#kontak" class="hover:text-red-600">Kontak</a>
            </nav>
            <div class="flex gap-4 justify-center">
                <a href="{{ route('login') }}"
                    class="w-[138px] h-[36px] flex items-center justify-center rounded-full 
            border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF]
            text-black font-semibold transition">
                    Login
                </a>

                <a href="{{ route('Auth.register') }}"
                    class="w-[138px] h-[36px] flex items-center justify-center rounded-full 
            border-2 border-[#7A1C1C] bg-[#E2B9B9] hover:bg-[#D9AFAF]
            text-black font-semibold transition">
                    Sign Up
                </a>
            </div>

        </div>
    </header>

    <!-- Banner -->
    <section id="beranda" class="relative bg-cover bg-center h-[420px]"
        style="background-image:url('https://images.unsplash.com/photo-1521540216272-a50305cd4421?q=80&w=1600&auto=format&fit=crop');">
        <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-center px-6">
            <h1 class="text-red-800 text-3xl md:text-xl font-bold drop-shadow">
                Selamat Datang di Timeline Progress Digital Banking
            </h1>
        </div>
    </section>

    <!-- Tentang Kami -->
    <section id="tentang" class="bg-grey-100 py-12">
        <div class="max-w-6xl mx-auto px-6 py-12">
            <h2 class="text-2xl font-bold text-red-700 mb-4">Tentang Kami</h2>
            <div class="bg-[#E2B9B9]/80  p-6 rounded-2xl shadow">
                <p class="mb-3">
                    Bank Jakarta berdiri dan beroperasi sejak tanggal 11 April 1961 merupakan bank pembangunan daerah
                    pertama yang lahir di Indonesia seiring dengan terbentuknya kota Jakarta sebagai ibukota Indonesia.
                    Bank Jakarta telah melalui sejarah panjang seiring dengan berbagai dinamika pesatnya pertumbuhan
                    kota Jakarta dan telah mengalami beberapa kali perubahan status dan nama perusahaan. Dari awal
                    didirikan dengan nama PT Bank Pembangunan Daerah Djakarta Raya menjadi Perusahaan Daerah (PD) Bank
                    Pembangunan Daerah DKI Jakarta pada tahun 1978. Pada tahun 1999 kembali berubah status dan nama
                    perusahaan menjadi PT Bank Daerah Khusus Ibukota Jakarta Raya hingga terakhir menjadi PT Bank DKI
                    sejak tahun 2008.
                </p>
                <p class="mb-3">
                    Portal ini digunakan untuk memonitor proyek IT dan Digital Banking, termasuk
                    timeline, progres mingguan, serta konfirmasi kesesuaian dengan rencana.
                </p>
            </div>
        </div>
    </section>

    <!-- FOOTER / KONTAK BLOK BESAR -->
    <section id="kontak" class="max-w-7xl mx-auto px-4 md:px-8 pb-12">
        <div class="card p-6 md:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

                <!-- Kolom 1: Identitas + Sosial -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <img src="https://website-api.bankdki.co.id/integrations/storage/page-meta-data/007UlZbO3Oe6PivLltdFiQax6QH5kWDvb0cKPdn4.png"
                            class="h-9" alt="">
                    </div>

                    <p class="text-sm text-gray-700 mb-4">
                        Gedung Prasada Sasana Karya<br>
                        Jl. Suryopranoto No.8, Jakarta Pusat 10130
                    </p>

                    <p class="font-medium mb-2">Media Sosial</p>
                    <div class="flex items-center gap-2">
                        <!-- IG -->
                        <a class="pill bg-pink-600/10 text-pink-600 hover:bg-pink-600 hover:text-white transition px-3 py-2"
                            href="https://www.instagram.com/bankdki" target="_blank" aria-label="Instagram">
                            <!-- simple IG glyph -->
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-5 h-5"
                                fill="currentColor">
                                <path
                                    d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2Zm8.5 1.5h-8.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7Zm4.75-.88a1.13 1.13 0 1 1-2.25 0 1.13 1.13 0 0 1 2.25 0Z" />
                            </svg>
                        </a>
                        <!-- X/Twitter -->
                        <a class="pill bg-sky-500/10 text-sky-600 hover:bg-sky-600 hover:text-white transition px-3 py-2"
                            href="https://twitter.com/bankdki" target="_blank" aria-label="Twitter">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M22 5.92c-.77.34-1.6.56-2.47.66a4.27 4.27 0 0 0 1.87-2.35 8.5 8.5 0 0 1-2.7 1.03 4.25 4.25 0 0 0-7.2 3.87A12.06 12.06 0 0 1 2.72 4.69a4.25 4.25 0 0 0 1.32 5.67 4.2 4.2 0 0 1-1.93-.53c0 2.04 1.45 3.74 3.37 4.13a4.27 4.27 0 0 1-1.92.07 4.26 4.26 0 0 0 3.98 2.96 8.53 8.53 0 0 1-5.46 1.82A12.03 12.03 0 0 0 8.5 21.45c7.83 0 12.11-6.49 12.11-12.11 0-.18-.01-.36-.02-.54A8.67 8.67 0 0 0 22 5.92Z" />
                            </svg>
                        </a>
                        <!-- LinkedIn -->
                        <a class="pill bg-blue-600/10 text-blue-700 hover:bg-blue-700 hover:text-white transition px-3 py-2"
                            href="https://www.linkedin.com/company/bankdki" target="_blank" aria-label="LinkedIn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M4.98 3.5c0 1.38-1.12 2.5-2.5 2.5S0 4.88 0 3.5 1.12 1 2.48 1s2.5 1.12 2.5 2.5zM.5 24V7.5h4.9V24H.5zm7.49 0V7.5h4.7v2.24h.07c.65-1.23 2.24-2.54 4.6-2.54 4.9 0 5.8 3.23 5.8 7.43V24h-4.9v-7.5c0-1.78-.03-4.06-2.47-4.06-2.47 0-2.85 1.93-2.85 3.93V24H7.99z" />
                            </svg>
                        </a>
                        <!-- YouTube -->
                        <a class="pill bg-red-600/10 text-red-600 hover:bg-red-600 hover:text-white transition px-3 py-2"
                            href="https://www.youtube.com" target="_blank" aria-label="YouTube">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24"
                                fill="currentColor">
                                <path
                                    d="M23.5 6.2s-.23-1.63-.92-2.35c-.88-.93-1.87-.94-2.32-.99C16.91 2.5 12 2.5 12 2.5S7.09 2.5 3.74 2.86c-.45.05-1.44.06-2.32.99C.73 4.57.5 6.2.5 6.2S.25 8.08.25 9.96v1.8c0 1.88.25 3.76.25 3.76s.23 1.63.92 2.35c.88.93 2.04.9 2.56 1 1.86.18 7.93.36 7.93.36s4.91 0 8.26-.36c.45-.05 1.44-.06 2.32-.99.69-.72.92-2.35.92-2.35s.25-1.88.25-3.76v-1.8c0-1.88-.25-3.76-.25-3.76ZM9.75 14.02V7.94l6.02 3.04-6.02 3.04z" />
                            </svg>
                        </a>
                    </div>

                    <div class="mt-4 text-sm">
                        <p class="font-medium mb-1">Hubungi Kami</p>
                        <div class="flex flex-col gap-1 text-gray-700">
                            <span>Call Center: <a href="tel:1500351"
                                    class="text-red-700 font-semibold">1500-351</a></span>
                            <span>Email: <a href="mailto:contact@bankdki.co.id"
                                    class="text-blue-600">contact@bankdki.co.id</a></span>
                            <span>Website: <a href="https://www.bankdki.co.id"
                                    class="text-blue-600">bankdki.co.id</a></span>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Digital -->
                <div>
                    <p class="font-semibold mb-3">Digital</p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li>JakOne Mobile</li>
                        <li>JakOne</li>
                        <li>JakOne Pay</li>
                        <li>JakOne Abank</li>
                        <li>Smart Finance</li>
                        <li>Ekosistem Digital Banking</li>
                    </ul>
                </div>

                <!-- Kolom 3: Produk -->
                <div>
                    <p class="font-semibold mb-3">Produk</p>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li>Syariah</li>
                        <li>Simpanan</li>
                        <li>Pembiayaan/Pinjaman</li>
                        <li>Treasury & Financial Institutions</li>
                        <li>Layanan</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- COPYRIGHT -->
    <footer class="bg-[#E2B9B9]/80">
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-4 text-center text-xs md:text-sm text-gray-500">
            © {{ date('Y') }} Bank Jakarta — All rights reserved.
        </div>
    </footer>

</body>

</html>
