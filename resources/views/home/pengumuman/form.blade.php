@extends('base.layout')
@section('title', 'Dashboard')
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        <form method="POST"
            action="{{ isset($items) ? route('dashboard.pengumuman.update', $items->id) : route('dashboard.pengumuman.store') }}"
            class="flex flex-col" enctype="multipart/form-data">
            @isset($items)
                @method('PUT')
            @endisset
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                <div class="relative">
                    <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                        class="border border-gray-300 ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                </div>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="mb-4" x-data="{ imagePreview: '{{ isset($items) ? asset('storage/' . $items->img) : null }}' }">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Photo</label>
                    <input type="file" name="image" accept="image/*"
                        @change="let file = $event.target.files[0]; imagePreview = URL.createObjectURL(file)"
                        class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0
                   file:text-sm file:font-semibold file:bg-blue-50 file:text-green-700 focus:outline-[#177245] border border-gray-300  ring-0 rounded-2xl
                   hover:file:bg-blue-100 cursor-pointer" />
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="w-100 h-75 object-cover rounded border border-gray-300 my-3" />
                    </template>
                    @error('image')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Deskripsi</label>
                <div x-data="trixEditor()" x-init="init()" class="bg-white rounded-lg shadow-md w-full">
                    <input id="x" type="hidden" value="{{ old('content', $items->des ?? '') }}"name="content" x-ref="input">
                    <trix-editor input="x" x-ref="trix" class="border rounded"
                        style="min-height: 200px;"></trix-editor>
                </div>
            </div>


            <div class="flex items-center">
                <button type="submit"
                    class="cursor-pointer bg-green-500 text-sm hover:bg-green-700 text-white font-bold py-2 px-3 rounded-2xl focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>
@endsection
