@extends('base.layout')
@section('title', $action)
@section('content')
    <div class="flex flex-col bg-white rounded-lg shadow-md p-6">
        <div class="font-semibold mb-3 text-xl">{{ $action }}</div>
        @isset($items)
            <form method="POST" action="{{ route('dashboard.master.mapel.update', ['mapel'=>$items->id]) }}" class="grid grid-cols-1">
                @method('PUT')
            @else
                <form method="POST" action="{{ route('dashboard.master.mapel.store') }}" class="grid grid-cols-1">
                @endisset
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-semibold mb-2">Nama</label>
                    <div class="relative">
                        <input type="text" name="name" value="{{ old('name', $items->name ?? '') }}"
                            class="border border-gray-300  ring-0 rounded-xl px-3 py-2 w-full md:w-1/2 focus:outline-[#177245]">
                    </div>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p>
                    @enderror
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