@extends('base.layout')
@section('title', $title)

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
    <style>
        .ts-wrapper.journal-ts-select {
            width: 100%;
            font-size: 12px;
        }
        .ts-wrapper.journal-ts-select .ts-control {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 4px 8px;
            font-size: 12px;
            min-height: 32px;
            background-color: #ffffff;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }
        .ts-wrapper.journal-ts-select.focus .ts-control {
            border-color: #16a34a;
            box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
        }
        .ts-wrapper.journal-ts-select .ts-control input {
            font-size: 12px;
        }
        .ts-dropdown.journal-ts-dropdown {
            font-size: 12px;
            z-index: 99999 !important;
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .ts-dropdown.journal-ts-dropdown .option {
            padding: 6px 10px;
            font-size: 12px;
        }
        .ts-dropdown.journal-ts-dropdown .option.active {
            background-color: #f0fdf4 !important;
            color: #166534 !important;
        }
        .ts-dropdown.journal-ts-dropdown .option.selected {
            background-color: #dcfce7 !important;
            color: #15803d !important;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
<script>
let uidCounter = 0;

function initRowSelect(el, row) {
    function setup() {
        const TomSelectClass = window.TomSelect || (typeof TomSelect !== 'undefined' ? TomSelect : null);
        if (!TomSelectClass) {
            setTimeout(setup, 30);
            return;
        }

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        const ts = new TomSelectClass(el, {
            create: false,
            maxOptions: 1000,
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown journal-ts-dropdown',
            placeholder: '-- Pilih Akun --',
            plugins: ['clear_button'],
            controlInput: '<input>',
            render: {
                option: function(data, escape) {
                    const codeBadge = data.code ? `<span class="font-mono text-[10px] bg-gray-100 text-gray-700 px-1 py-0.5 rounded mr-1.5">${escape(data.code)}</span>` : '';
                    const typeBadge = data.type ? `<span class="text-[9px] font-semibold px-1.5 py-0.5 rounded ml-1 bg-gray-100 text-gray-600">${escape(data.type)}</span>` : '';
                    return `<div class="flex items-center justify-between py-1">
                        <div class="truncate text-xs">
                            ${codeBadge}
                            <span class="text-gray-900">${escape(data.path || data.text)}</span>
                        </div>
                        ${typeBadge}
                    </div>`;
                },
                item: function(data, escape) {
                    const codeBadge = data.code ? `<span class="font-mono text-[10px] bg-gray-100 text-gray-700 px-1 py-0.5 rounded mr-1.5">${escape(data.code)}</span>` : '';
                    return `<div class="truncate text-xs">${codeBadge}<span>${escape(data.path || data.text)}</span></div>`;
                },
                no_results: function(data, escape) {
                    return '<div class="no-results text-xs text-gray-500 p-2">Tidak ditemukan akun "' + escape(data.input) + '"</div>';
                }
            }
        });

        ts.on('change', function(val) {
            row.account_id = val || '';
            el.value = val || '';
        });

        el.addEventListener('change', function() {
            row.account_id = el.value || '';
        });

        el.closest('.ts-wrapper')?.classList.add('journal-ts-select');

        if (row.account_id) {
            ts.setValue(String(row.account_id), true);
        }

    }

    setup();
}

function journalForm() {
    const rawSplits = @json($splits) || [];

    return {
        rows: rawSplits.map(s => ({
            uid: ++uidCounter,
            account_id: s.account_id ? String(s.account_id) : '',
            memo: s.memo || '',
            debit: s.debit !== undefined ? s.debit : '',
            credit: s.credit !== undefined ? s.credit : ''
        })),
        addRow() {
            this.rows.push({
                uid: ++uidCounter,
                account_id: '',
                memo: '',
                debit: '',
                credit: ''
            });
        },
        removeRow(idx) {
            if (this.rows.length > 2) {
                this.rows.splice(idx, 1);
            }
        },
        totalDebit() {
            return this.rows.reduce((s, r) => s + (parseFloat(r.debit) || 0), 0);
        },
        totalCredit() {
            return this.rows.reduce((s, r) => s + (parseFloat(r.credit) || 0), 0);
        },
        formatNumber(val) {
            return val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        validateBeforeSubmit(e) {
            const selects = document.querySelectorAll('.journal-account-select');
            for (let i = 0; i < this.rows.length; i++) {
                const selectVal = selects[i] ? selects[i].value : '';
                const modelVal = this.rows[i].account_id;
                const accountVal = modelVal || selectVal || '';

                if (!accountVal) {
                    e.preventDefault();
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Akun Belum Dipilih',
                            text: 'Baris ke-' + (i + 1) + ' belum memiliki akun. Harap pilih akun untuk semua baris.',
                            confirmButtonColor: '#16a34a'
                        });
                    } else {
                        alert('Baris ke-' + (i + 1) + ' belum memiliki akun. Harap pilih akun untuk semua baris.');
                    }
                    if (selects[i] && selects[i].tomselect) {
                        selects[i].tomselect.focus();
                    }
                    return false;
                }

                // Sync back to ensure form submission has correct data
                this.rows[i].account_id = accountVal;
                if (selects[i]) {
                    selects[i].value = accountVal;
                }
            }
        }
    };
}
</script>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6 sm:p-8" x-data="journalForm()">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $action }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Entri jurnal double-entry (total Debit harus sama dengan total Kredit).</p>
            </div>
            <a href="{{ route('dashboard.gnucash.transactions.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali
            </a>
        </div>

        <form action="{{ $transaction->id ? route('dashboard.gnucash.transactions.update', $transaction->id) : route('dashboard.gnucash.transactions.store') }}"
              method="POST" class="space-y-5" @submit="validateBeforeSubmit($event)">
            @csrf
            @if($transaction->id)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Tanggal -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="post_date"
                           value="{{ old('post_date', $transaction->post_date ? $transaction->post_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           required
                           class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('post_date') border-red-500 @enderror" />
                    @error('post_date')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No. Ref -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nomor Referensi</label>
                    <input type="text" name="num" value="{{ old('num', $transaction->num) }}" placeholder="Contoh: INV-001, BKM-2024"
                           class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500" />
                </div>

                <!-- Deskripsi -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Deskripsi <span class="text-red-500">*</span></label>
                    <input type="text" name="description" value="{{ old('description', $transaction->description) }}" required
                           placeholder="Keterangan transaksi..."
                           class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('description') border-red-500 @enderror" />
                    @error('description')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Catatan / Notes -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Catatan Tambahan</label>
                <textarea name="notes" rows="2" placeholder="Opsional..."
                          class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('notes', $transaction->notes) }}</textarea>
            </div>

            <!-- SPLITS TABLE -->
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="bg-green-600 text-white px-4 py-2.5 flex items-center justify-between">
                    <span class="text-sm font-bold">Rincian Jurnal (Splits)</span>
                    <button type="button" @click="addRow()"
                            class="bg-white/20 hover:bg-white/30 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Baris
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase font-semibold">
                                <th class="px-3 py-2.5 text-left w-12">#</th>
                                <th class="px-3 py-2.5 text-left min-w-[280px]">Akun <span class="text-red-500">*</span></th>
                                <th class="px-3 py-2.5 text-left">Memo</th>
                                <th class="px-3 py-2.5 text-right w-40">Debit (Rp)</th>
                                <th class="px-3 py-2.5 text-right w-40">Kredit (Rp)</th>
                                <th class="px-3 py-2.5 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(row, idx) in rows" :key="row.uid">
                                <tr class="hover:bg-gray-50/50 transition">
                                    <td class="px-3 py-2 text-center text-xs text-gray-400" x-text="idx + 1"></td>
                                    <td class="px-3 py-2 min-w-[280px]">
                                        <select :name="'splits['+idx+'][account_id]'"
                                                x-model="row.account_id"
                                                x-init="initRowSelect($el, row)"
                                                class="journal-account-select w-full text-xs"
                                                data-placeholder="-- Pilih Akun --">
                                            <option value="">-- Pilih Akun --</option>
                                            @foreach($accounts as $acc)
                                                <option value="{{ $acc->id }}"
                                                        data-code="{{ $acc->code }}"
                                                        data-type="{{ $acc->account_type }}"
                                                        data-path="{{ $acc->resolved_path ?: $acc->name }}">{{ $acc->code ? '[' . $acc->code . '] ' : '' }}{{ $acc->resolved_path ?: $acc->name }} ({{ $acc->account_type }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="text" :name="'splits['+idx+'][memo]'" x-model="row.memo"
                                               placeholder="Opsional..."
                                               class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" :name="'splits['+idx+'][debit]'" x-model.number="row.debit"
                                               step="0.01" min="0" placeholder="0"
                                               @input="if(row.debit > 0) row.credit = ''"
                                               class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right font-mono focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                    </td>
                                    <td class="px-3 py-2">
                                        <input type="number" :name="'splits['+idx+'][credit]'" x-model.number="row.credit"
                                               step="0.01" min="0" placeholder="0"
                                               @input="if(row.credit > 0) row.debit = ''"
                                               class="w-full border border-gray-300 rounded-lg px-2 py-1.5 text-xs text-right font-mono focus:outline-none focus:ring-2 focus:ring-amber-500" />
                                    </td>
                                    <td class="px-3 py-2 text-center">
                                        <button type="button" @click="removeRow(idx)" x-show="rows.length > 2"
                                                class="text-rose-400 hover:text-rose-600 p-0.5 rounded transition" title="Hapus baris">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold text-xs">
                                <td colspan="3" class="px-3 py-3 text-right uppercase text-gray-600">Total</td>
                                <td class="px-3 py-3 text-right font-mono text-blue-700" x-text="'Rp ' + formatNumber(totalDebit())"></td>
                                <td class="px-3 py-3 text-right font-mono text-amber-700" x-text="'Rp ' + formatNumber(totalCredit())"></td>
                                <td></td>
                            </tr>
                            <tr x-show="Math.abs(totalDebit() - totalCredit()) > 0.01" class="bg-red-50">
                                <td colspan="6" class="px-3 py-2 text-center text-xs text-red-600 font-semibold">
                                    ⚠ Jurnal tidak seimbang! Selisih: <span x-text="'Rp ' + formatNumber(Math.abs(totalDebit() - totalCredit()))"></span>
                                </td>
                            </tr>
                            <tr x-show="totalDebit() > 0 && Math.abs(totalDebit() - totalCredit()) < 0.01" class="bg-green-50">
                                <td colspan="6" class="px-3 py-2 text-center text-xs text-green-700 font-semibold">
                                    ✓ Jurnal seimbang (balanced).
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            @error('splits')
                <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('dashboard.gnucash.transactions.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2.5 px-5 rounded-xl transition">
                    Batal
                </a>
                <button type="submit"
                        :disabled="Math.abs(totalDebit() - totalCredit()) > 0.01 || totalDebit() === 0"
                        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-semibold py-2.5 px-6 rounded-xl transition shadow">
                    {{ $transaction->id ? 'Simpan Perubahan' : 'Simpan Transaksi' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
