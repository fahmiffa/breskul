@extends('base.layout')
@section('title', $title)
@section('content')
<div class="flex flex-col bg-white rounded-lg shadow-md p-6">
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
        <form method="GET" action="{{ route('dashboard.penjadwalan-ujian.index') }}" class="w-full flex flex-col md:flex-row gap-2">
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Murid..."
                    class="w-full border border-gray-300 ring-0 rounded-xl px-4 py-2.5 pl-10 focus:outline-[#177245] shadow-sm transition-all text-sm" />
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </div>
            </div>

            <select name="class_id" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-[#177245] shadow-sm transition-all text-sm min-w-[150px]">
                <option value="">Semua Kelas</option>
                @foreach($classes as $class)
                <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="hidden">Filter</button>
        </form>
    </div>

    <form id="bulkForm" method="POST" action="{{ route('dashboard.penjadwalan-ujian.bulk-verify') }}">
        @csrf
        <div id="bulkActions" class="mb-4 hidden animate-in fade-in slide-in-from-top-2 duration-300">
            <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-100 rounded-xl">
                <span class="text-sm font-medium text-blue-700 ml-2" id="selectedCount">0 dipilih</span>
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
                            <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">No</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Ujian</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Murid</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Kelas</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Status</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Bayar</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Skor</th>
                        <th class="px-6 py-4 font-bold uppercase tracking-wider text-[11px]">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $index => $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            @if($row->ujian->is_paid && $row->payment_status == 0)
                            <input type="checkbox" name="ids[]" value="{{ $row->id }}" class="row-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 font-medium">{{ $items->firstItem() + $index }}</td>
                        <td class="px-6 py-4 font-bold text-gray-800">{{ $row->ujian->nama }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $row->student->name }}</td>
                        <td class="px-6 py-4 text-gray-600">
                            @foreach($row->student->Kelas as $kelas)
                            <span class="px-2 py-0.5 bg-gray-100 rounded text-[10px]">{{ $kelas->name }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            @php
                            $statusMap = [
                            0 => ['label' => 'Belum', 'class' => 'bg-gray-100 text-gray-600'],
                            1 => ['label' => 'Mengerjakan', 'class' => 'bg-blue-100 text-blue-600'],
                            2 => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-600'],
                            ];
                            $st = $statusMap[$row->status] ?? $statusMap[0];
                            @endphp
                            <span class="px-2 py-1 {{ $st['class'] }} rounded-full font-bold text-[10px] uppercase">
                                {{ $st['label'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if(!$row->ujian->is_paid)
                            <span class="px-2 py-1 bg-blue-50 text-blue-600 rounded-full font-bold text-[10px] uppercase border border-blue-200">Gratis</span>
                            @elseif($row->payment_status == 1)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full font-bold text-[10px] uppercase border border-green-200">✓ Lunas</span>
                            @else
                            <div>
                                <span class="px-2 py-1 bg-orange-100 text-orange-700 rounded-full font-bold text-[10px] uppercase border border-orange-200">Belum Bayar</span>
                                <div class="text-[10px] text-gray-400 mt-0.5">Rp {{ number_format($row->ujian->harga, 0, ',', '.') }}</div>
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-800">{{ $row->score ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center gap-1 justify-center">
                                @if($row->status == 2)
                                <a href="{{ route('dashboard.penjadwalan-ujian.pdf', $row->id) }}" target="_blank" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Download PDF Evaluasi">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" x2="12" y1="15" y2="3" />
                                    </svg>
                                </a>
                                @endif
                                <button type="button" onclick="confirmDelete({{ $row->id }})" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center px-6 py-10 text-gray-500">
                            Belum ada ujian yang ditugaskan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-6">
        {{ $items->links() }}
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-black bg-opacity-50 z-[999] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        <div class="p-6 border-b flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-xl font-bold text-gray-800" id="modalTitle">Detail Ujian</h3>
                <p class="text-sm text-gray-500" id="modalSubtitle"></p>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-2 rounded-lg hover:bg-gray-100 transition-all">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto flex-1 bg-white" id="modalContent">
            <!-- Questions and answers will be injected here -->
            <div class="flex justify-center py-10">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600"></div>
            </div>
        </div>
        <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
            <a id="btnDownloadPdf" href="#" target="_blank" class="px-6 py-2 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition-all flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" x2="12" y1="15" y2="3" />
                </svg>
                Download PDF
            </a>
            <button onclick="closeModal()" class="px-6 py-2 bg-gray-200 text-gray-700 font-bold rounded-xl hover:bg-gray-300 transition-all">Tutup</button>
        </div>
    </div>
</div>

<script>
    // Bulk Selection Logic
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    const selectedCount = document.getElementById('selectedCount');

    function confirmDelete(id) {
        if (confirm('Hapus penugasan ini?')) {
            const form = document.getElementById('deleteForm');
            form.action = `{{ url('dashboard/penjadwalan-ujian') }}/${id}`;
            form.submit();
        }
    }

    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.innerText = `${checkedCount} dipilih`;
        } else {
            bulkActions.classList.add('hidden');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActions();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateBulkActions();
            if (!this.checked) selectAll.checked = false;
            if (document.querySelectorAll('.row-checkbox:checked').length === rowCheckboxes.length) selectAll.checked = true;
        });
    });

    function showDetail(id) {
        const modal = document.getElementById('detailModal');
        const content = document.getElementById('modalContent');
        const title = document.getElementById('modalTitle');
        const subtitle = document.getElementById('modalSubtitle');

        modal.classList.remove('hidden');
        content.innerHTML = `<div class="flex justify-center py-10"><div class="animate-spin rounded-full h-10 w-10 border-b-2 border-green-600"></div></div>`;
        document.getElementById('btnDownloadPdf').href = `{{ url('dashboard/penjadwalan-ujian') }}/${id}/pdf`;

        fetch(`{{ url('dashboard/penjadwalan-ujian') }}/${id}`)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    const data = res.data;
                    title.innerText = data.ujian.nama;
                    subtitle.innerText = `${data.student.name} • Skor: ${data.item.score}`;

                    let html = '';
                    const stripHtml = (html) => {
                        let tmp = document.createElement("DIV");
                        tmp.innerHTML = html;
                        return tmp.textContent || tmp.innerText || "";
                    };

                    data.soals.forEach((soal, index) => {
                        const studentKey = (data.answers[soal.id] || '-').toString().trim().toUpperCase();
                        const correctValue = stripHtml(soal.jawaban).trim();

                        // Deteksi value student (kunci atau teks langsung)
                        let studentValue = studentKey;
                        if (['A', 'B', 'C', 'D', 'E'].includes(studentKey)) {
                            const optKey = 'opsi_' + studentKey.toLowerCase();
                            studentValue = soal[optKey] ? stripHtml(soal[optKey]) : studentKey;
                        }

                        const isCorrect = stripHtml(studentValue).trim().toLowerCase() === correctValue.toLowerCase() ||
                            studentKey.toLowerCase() === correctValue.toLowerCase();

                        html += `
                        <div class="mb-8 p-6 rounded-2xl border ${isCorrect ? 'border-green-100 bg-green-50/30' : 'border-red-100 bg-red-50/30'}">
                            <div class="flex justify-between items-start mb-4">
                                <span class="bg-gray-800 text-white text-[10px] px-2 py-1 rounded-md font-bold uppercase tracking-wider">Soal ${index + 1}</span>
                                <span class="px-3 py-1 rounded-full text-[10px] font-black ${isCorrect ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'} uppercase tracking-widest border ${isCorrect ? 'border-green-200' : 'border-red-200'}">
                                    ${isCorrect ? '✓ Benar' : '✗ Salah'}
                                </span>
                            </div>
                            <div class="prose prose-sm max-w-none text-gray-800 mb-6 font-medium leading-relaxed">
                                ${soal.nama}
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm mt-4">
                                <div class="p-4 rounded-xl bg-white border border-gray-100 shadow-sm flex flex-col gap-1">
                                    <span class="text-gray-400 font-bold text-[9px] uppercase tracking-widest">Jawaban Murid</span>
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-lg ${isCorrect ? 'bg-green-600' : 'bg-red-600'} text-white font-bold text-xs">${studentKey}</span>
                                        <span class="font-bold text-gray-700">${studentValue}</span>
                                    </div>
                                </div>
                                <div class="p-4 rounded-xl bg-white border border-gray-100 shadow-sm flex flex-col gap-1">
                                    <span class="text-gray-400 font-bold text-[9px] uppercase tracking-widest">Kunci Jawaban</span>
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 flex items-center justify-center rounded-lg bg-green-600 text-white font-bold text-xs">✔</span>
                                        <span class="font-bold text-green-700">${correctValue}</span>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    });

                    content.innerHTML = html;
                } else {
                    content.innerHTML = `<div class="text-center py-10 text-red-500 font-bold">Gagal memuat data detail ujian.</div>`;
                }
            })
            .catch(err => {
                console.error(err);
                content.innerHTML = `<div class="text-center py-10 text-red-500 font-bold">Terjadi kesalahan sistem saat mengambil data.</div>`;
            });
    }

    function closeModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // Close modal on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('detailModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
@endsection