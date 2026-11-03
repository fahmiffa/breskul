@extends('base.layout')
@section('title', $title)
@section('content')
<div class="max-w-2xl mx-auto" x-data="gnucashImport()">
    <div class="bg-white rounded-xl shadow-md p-6 sm:p-8">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Import File GnuCash</h1>
                <p class="text-xs text-gray-500 mt-0.5">Upload file berekstensi <code>.gnucash</code> untuk mengimpor data akuntansi (akun dan transaksi) via background job queue.</p>
            </div>
            <a href="{{ route('dashboard.gnucash.transactions.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali
            </a>
        </div>

        <!-- Error Alert -->
        <template x-if="error">
            <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl text-xs text-red-700 flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500 shrink-0 mt-0.5">
                    <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div class="flex-1">
                    <p class="font-bold">Gagal Mengimpor File</p>
                    <p class="mt-0.5" x-text="error"></p>
                </div>
            </div>
        </template>

        <!-- Progress Box (Active while Uploading or Processing) -->
        <div x-show="isUploading || isProcessing" x-cloak class="mb-6 p-5 bg-blue-50 border border-blue-200 rounded-xl">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-xs font-bold text-blue-900" x-text="isUploading ? 'Mengunggah File...' : 'Memproses di Background (Job Queue)...'"></span>
                </div>
                <span class="text-xs font-extrabold text-blue-700" x-text="progress + '%'"></span>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-blue-200/60 rounded-full h-3 overflow-hidden">
                <div class="bg-blue-600 h-3 rounded-full transition-all duration-300 ease-out"
                     :style="{ width: progress + '%' }"></div>
            </div>

            <p class="text-xs text-blue-700 mt-2 flex items-center justify-between">
                <span x-text="status || 'Menunggu proses...'"></span>
                <span class="text-[11px] text-blue-500 italic">Halaman dapat ditutup atau ditinggal</span>
            </p>
        </div>

        <!-- Success Box -->
        <div x-show="isFinished" x-cloak class="mb-6 p-5 bg-emerald-50 border border-emerald-200 rounded-xl">
            <div class="flex items-center gap-2.5 mb-2">
                <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-900">Import Berhasil Diselesaikan!</h3>
                    <p class="text-xs text-emerald-700" x-text="status"></p>
                </div>
            </div>

            <!-- Summary Results if Available -->
            <template x-if="result">
                <div class="mt-3 pt-3 border-t border-emerald-200/70 grid grid-cols-2 sm:grid-cols-4 gap-2 text-center">
                    <div class="bg-white/80 p-2 rounded-lg border border-emerald-100">
                        <p class="text-[10px] text-gray-500 uppercase font-semibold">Komoditas</p>
                        <p class="text-sm font-bold text-gray-800" x-text="result.commodities ?? 0"></p>
                    </div>
                    <div class="bg-white/80 p-2 rounded-lg border border-emerald-100">
                        <p class="text-[10px] text-gray-500 uppercase font-semibold">Bagan Akun</p>
                        <p class="text-sm font-bold text-gray-800" x-text="result.accounts ?? 0"></p>
                    </div>
                    <div class="bg-white/80 p-2 rounded-lg border border-emerald-100">
                        <p class="text-[10px] text-gray-500 uppercase font-semibold">Transaksi</p>
                        <p class="text-sm font-bold text-gray-800" x-text="result.transactions ?? 0"></p>
                    </div>
                    <div class="bg-white/80 p-2 rounded-lg border border-emerald-100">
                        <p class="text-[10px] text-gray-500 uppercase font-semibold">Waktu</p>
                        <p class="text-sm font-bold text-gray-800" x-text="(result.duration ?? 0) + 's'"></p>
                    </div>
                </div>
            </template>

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                   class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold py-2 px-4 rounded-xl transition flex items-center gap-1.5 shadow">
                    <span>Lihat Daftar Transaksi</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="{{ route('dashboard.gnucash.accounts.index') }}"
                   class="bg-white hover:bg-emerald-100 text-emerald-800 border border-emerald-300 text-xs font-semibold py-2 px-3 rounded-xl transition">
                    Lihat Bagan Akun (COA)
                </a>
                <button type="button" @click="resetForm()"
                        class="text-xs text-gray-500 hover:text-gray-800 font-semibold py-2 px-3 rounded-xl transition underline">
                    Import File Lain
                </button>
            </div>
        </div>

        <!-- Import Form -->
        <form @submit.prevent="submitImport" class="space-y-5" x-show="!isFinished">
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">File GnuCash (.gnucash)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition"
                     :class="{ 'opacity-50 pointer-events-none': isUploading || isProcessing }">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-gray-400 mb-3">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <input type="file" name="gnucash_file" accept=".gnucash,.gz,.xml" required
                           @change="handleFile($event)"
                           :disabled="isUploading || isProcessing"
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                    <p class="text-xs text-gray-400 mt-2">Mendukung file XML terkompresi (gzip) dan file XML biasa.</p>
                </div>
            </div>

            <!-- Fresh import option -->
            <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <input type="checkbox" id="fresh" name="fresh" value="1"
                       x-model="fresh"
                       :disabled="isUploading || isProcessing"
                       class="mt-1 w-4 h-4 text-amber-600 rounded border-gray-300 focus:ring-amber-500" />
                <label for="fresh" class="text-xs text-gray-700 cursor-pointer">
                    <span class="font-semibold block text-amber-800">Import Fresh (Hapus Data Lama)</span>
                    Centang opsi ini jika ingin mengosongkan seluruh data akun dan transaksi GnuCash yang ada di database sebelum mengimpor data baru. Data non-GnuCash tidak akan terpengaruh.
                </label>
            </div>

            <!-- Background Job Info Banner -->
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <h3 class="text-xs font-bold text-indigo-900 mb-1.5 flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Proses Berjalan di Antrean Latar Belakang (Job Queue)
                </h3>
                <ul class="text-xs text-indigo-800 space-y-1 list-disc list-inside">
                    <li>File tidak membebani web server saat diunggah karena diproses di antrean antrian (queue).</li>
                    <li>Browser tidak akan timeout (bebas dari error 504 Gateway Timeout).</li>
                    <li>Pastikan queue worker aktif di terminal/server: <code>php artisan queue:work</code>.</li>
                </ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2.5 px-5 rounded-xl transition"
                   :class="{ 'pointer-events-none opacity-50': isUploading || isProcessing }">
                    Batal
                </a>
                <button type="submit"
                        :disabled="isUploading || isProcessing || !file"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-300 text-white text-xs font-semibold py-2.5 px-6 rounded-xl transition shadow flex items-center gap-1.5 cursor-pointer disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         x-show="!isUploading && !isProcessing">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <svg class="animate-spin h-4 w-4 text-white" x-show="isUploading || isProcessing" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!isUploading && !isProcessing">Mulai Import</span>
                    <span x-show="isUploading" x-cloak>Mengunggah...</span>
                    <span x-show="isProcessing" x-cloak>Memproses di Queue...</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function gnucashImport() {
    return {
        file: null,
        fresh: false,
        jobId: null,
        progress: 0,
        status: '',
        error: '',
        isUploading: false,
        isProcessing: false,
        isFinished: false,
        result: null,
        pollInterval: null,

        handleFile(event) {
            this.file = event.target.files[0] || null;
            this.error = '';
        },

        resetForm() {
            if (this.pollInterval) clearInterval(this.pollInterval);
            this.file = null;
            this.fresh = false;
            this.jobId = null;
            this.progress = 0;
            this.status = '';
            this.error = '';
            this.isUploading = false;
            this.isProcessing = false;
            this.isFinished = false;
            this.result = null;
        },

        submitImport() {
            if (!this.file) {
                this.error = 'Pilih file .gnucash terlebih dahulu.';
                return;
            }

            this.error = '';
            this.isUploading = true;
            this.isProcessing = false;
            this.isFinished = false;
            this.progress = 0;
            this.status = 'Mengunggah file ke server...';

            const formData = new FormData();
            formData.append('gnucash_file', this.file);
            if (this.fresh) {
                formData.append('fresh', '1');
            }

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('{{ route('dashboard.gnucash.transactions.import') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(errData => {
                        throw new Error(errData.message || 'Gagal memulai import');
                    });
                }
                return res.json();
            })
            .then(data => {
                this.isUploading = false;
                this.isProcessing = true;
                this.jobId = data.jobId;
                this.status = 'File berhasil diunggah, menunggu worker queue...';
                this.startPolling();
            })
            .catch(err => {
                this.isUploading = false;
                this.isProcessing = false;
                this.error = err.message || 'Terjadi kesalahan saat mengunggah file.';
            });
        },

        startPolling() {
            if (this.pollInterval) clearInterval(this.pollInterval);

            this.pollInterval = setInterval(() => {
                if (!this.jobId) return;

                fetch(`/dashboard/job-progress/${this.jobId}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        this.status = data.status;
                    }

                    if (data.progress === -1 || data.error) {
                        clearInterval(this.pollInterval);
                        this.isProcessing = false;
                        this.error = data.error || 'Terjadi kegagalan saat proses import.';
                        return;
                    }

                    if (data.progress >= 0) {
                        this.progress = data.progress;
                    }

                    if (data.progress >= 100) {
                        clearInterval(this.pollInterval);
                        this.isProcessing = false;
                        this.isFinished = true;
                        this.status = data.status || 'Import berhasil diselesaikan!';
                        this.result = data.result || null;
                    }
                })
                .catch(() => {
                    // Jangan gagalkan polling jika ada spike koneksi sementara
                });
            }, 1000);
        }
    };
}
</script>
@endsection
