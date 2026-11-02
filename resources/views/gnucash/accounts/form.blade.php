@extends('base.layout')
@section('title', $title)
@section('content')
<div class="max-w-3xl mx-auto" x-data="accountForm()" x-init="init()">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <div class="flex items-center justify-between pb-4 mb-6 border-b border-gray-100">
            <div>
                <h1 class="text-xl font-bold text-gray-800">{{ $action }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola informasi rekening dan penempatan hierarki bagan akun.</p>
            </div>
            <a href="{{ route('dashboard.gnucash.accounts.index') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2 px-3 rounded-xl transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Kembali
            </a>
        </div>

        <form action="{{ $account->id ? route('dashboard.gnucash.accounts.update', $account->id) : route('dashboard.gnucash.accounts.store') }}"
              method="POST" class="space-y-5">
            @csrf
            @if($account->id)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Kode Akun -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Kode Akun</label>
                    <input type="text" name="code" value="{{ old('code', $account->code) }}" placeholder="Contoh: 1110, 4100"
                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500 @error('code') border-red-500 @enderror" />
                    @error('code')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipe Akun -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">
                        Tipe Akun <span class="text-red-500">*</span>
                    </label>
                    <select name="account_type" required x-model="selectedType" @change="onTypeChange()"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500 @error('account_type') border-red-500 @enderror">
                        <option value="">-- Pilih Tipe Akun --</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                    @error('account_type')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <span class="text-[11px] text-gray-400 mt-1 block">
                        Pilihan akun induk di bawah akan disesuaikan otomatis dengan tipe ini.
                    </span>
                </div>
            </div>

            <!-- Nama Akun -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Nama Akun <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required placeholder="Contoh: Bank BSI Operasional, Kas Pondok, Biaya Listrik"
                       class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror" />
                @error('name')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Akun Induk (Parent) - Searchable Select with Type Filtering -->
            <div class="relative">
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1 flex items-center justify-between">
                    <span>Akun Induk (Parent)</span>
                    <span class="text-[10px] font-normal text-green-700 font-sans" x-show="selectedType">
                        Filter aktif: <span class="font-semibold" x-text="selectedType"></span> (<span x-text="filteredParents.length"></span> opsi)
                    </span>
                </label>

                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="parent_id" :value="selectedParentId">

                <!-- Trigger Input Button -->
                <div @click="toggleDropdown()"
                     class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs bg-white cursor-pointer flex items-center justify-between hover:border-green-500 transition shadow-sm @error('parent_id') border-red-500 @enderror">
                    <div class="flex items-center gap-2 truncate">
                        <template x-if="!selectedParentId">
                            <span class="text-gray-400 italic">-- Tanpa Induk (Top Level) --</span>
                        </template>
                        <template x-if="selectedParentId">
                            <span class="text-gray-900 font-medium truncate" x-text="selectedParentName"></span>
                        </template>
                    </div>
                    <div class="flex items-center gap-1.5 ml-2">
                        <template x-if="selectedParentId">
                            <button type="button" @click.stop="selectParent(null)" class="text-gray-400 hover:text-red-500 p-0.5 rounded" title="Hapus pilihan parent">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </template>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>

                <!-- Dropdown Search Panel -->
                <div x-show="isOpen" @click.outside="isOpen = false" x-transition
                     class="absolute z-50 left-0 right-0 mt-1.5 bg-white border border-gray-200 rounded-2xl shadow-xl overflow-hidden text-xs max-h-80 flex flex-col">
                    
                    <!-- Search Input Box -->
                    <div class="p-2.5 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                            <circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" x-model="searchQuery" x-ref="searchInput" placeholder="Ketik untuk mencari akun induk..."
                               class="w-full bg-transparent border-0 text-xs focus:outline-none text-gray-800 placeholder-gray-400" />
                        <template x-if="searchQuery">
                            <button type="button" @click="searchQuery = ''" class="text-gray-400 hover:text-gray-600 text-xs">
                                &times;
                            </button>
                        </template>
                    </div>

                    <!-- Scrollable List -->
                    <div class="overflow-y-auto flex-1 divide-y divide-gray-50">
                        <!-- Option: Tanpa Induk -->
                        <div @click="selectParent(null)"
                             class="px-3.5 py-2.5 hover:bg-green-50 cursor-pointer flex items-center justify-between text-gray-600"
                             :class="{ 'bg-green-50/80 font-bold text-green-800': !selectedParentId }">
                            <span class="italic">-- Tanpa Induk (Jadikan Akun Tingkat Atas) --</span>
                            <template x-if="!selectedParentId">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-600">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </template>
                        </div>

                        <!-- Filtered Parents List -->
                        <template x-for="p in filteredParents" :key="p.id">
                            <div @click="selectParent(p)"
                                 class="px-3.5 py-2 hover:bg-green-50 cursor-pointer flex items-center justify-between transition-colors"
                                 :class="{ 'bg-green-50/80 font-bold text-green-900': selectedParentId === p.id }">
                                <div class="flex flex-col gap-0.5 truncate pr-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-gray-900 font-medium" x-text="p.name"></span>
                                        <template x-if="p.code">
                                            <span class="font-mono text-[10px] bg-gray-100 text-gray-600 px-1 rounded" x-text="p.code"></span>
                                        </template>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold"
                                              :class="{
                                                  'bg-blue-100 text-blue-800': ['ASSET', 'BANK', 'CASH'].includes(p.account_type),
                                                  'bg-amber-100 text-amber-800': ['LIABILITY', 'CREDIT'].includes(p.account_type),
                                                  'bg-purple-100 text-purple-800': p.account_type === 'EQUITY',
                                                  'bg-green-100 text-green-800': p.account_type === 'INCOME',
                                                  'bg-rose-100 text-rose-800': p.account_type === 'EXPENSE',
                                              }"
                                              x-text="p.account_type"></span>
                                    </div>
                                    <span class="text-[10px] text-gray-400 font-mono truncate" x-text="p.full_path"></span>
                                </div>
                                <template x-if="selectedParentId === p.id">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-green-600 flex-shrink-0">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </template>
                            </div>
                        </template>

                        <!-- Empty Search Result -->
                        <template x-if="filteredParents.length === 0">
                            <div class="px-4 py-8 text-center text-gray-400 italic">
                                Tidak ada akun induk yang sesuai dengan kriteria pencarian atau tipe akun yang dipilih.
                            </div>
                        </template>
                    </div>

                    <!-- Footer Info -->
                    <div class="px-3.5 py-1.5 bg-gray-50 border-t border-gray-100 text-[10px] text-gray-500 flex items-center justify-between">
                        <span>Tekan untuk memilih akun induk</span>
                        <span x-text="filteredParents.length + ' akun tersedia'"></span>
                    </div>
                </div>

                @error('parent_id')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Deskripsi -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-1">Deskripsi / Keterangan</label>
                <textarea name="description" rows="3" placeholder="Catatan tambahan mengenai akun ini..."
                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-green-500">{{ old('description', $account->description) }}</textarea>
            </div>

            <!-- Placeholder Checkbox -->
            <div class="flex items-start gap-3 p-3.5 bg-gray-50/70 rounded-2xl border border-gray-200">
                <input type="checkbox" id="placeholder" name="placeholder" value="1" {{ old('placeholder', $account->placeholder) ? 'checked' : '' }}
                       class="mt-0.5 w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500" />
                <label for="placeholder" class="text-xs text-gray-700 cursor-pointer">
                    <span class="font-semibold block text-gray-900">Placeholder Account</span>
                    Tandai jika akun ini hanya sebagai header / pengelompokan sub-akun (tidak menerima mutasi transaksi langsung).
                </label>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('dashboard.gnucash.accounts.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold py-2.5 px-5 rounded-xl transition">
                    Batal
                </a>
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2.5 px-6 rounded-xl transition shadow-sm">
                    {{ $account->id ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function accountForm() {
    return {
        selectedType: '{{ old('account_type', $account->account_type ?? 'ASSET') }}',
        selectedParentId: '{{ old('parent_id', $selectedParentId ?? $account->parent_id ?? '') }}',
        selectedParentName: '',
        searchQuery: '',
        isOpen: false,
        allParents: @json($parentOptions ?? []),

        init() {
            if (this.selectedParentId) {
                const p = this.allParents.find(x => String(x.id) === String(this.selectedParentId));
                if (p) {
                    this.selectedParentName = (p.code ? '[' + p.code + '] ' : '') + p.name + ' (' + p.account_type + ')';
                }
            }
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            }
        },

        get filteredParents() {
            let list = this.allParents || [];
            const t = (this.selectedType || '').toUpperCase().trim();

            // 1. Filter by compatible account_type
            if (t) {
                if (['ASSET', 'BANK', 'CASH'].includes(t)) {
                    list = list.filter(p => ['ASSET', 'BANK', 'CASH'].includes((p.account_type || '').toUpperCase()));
                } else if (['LIABILITY', 'CREDIT', 'PAYABLE'].includes(t)) {
                    list = list.filter(p => ['LIABILITY', 'CREDIT', 'PAYABLE'].includes((p.account_type || '').toUpperCase()));
                } else if (t === 'INCOME') {
                    list = list.filter(p => (p.account_type || '').toUpperCase() === 'INCOME');
                } else if (t === 'EXPENSE') {
                    list = list.filter(p => (p.account_type || '').toUpperCase() === 'EXPENSE');
                } else if (t === 'EQUITY') {
                    list = list.filter(p => (p.account_type || '').toUpperCase() === 'EQUITY');
                } else {
                    list = list.filter(p => (p.account_type || '').toUpperCase() === t);
                }
            }

            // 2. Filter by search query (name, code, or path)
            if (this.searchQuery && this.searchQuery.trim()) {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(p =>
                    (p.name || '').toLowerCase().includes(q) ||
                    (p.code || '').toLowerCase().includes(q) ||
                    (p.full_path || '').toLowerCase().includes(q)
                );
            }

            return list;
        },

        selectParent(p) {
            if (!p) {
                this.selectedParentId = '';
                this.selectedParentName = '';
            } else {
                this.selectedParentId = p.id;
                this.selectedParentName = (p.code ? '[' + p.code + '] ' : '') + p.name + ' (' + p.account_type + ')';
            }
            this.isOpen = false;
            this.searchQuery = '';
        },

        onTypeChange() {
            // When type changes, verify if currently selected parent is still compatible
            if (this.selectedParentId) {
                const stillValid = this.filteredParents.some(p => String(p.id) === String(this.selectedParentId));
                if (!stillValid) {
                    this.selectedParentId = '';
                    this.selectedParentName = '';
                }
            }
        }
    };
}
</script>
@endsection
