@extends('base.layout')
@section('title', $title)
@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6 sm:p-8">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Import File GnuCash</h1>
                <p class="text-xs text-gray-500 mt-0.5">Upload file berekstensi <code>.gnucash</code> untuk mengimpor data akuntansi (akun dan transaksi).</p>
            </div>
            <a href="{{ route('dashboard.gnucash.transactions.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali
            </a>
        </div>

        <form action="{{ route('dashboard.gnucash.transactions.import') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">File GnuCash (.gnucash)</label>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-green-400 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-gray-400 mb-3">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <input type="file" name="gnucash_file" accept=".gnucash,.gz,.xml" required
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 cursor-pointer" />
                    <p class="text-xs text-gray-400 mt-2">Mendukung file XML terkompresi (gzip) dan file XML biasa.</p>
                </div>
                @error('gnucash_file')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fresh import option -->
            <div class="flex items-start gap-3 p-4 bg-amber-50 rounded-xl border border-amber-200">
                <input type="checkbox" id="fresh" name="fresh" value="1"
                       class="mt-1 w-4 h-4 text-amber-600 rounded border-gray-300 focus:ring-amber-500" />
                <label for="fresh" class="text-xs text-gray-700 cursor-pointer">
                    <span class="font-semibold block text-amber-800">Import Fresh (Hapus Data Lama)</span>
                    Centang opsi ini jika ingin menghapus seluruh data akun dan transaksi GnuCash yang ada di database sebelum mengimpor data baru. Data non-GnuCash tidak akan terpengaruh.
                </label>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <h3 class="text-xs font-bold text-blue-800 mb-1.5">ℹ️ Informasi Import</h3>
                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                    <li>Proses import akan membaca mata uang, bagan akun (COA), transaksi, dan rincian jurnal (splits).</li>
                    <li>Jika data sudah ada (berdasarkan ID), data akan di-update (upsert).</li>
                    <li>Proses import bisa memerlukan beberapa detik tergantung ukuran file.</li>
                </ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2.5 px-5 rounded-xl transition">
                    Batal
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2.5 px-6 rounded-xl transition shadow flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    Mulai Import
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
