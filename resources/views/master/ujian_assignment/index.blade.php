@extends('base.layout')
@section('title', $title)
@section('content')
<div x-data="ujianAssignmentTable({{ $items->toJson() }}, {{ $classes->toJson() }})" class="flex flex-col bg-white rounded-lg shadow-md p-6">
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b pb-4">
        <h2 class="text-xl font-bold text-gray-800">Daftar Exam</h2>
        <a href="{{ route('dashboard.penjadwalan-ujian.create') }}"
            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-6 rounded-xl transition-all shadow-lg flex items-center gap-2 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14" />
                <path d="M12 5v14" />
            </svg>
            Tugaskan Ujian
        </a>
    </div>

    {{-- Form Deletion (Hidden) --}}
    <form id="deleteForm" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 text-green-700 rounded-xl border border-green-200">
        {{ session('success') }}
    </div>
    @endif

    <div class="mb-4 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="w-full flex flex-col md:flex-row gap-2">
            <div class="relative flex-1">
                <input type="text" x-model="search" placeholder="Cari Nama Murid atau Ujian..."
                    class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2.5 pl-10 focus:outline-[#177245] shadow-sm transition-all text-sm" />
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
            </div>

            <select x-model="selectedClass" class="border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-[#177245] shadow-sm transition-all text-sm min-w-[150px]">
                <option value="">Semua Kelas</option>
                <template x-for="cls in classes" :key="cls.id">
                    <option :value="cls.id" x-text="cls.name"></option>
                </template>
            </select>

            <select x-model="perPage" @change="currentPage = 1" class="border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-[#177245] shadow-sm transition-all text-sm">
                <option value="10">10 Data</option>
                <option value="20">20 Data</option>
                <option value="50">50 Data</option>
                <option value="100">100 Data</option>
                <option :value="rows.length">Semua</option>
            </select>
        </div>
    </div>

    <form id="bulkForm" method="POST" action="{{ route('dashboard.penjadwalan-ujian.bulk-verify') }}">
        @csrf
        <div x-show="selectedItems.length > 0" x-cloak class="mb-4 animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                <span class="text-sm font-bold text-blue-700 ml-2" x-text="`${selectedItems.length} dipilih`"></span>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded-xl text-xs transition-all shadow-md flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                    Verifikasi manual yang dipilih
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
            <table class="min-w-full bg-white text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-gray-600 border-b border-gray-200">
                        <th class="px-6 py-4 w-10">
                            <input type="checkbox" @change="toggleAll()" :checked="selectedItems.length > 0 && paginatedData().filter(r => r.can_verify).every(r => selectedItems.includes(r.id))" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('id')">No</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('ujian_nama')">Ujian</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('student_name')">Murid</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Kelas</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('status')">Status</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('payment_status')">Bayar</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('unique_code')">Kode Unik</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px] cursor-pointer" @click="sortBy('score')">Skor</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(row, index) in paginatedData()" :key="row.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <template x-if="row.can_verify">
                                    <input type="checkbox" name="ids[]" :value="row.id" x-model="selectedItems" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                </template>
                            </td>
                            <td class="px-6 py-4 text-gray-500 font-medium" x-text="(currentPage - 1) * perPage + index + 1"></td>
                            <td class="px-6 py-4 font-bold text-gray-800" x-text="row.ujian_nama"></td>
                            <td class="px-6 py-4 text-gray-700" x-text="row.student_name"></td>
                            <td class="px-6 py-4 text-gray-600">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="name in row.class_names">
                                        <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px]" x-text="name"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-full font-bold text-[10px] uppercase"
                                    :class="{
                                        'bg-gray-100 text-gray-600': row.status === 0,
                                        'bg-blue-100 text-blue-600': row.status === 1,
                                        'bg-green-100 text-green-600': row.status === 2
                                    }"
                                    x-text="row.status === 2 ? 'Selesai' : (row.status === 1 ? 'Mengerjakan' : 'Belum')">
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <template x-if="!row.is_paid">
                                    <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full font-bold text-[10px] uppercase border border-blue-200">Gratis</span>
                                </template>
                                <template x-if="row.is_paid && row.payment_status === 1">
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full font-bold text-[10px] uppercase border border-green-200">Lunas</span>
                                </template>
                                <template x-if="row.is_paid && row.payment_status === 0">
                                    <div>
                                        <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full font-bold text-[10px] uppercase border border-orange-200">Belum</span>
                                        <div class="text-[10px] text-gray-400 mt-0.5" x-text="`Rp ${formatNumber(row.harga)}`"></div>
                                    </div>
                                </template>
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-700" x-text="row.unique_code || '-'"></td>
                            <td class="px-6 py-4 font-bold text-gray-800" x-text="row.score || '-'"></td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center gap-1 justify-center">
                                    <button type="button" @click="showDetail(row.id)" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                    <template x-if="row.status === 2">
                                        <a :href="`/dashboard/penjadwalan-ujian/${row.id}/pdf`" target="_blank" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Download PDF Evaluasi">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                                <polyline points="7 10 12 15 17 10" />
                                                <line x1="12" x2="12" y1="15" y2="3" />
                                            </svg>
                                        </a>
                                    </template>
                                    <button type="button" @click="confirmDelete(row.id)" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                            <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="filteredData().length === 0">
                        <td colspan="9" class="text-center px-6 py-10 text-gray-500">
                            Tidak ada data yang ditemukan.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="text-sm text-gray-500">
            Menampilkan <span class="font-medium" x-text="Math.min((currentPage - 1) * perPage + 1, filteredData().length)"></span>
            sampai <span class="font-medium" x-text="Math.min(currentPage * perPage, filteredData().length)"></span>
            dari <span class="font-medium" x-text="filteredData().length"></span> data
        </div>
        <div class="flex items-center gap-2">
            <button @click="prevPage()" :disabled="currentPage === 1" class="px-4 py-2 border rounded-xl hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm font-medium">Prev</button>
            <div class="flex items-center gap-1">
                <template x-for="page in totalPages()" :key="page">
                    <button @click="currentPage = page"
                        class="w-10 h-10 rounded-xl text-sm font-medium transition-all"
                        :class="currentPage === page ? 'bg-green-600 text-white shadow-md' : 'hover:bg-gray-100 text-gray-600'"
                        x-text="page">
                    </button>
                </template>
            </div>
            <button @click="nextPage()" :disabled="currentPage === totalPages()" class="px-4 py-2 border rounded-xl hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-all text-sm font-medium">Next</button>
        </div>
    </div>

    <!-- Detail Modal -->
    <div x-show="detailModalOpen" x-cloak class="fixed inset-0 bg-black/50 z-[999] flex items-center justify-center p-4 transition-all duration-300" x-transition.opacity>
        <div @click.outside="closeDetailModal()" class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col transform transition-all" x-transition.scale.95>
            <div class="p-6 border-b flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-xl font-bold text-gray-800" x-text="detailData ? detailData.ujian.nama : 'Memuat...'"></h3>
                    <p class="text-sm text-gray-500" x-text="detailData ? `${detailData.student.name} • Skor: ${detailData.item.score}` : ''"></p>
                </div>
                <button @click="closeDetailModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6 overflow-y-auto flex-1 bg-white relative">
                <div x-show="detailLoading" class="flex justify-center py-10">
                    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600"></div>
                </div>
                <div x-show="!detailLoading && detailData" class="space-y-8">
                    <template x-for="(soal, index) in (detailData ? detailData.soals : [])" :key="soal.id">
                        <div class="p-6 rounded-2xl border transition-all" :class="getScoreBg(isAnswerCorrect(soal, detailData.answers[soal.id]))">
                            <div class="flex justify-between items-start mb-4">
                                <span class="bg-gray-800 text-white text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-wider" x-text="`Soal ${index + 1}`"></span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border"
                                    :class="getScoreBg(isAnswerCorrect(soal, detailData.answers[soal.id])) + ' ' + getScoreColor(isAnswerCorrect(soal, detailData.answers[soal.id]))"
                                    x-text="isAnswerCorrect(soal, detailData.answers[soal.id]) ? '✓ Benar' : '✗ Salah'">
                                </span>
                            </div>
                            <div class="prose prose-sm max-w-none text-gray-800 mb-6 font-medium leading-relaxed" x-html="soal.nama"></div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-4">
                                <div class="p-4 rounded-xl bg-white border border-gray-100 shadow-sm flex flex-col gap-1">
                                    <span class="text-gray-400 font-bold text-[9px] uppercase tracking-widest">Jawaban Murid</span>
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-lg text-white font-bold text-xs"
                                            :class="isAnswerCorrect(soal, detailData.answers[soal.id]) ? 'bg-green-600' : 'bg-red-600'"
                                            x-text="detailData.answers[soal.id] || '-'"></span>
                                        <span class="font-bold text-gray-700" x-text="getStudentValue(soal, detailData.answers[soal.id])"></span>
                                    </div>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-gray-100 shadow-sm flex flex-col gap-1">
                                    <span class="text-gray-400 font-bold text-[9px] uppercase tracking-widest">Kunci Jawaban</span>
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-green-600 text-white font-bold text-xs">✔</span>
                                        <span class="font-bold text-green-700" x-text="stripHtml(soal.jawaban)"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
                <template x-if="detailData">
                    <a :href="`/dashboard/penjadwalan-ujian/${detailData.item.id}/pdf`" target="_blank" class="px-6 py-2 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" x2="12" y1="15" y2="3" />
                        </svg>
                        Download PDF
                    </a>
                </template>
                <button @click="closeDetailModal()" class="px-6 py-2 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-all">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection