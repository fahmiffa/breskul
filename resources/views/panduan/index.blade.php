@extends('base.layout')
@section('title', 'Panduan & Petunjuk Penggunaan')

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="panduanApp()">

    <!-- Banner Header -->
    <div class="bg-gradient-to-r from-green-700 via-green-600 to-emerald-600 rounded-2xl shadow-lg p-6 sm:p-8 text-white relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="280" height="280" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M12 7v14" />
                <path d="M16 12h2" />
                <path d="M16 8h2" />
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
            </svg>
        </div>
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-xs font-semibold uppercase tracking-wider mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Pusat Bantuan & Edukasi
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight">Panduan Penggunaan Breskul</h1>
            <p class="mt-2 text-green-100 text-sm sm:text-base leading-relaxed">
                Temukan petunjuk lengkap penggunaan aplikasi Breskul (Web & Mobile Service) untuk mengelola data akademik, absensi RFID/QR Code, keuangan, serta pelaksanaan ujian online.
            </p>

            <!-- Search Bar -->
            <div class="mt-5 relative">
                <input type="text" x-model="searchQuery" placeholder="Cari topik panduan (contoh: absensi, topup, ujian, password)..."
                    class="w-full pl-11 pr-4 py-3 rounded-xl bg-white text-gray-800 placeholder-gray-400 shadow-md focus:outline-none focus:ring-2 focus:ring-green-400 text-sm sm:text-base" />
                <div class="absolute left-3.5 top-3.5 text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none border-b border-gray-200">
        <template x-for="tab in tabs" :key="tab.id">
            <button @click="activeTab = tab.id"
                :class="activeTab === tab.id ? 'bg-green-600 text-white shadow-sm font-semibold' : 'bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-gray-200 font-medium'"
                class="px-4 py-2.5 rounded-xl text-xs sm:text-sm whitespace-nowrap transition-all duration-150 flex items-center gap-2 cursor-pointer">
                <span x-html="tab.icon"></span>
                <span x-text="tab.label"></span>
            </button>
        </template>
    </div>

    <!-- Content Sections -->
    <div class="space-y-6">

        <!-- TAB 1: PENGENALAN -->
        <div x-show="activeTab === 'overview' && matchesSearch('pengenalan overview sistem role login')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-green-100 text-green-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Tentang Aplikasi Breskul</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Sistem Informasi Manajemen Sekolah / Perguruan Tinggi Berbasis Cloud & Mobile</p>
                </div>
            </div>

            <div class="prose max-w-none text-gray-600 text-sm leading-relaxed space-y-4">
                <p>
                    <strong>Breskul</strong> adalah ekosistem aplikasi terpadu yang dirancang untuk memudahkan operasional sekolah maupun kampus. Sistem ini mencakup aplikasi Manajemen Web untuk Pengelola/Admin dan Guru/Dosen, serta Aplikasi Mobile Flutter (<strong>breskul_app</strong>) untuk Siswa/Mahasiswa dan Orang Tua.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    <div class="border border-green-200 bg-green-50/50 rounded-xl p-4">
                        <div class="font-semibold text-green-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-600"></span> Super Admin (Role 0)
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Mengelola tenant/aplikasi institusi, API Keys, dan referensi Master Jabatan utama.</p>
                    </div>

                    <div class="border border-blue-200 bg-blue-50/50 rounded-xl p-4">
                        <div class="font-semibold text-blue-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span> Admin App (Role 1)
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Mengelola seluruh data master instansi (Siswa, Guru, Karyawan, Pembayaran, Kas, Jadwal).</p>
                    </div>

                    <div class="border border-purple-200 bg-purple-50/50 rounded-xl p-4">
                        <div class="font-semibold text-purple-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-600"></span> Guru / Dosen (Role 3)
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Mengelola bank soal, membuat ujian online, memantau absensi siswa & bimbingan halaqah.</p>
                    </div>

                    <div class="border border-amber-200 bg-amber-50/50 rounded-xl p-4">
                        <div class="font-semibold text-amber-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-600"></span> Siswa & Karyawan (Role 2 & 4)
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Melakukan presensi RFID/QR Code, mengikuti ujian, cek tagihan spp, serta dompet saldo topup.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: PENGGUNA & AKUN -->
        <div x-show="activeTab === 'users' && matchesSearch('pengguna akun user murid siswa guru karyawan jabatan password status')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-blue-100 text-blue-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Manajemen Akun & Pengguna</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Panduan pengelolaan data Murid, Guru, Karyawan, dan Akun Pengguna</p>
                </div>
            </div>

            <div class="space-y-4 text-sm text-gray-600">
                <div class="border-l-4 border-blue-500 pl-4 py-1">
                    <h3 class="font-semibold text-gray-800">1. Kelola Akun Pengguna (`/dashboard/master/akun`)</h3>
                    <p class="mt-1">
                        Halaman ini menampilkan <strong>semua akun pengguna</strong> dalam sistem (Admin, Guru, Siswa, Karyawan). Anda dapat memfilter pengguna berdasarkan <strong>Tipe Account</strong> maupun <strong>Jabatan</strong>, melakukan <strong>Edit Password</strong> instan, serta mengubah <strong>Status Aktif/Nonaktif</strong> akun.
                    </p>
                </div>

                <div class="border-l-4 border-blue-500 pl-4 py-1">
                    <h3 class="font-semibold text-gray-800">2. Pendaftaran Data Murid / Mahasiswa</h3>
                    <p class="mt-1">
                        Dapat ditambahkan secara manual melalui menu <code>Master &gt; Murid</code> atau secara masal mengunduh file template Excel/CSV yang telah disediakan kemudian mengunggahnya melalui tombol <strong>Import</strong>.
                    </p>
                </div>

                <div class="border-l-4 border-blue-500 pl-4 py-1">
                    <h3 class="font-semibold text-gray-800">3. Pendaftaran Karyawan & Jabatan</h3>
                    <p class="mt-1">
                        Jabatan karyawan seperti <em>Kepala Sekolah, Bendahara, Wali Kelas, Staf IT</em> dikelola pada menu <code>Master &gt; Jabatan</code>. Saat menambahkan Karyawan baru, akun pengguna otomatis dibuat dan terhubung dengan Jabatan yang dipilih.
                    </p>
                </div>
            </div>
        </div>

        <!-- TAB 3: ABSENSI & PRESENSI -->
        <div x-show="activeTab === 'attendance' && matchesSearch('absensi presensi rfid qrcode scan jam masuk pulang denda')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-emerald-100 text-emerald-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Presensi & Integrasi RFID / QR Code</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Pengaturan jadwal kehadiran, toleransi keterlambatan, dan riwayat presensi</p>
                </div>
            </div>

            <div class="space-y-4 text-sm text-gray-600">
                <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                    <h4 class="font-bold text-emerald-800">Langkah Pengaturan Absensi:</h4>
                    <ol class="list-decimal list-inside space-y-1.5 mt-2 text-emerald-900">
                        <li>Buka menu <code>Master &gt; Setting Absensi</code> untuk menetapkan waktu <strong>Jam Masuk</strong> dan <strong>Jam Pulang</strong> per Jabatan/Role.</li>
                        <li>Tentukan batas toleransi keterlambatan (dalam menit) serta besaran nominal denda keterlambatan jika diberlakukan.</li>
                        <li>Pastikan nomor Kartu RFID atau QR Code siswa sudah terdaftar melalui tombol <strong>Scan RFID</strong> pada data murid.</li>
                    </ol>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="border rounded-xl p-4">
                        <h4 class="font-semibold text-gray-800 mb-1">Presensi Masuk & Pulang</h4>
                        <p class="text-xs text-gray-500">Siswa/Karyawan melakukan tempel kartu RFID pada mesin scanner yang terhubung ke Web Service API. Status otomatis tercatat Masuk/Terlambat.</p>
                    </div>
                    <div class="border rounded-xl p-4">
                        <h4 class="font-semibold text-gray-800 mb-1">Monitoring & Laporan</h4>
                        <p class="text-xs text-gray-500">Admin/Guru dapat memantau kehadiran secara real-time pada menu <code>Operasional &gt; Absensi</code> lengkap dengan filter tanggal dan peran.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 4: KEUANGAN & PEMBAYARAN -->
        <div x-show="activeTab === 'finance' && matchesSearch('keuangan pembayaran spp tagihan saldo topup kas midtrans verifikasi')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-purple-100 text-purple-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Keuangan, Tagihan SPP & Buku Kas</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Pengelolaan modul pembayaran, transaksi online Midtrans, dompet saldo, dan kas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm text-gray-600">
                <div class="space-y-3 border rounded-2xl p-5">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <span class="p-1.5 bg-purple-100 text-purple-700 rounded-lg">💳</span> Tagihan & Pembayaran SPP
                    </h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li>• <strong>Master Jenis Pembayaran:</strong> Dibuat di <code>Master &gt; Pembayaran</code> (contoh: SPP Bulanan, Biaya Ujian, Uang Gedung).</li>
                        <li>• <strong>Assign Tagihan:</strong> Buka <code>Keuangan &gt; Pembayaran</code>, pilih siswa/kelas lalu klik tambahkan tagihan.</li>
                        <li>• <strong>Verifikasi Manual:</strong> Admin dapat mengonfirmasi pembayaran tunai secara manual yang otomatis mencatat pemasukan pada Buku Kas.</li>
                    </ul>
                </div>

                <div class="space-y-3 border rounded-2xl p-5">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <span class="p-1.5 bg-green-100 text-green-700 rounded-lg">👛</span> Saldo, Topup & Buku Kas
                    </h3>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li>• <strong>Dompet Saldo Siswa:</strong> Digunakan untuk transaksi di kantin/internal sekolah. Pengisian saldo dikelola pada menu <code>Keuangan &gt; Topup</code>.</li>
                        <li>• <strong>Buku Kas Instansi:</strong> Perekaman transaksi Pemasukan & Pengeluaran secara transparan di menu <code>Keuangan &gt; Kas</code>.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- TAB 5: AKADEMIK & UJIAN -->
        <div x-show="activeTab === 'academic' && matchesSearch('akademik ujian bank soal kelas prodi mapel halaqah pdf')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-amber-100 text-amber-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0112 20.055a11.952 11.952 0 01-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Akademik & Ujian Online</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Pengelolaan struktur kelas/prodi, bank soal, jadwal ujian, dan cetak PDF</p>
                </div>
            </div>

            <div class="space-y-4 text-sm text-gray-600">
                <div class="border-l-4 border-amber-500 pl-4 py-1">
                    <h3 class="font-semibold text-gray-800">1. Bank Soal & Pembuatan Soal</h3>
                    <p class="mt-1">
                        Guru/Dosen menginput paket soal pada menu <code>Ujian &gt; Soal</code>. Dapat diinput manual lengkap dengan bobot nilai/kunci jawaban atau melalui fitur <strong>Import Excel Soal</strong>.
                    </p>
                </div>

                <div class="border-l-4 border-amber-500 pl-4 py-1">
                    <h3 class="font-semibold text-gray-800">2. Pelaksanaan & Penjadwalan Ujian</h3>
                    <p class="mt-1">
                        Setelah soal siap, Guru mengaktifkan ujian pada menu <code>Ujian &gt; Ujian</code>. Jadwal sesi dan penugasan kelas diatur agar siswa dapat mengerjakan langsung dari aplikasi web/mobile. Berita acara dapat diunduh dalam format <strong>PDF</strong>.
                    </p>
                </div>
            </div>
        </div>

        <!-- TAB 6: MOBILE APP -->
        <div x-show="activeTab === 'mobile' && matchesSearch('mobile flutter breskul_app android notifikasi fcm siswa orang tua')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-indigo-100 text-indigo-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Aplikasi Mobile Breskul (breskul_app)</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Panduan penggunaan aplikasi Flutter untuk Siswa, Mahasiswa, dan Orang Tua</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs sm:text-sm text-gray-600">
                <div class="p-4 border rounded-xl bg-indigo-50/40">
                    <div class="font-bold text-indigo-900 mb-1">🔔 Notifikasi Push FCM</div>
                    <p class="text-gray-600">Siswa & Ortu menerima notifikasi instan saat ada pengumuman baru, tagihan SPP terbit, atau konfirmasi pembayaran lunas.</p>
                </div>
                <div class="p-4 border rounded-xl bg-indigo-50/40">
                    <div class="font-bold text-indigo-900 mb-1">📲 Scan Presensi & Saldo</div>
                    <p class="text-gray-600">Memeriksa riwayat kehadiran harian, sisa saldo dompet sekolah, serta pembayaran langsung via Midtrans Snap Gateway.</p>
                </div>
                <div class="p-4 border rounded-xl bg-indigo-50/40">
                    <div class="font-bold text-indigo-900 mb-1">📝 Ujian Mobile</div>
                    <p class="text-gray-600">Siswa dapat mengerjakan ujian secara langsung lewat handphone dengan tampilan antarmuka yang ramah dan responsif.</p>
                </div>
            </div>
        </div>

        <!-- TAB 7: FAQ -->
        <div x-show="activeTab === 'faq' && matchesSearch('faq kendala error lupa password rfid tidak terbaca pertanyaaan umum')" class="bg-white rounded-2xl shadow-md p-6 sm:p-8 space-y-6" x-transition>
            <div class="flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="p-3 bg-red-100 text-red-700 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Pertanyaan Umum (FAQ) & Problem Solving</h2>
                    <p class="text-xs sm:text-sm text-gray-500">Solusi cepat untuk kendala teknis yang sering ditemui</p>
                </div>
            </div>

            <div class="space-y-4">
                <template x-for="(item, index) in faqs" :key="index">
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <button @click="item.open = !item.open" class="w-full px-5 py-4 text-left font-semibold text-gray-800 hover:bg-gray-50 flex items-center justify-between transition-colors text-sm sm:text-base">
                            <span x-text="item.question"></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400 transition-transform duration-200" :class="item.open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="item.open" class="px-5 pb-4 text-xs sm:text-sm text-gray-600 bg-gray-50/50 border-t border-gray-100 pt-3" x-transition>
                            <p x-text="item.answer"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

