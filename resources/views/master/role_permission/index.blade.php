@extends('base.layout')
@section('title', $title)

@section('content')
<div class="flex flex-col bg-white rounded-lg shadow-md p-6" x-data="rolePermissionManager()">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 mb-4 border-b border-gray-200 gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Manajemen Hak Akses & Menu</h1>
            <p class="text-xs text-gray-500 mt-1">
                Atur menu dan hak akses per-role akun. Centang untuk mengizinkan akses ke menu/fitur terkait.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span x-show="isSaving" class="text-xs text-green-700 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full font-medium flex items-center gap-1.5 animate-pulse" style="display: none;">
                <svg class="animate-spin h-3.5 w-3.5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                Menyimpan...
            </span>
            <span x-show="saveSuccess" class="text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full font-medium" style="display: none;">
                ✓ Tersimpan
            </span>
        </div>
    </div>

    <!-- Filter Tab Role (Pilihan Role Aktif) -->
    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-100 pb-3">
        <span class="text-xs font-semibold text-gray-400 self-center mr-1">Pilih Role:</span>
        @foreach($roles as $role)
            <button type="button"
                @click="selectedRole = {{ $role->id }}"
                :class="selectedRole === {{ $role->id }} ? 'bg-green-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition-all">
                {{ $role->display_name }}
                <span class="ml-1 opacity-75 text-[10px]">({{ $role->users()->count() }})</span>
            </button>
        @endforeach
    </div>

    <!-- Matrix View Table -->
    <div class="overflow-x-auto border border-gray-200 rounded-xl">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th scope="col" class="px-5 py-3.5 text-left font-semibold text-xs tracking-wider uppercase w-1/2">
                        Menu / Fitur
                    </th>
                    <th scope="col" class="px-5 py-3.5 text-center font-semibold text-xs tracking-wider uppercase">
                        Kunci Akses
                    </th>
                    <th scope="col" class="px-5 py-3.5 text-center font-semibold text-xs tracking-wider uppercase w-32">
                        Akses
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($permissions as $group => $items)
                    <!-- Group Header Row -->
                    <tr class="bg-emerald-50/60 font-semibold text-emerald-950">
                        <td colspan="2" class="px-5 py-2.5 text-xs">
                            <span class="inline-flex items-center gap-1.5 font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-700"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                                {{ $group ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5 text-center text-xs">
                            <button type="button" 
                                @click="toggleGroup({{ json_encode($items->pluck('id')) }})"
                                class="text-[11px] text-emerald-700 hover:text-emerald-900 font-medium underline">
                                Toggle Grup
                            </button>
                        </td>
                    </tr>

                    <!-- Item Rows -->
                    @foreach($items as $perm)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3 text-gray-800 font-medium">
                                <span class="ml-3 text-xs md:text-sm">{{ $perm->display_name }}</span>
                            </td>
                            <td class="px-5 py-3 text-center text-xs text-gray-400 font-mono">
                                {{ $perm->name }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                        class="sr-only peer"
                                        :checked="hasPermission(selectedRole, {{ $perm->id }})"
                                        @change="togglePermission(selectedRole, {{ $perm->id }}, $event.target.checked)">
                                    <div class="relative w-9 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 p-3 bg-gray-50 rounded-lg text-xs text-gray-500 flex items-center justify-between">
        <span>💡 <b>Tips:</b> Perubahan permission otomatis tersimpan dan segera diterapkan ke semua akun yang memakai role terkait.</span>
    </div>
</div>

<script>
function rolePermissionManager() {
    return {
        selectedRole: {{ $roles->first()->id ?? 1 }},
        isSaving: false,
        saveSuccess: false,
        rolePermissions: {
            @foreach($roles as $role)
                {{ $role->id }}: [{{ $role->permissions->pluck('id')->implode(',') }}],
            @endforeach
        },

        hasPermission(roleId, permId) {
            return this.rolePermissions[roleId] && this.rolePermissions[roleId].includes(permId);
        },

        togglePermission(roleId, permId, allowed) {
            if (!this.rolePermissions[roleId]) {
                this.rolePermissions[roleId] = [];
            }

            if (allowed) {
                if (!this.rolePermissions[roleId].includes(permId)) {
                    this.rolePermissions[roleId].push(permId);
                }
            } else {
                this.rolePermissions[roleId] = this.rolePermissions[roleId].filter(id => id !== permId);
            }

            this.sendToggle(roleId, permId, allowed);
        },

        toggleGroup(permIds) {
            const roleId = this.selectedRole;
            const allChecked = permIds.every(id => this.hasPermission(roleId, id));
            const targetState = !allChecked;

            permIds.forEach(id => {
                this.togglePermission(roleId, id, targetState);
            });
        },

        sendToggle(roleId, permId, allowed) {
            this.isSaving = true;
            this.saveSuccess = false;

            fetch("{{ route('dashboard.master.role-permission.toggle') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    role_id: roleId,
                    permission_id: permId,
                    allowed: allowed ? 1 : 0
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                this.saveSuccess = true;
                setTimeout(() => {
                    this.saveSuccess = false;
                }, 1800);
            })
            .catch(err => {
                this.isSaving = false;
                alert("Gagal memperbarui permission.");
            });
        }
    }
}
</script>
@endsection
