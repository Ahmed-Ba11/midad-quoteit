@extends('layouts.app')

@section('title', 'حدث خطأ غير متوقع')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20">
        <x-empty-state title="حدث خطأ غير متوقع" icon="feather">
            <p>حاول مرة أخرى بعد قليل.</p>
            <p class="mt-1 text-xs">(500)</p>
            <x-button :href="route('home')" class="mt-6">العودة إلى الرئيسية</x-button>
        </x-empty-state>
    </div>
@endsection