</div>

<script>
    function panduanApp() {
        return {
            searchQuery: '',
            activeTab: 'overview',
            tabs: [
                { id: 'overview', label: 'Pengenalan', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                { id: 'users', label: 'Pengguna & Akun', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>' },
                { id: 'attendance', label: 'Presensi & RFID', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                { id: 'finance', label: 'Keuangan & Kas', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>' },
                { id: 'academic', label: 'Akademik & Ujian', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg>' },
                { id: 'mobile', label: 'Aplikasi Mobile', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>' },
                { id: 'faq', label: 'FAQ & Kendala', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' }
            ],
            faqs: [
                {
                    question: "Bagaimana cara mereset password akun pengguna yang lupa?",
                    answer: "Admin dapat membuka menu Master > Akun, cari nama/username pengguna, lalu klik tombol 'Edit Password'. Masukkan password baru (minimal 6 karakter) dan simpan.",
                    open: true
                },
                {
                    question: "Mengapa kartu RFID siswa tidak merespon saat di-scan?",
                    answer: "Pastikan nomor kartu RFID siswa sudah dimasukkan pada data murid (Master > Murid > Edit/Scan RFID). Jika belum terdaftar, sistem tidak akan mengenali ID kartu saat scanner membaca kartu.",
                    open: false
                },
                {
                    question: "Bagaimana cara melakukan konfirmasi pembayaran manual?",
                    answer: "Buka menu Keuangan > Pembayaran. Cari siswa yang bersangkutan, lalu klik Verifikasi pada daftar tagihan. Pembayaran akan otomatis ditandai Lunas dan dicatat di Buku Kas.",
                    open: false
                },
                {
                    question: "Bagaimana cara mengunggah soal ujian secara masal?",
                    answer: "Masuk ke menu Ujian > Soal, klik tombol 'Template Excel' untuk mengunduh format excel yang sesuai. Isi soal dan pilihan jawaban, kemudian unggah melalui tombol 'Import Soal'.",
                    open: false
                }
            ],
            matchesSearch(keywords) {
                if (!this.searchQuery.trim()) return true;
                const query = this.searchQuery.toLowerCase();
                return keywords.toLowerCase().includes(query);
            }
        }
    }
</script>
@endsection
