@extends('base.layout')
@section('title', 'Video')
@section('content')
    <div class="flex flex-col items-center justify-center w-full bg-gray-100 p-4">
       @foreach($items as $val)
       <div class="text-2xl font-semibold">{{$val->name}}</div>
       <img src="{{asset('storage/'.$val->img)}}" class="w-25 h-25 object-cover rounded-2xl shadow my-3">
       <p class="text-justify">{!! $val->des !!}</p>
       @endforeach
    </div>
@endsection
