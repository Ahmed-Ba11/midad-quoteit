@extends('layouts.app')

@section('title', 'طلبات كثيرة')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20">
        <x-empty-state title="طلبات كثيرة" icon="feather">
            <p>انتظر دقيقة ثم أعد المحاولة.</p>
            <p class="mt-1 text-xs">(429)</p>
            <x-button :href="route('home')" class="mt-6">العودة إلى الرئيسية</x-button>
        </x-empty-state>
    </div>
@endsection
