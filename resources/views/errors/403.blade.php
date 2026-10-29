@extends('layouts.app')

@section('title', 'ليس لك أن تفعل هذا')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20">
        <x-empty-state title="ليس لك أن تفعل هذا" icon="feather">
            <p>هذا الإجراء متاح لصاحب الاقتباس فقط.</p>
            <p class="mt-1 text-xs">(403)</p>
            <x-button :href="route('home')" class="mt-6">العودة إلى الرئيسية</x-button>
        </x-empty-state>
    </div>
@endsection
