@extends('base.layout')
@section('title', 'Kebijakan Privasi')
@section('content')
@section('content')
    <div class="max-w-3xl mx-auto py-10 px-6">
        <h1 class="text-4xl font-bold text-blue-600 mb-8">
            Kebijakan Privasi
        </h1>

        <p class=" text-sm text-gray-500 mb-10">
            Terakhir diperbarui: <span class="font-medium text-gray-700">31 Oktober 2025</span>
        </p>

        <section class="space-y-6">
            <p>
                Selamat datang di <span class="font-semibold">BRESKUL</span>. Kami menghargai privasi Anda dan
                berkomitmen untuk melindungi informasi pribadi Anda.
                Dokumen ini menjelaskan bagaimana kami mengumpulkan, menggunakan, dan melindungi data Anda ketika
                menggunakan layanan kami.
            </p>

            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">1. Informasi yang Kami Kumpulkan</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Data pribadi (nama, email, nomor telepon)</li>
                    <li>Data penggunaan aplikasi (Informasi absensi dan pengumuman)</li>
                    <li>Data perangkat (model, sistem operasi, versi aplikasi)</li>
                </ul>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">2. Cara Kami Menggunakan Informasi</h2>
                <ul class="list-disc list-inside space-y-1">
                    <li>Menyediakan dan meningkatkan layanan aplikasi</li>
                    <li>Menghubungi Anda untuk dukungan atau pembaruan</li>
                    <li>Menganalisis penggunaan untuk meningkatkan pengalaman pengguna</li>
                </ul>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">3. Keamanan Data</h2>
                <p>
                    Kami berusaha melindungi data Anda dengan menerapkan langkah-langkah keamanan teknis dan organisasi yang
                    sesuai untuk mencegah akses, penggunaan, atau pengungkapan yang tidak sah.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">4. Pembagian Informasi</h2>
                <p>
                    Kami tidak menjual atau membagikan data pribadi Anda kepada pihak ketiga, kecuali jika diwajibkan oleh
                    hukum atau diperlukan untuk penyedia layanan pihak ketiga yang membantu operasional aplikasi.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">5. Hak Pengguna</h2>
                <p>Anda berhak untuk:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Meminta salinan data pribadi Anda</li>
                    <li>Memperbarui atau menghapus data Anda</li>
                    <li>Menarik izin penggunaan data kapan saja</li>
                </ul>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">6. Perubahan Kebijakan Privasi</h2>
                <p>
                    Kami dapat memperbarui kebijakan ini dari waktu ke waktu. Versi terbaru akan selalu tersedia di halaman
                    ini, dengan tanggal pembaruan di bagian atas.
                </p>
            </div>

            <div>
                <h2 class="text-2xl font-semibold text-blue-500 mb-2">7. Kontak Kami</h2>
                <p>
                    Jika Anda memiliki pertanyaan tentang kebijakan privasi ini, silakan hubungi kami di:
                </p>
                <p class="mt-2 font-medium">
                    📧 <a href="mailto:faisol.ajifa@gmail.com"
                        class="text-blue-600 hover:underline">faisol.ajifa@gmail.com</a>
                </p>
            </div>
        </section>

        <footer class="text-center text-gray-500 text-sm mt-10 border-t pt-6">
            © 2025 BRESKUL. Semua hak dilindungi.
        </footer>
    </div>
@endsection
