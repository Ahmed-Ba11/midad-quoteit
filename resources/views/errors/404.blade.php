@extends('layouts.app')

@section('title', 'الصفحة غير موجودة')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20">
        <x-empty-state title="الصفحة غير موجودة" icon="feather">
            <p>ربما حُذف الاقتباس أو تغيّر الرابط.</p>
            <p class="mt-1 text-xs">(404)</p>
            <x-button :href="route('home')" class="mt-6">العودة إلى الرئيسية</x-button>
        </x-empty-state>
    </div>
@endsection
