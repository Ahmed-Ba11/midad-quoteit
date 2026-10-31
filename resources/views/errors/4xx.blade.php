@extends('layouts.app')

@section('title', 'تعذّر فتح الصفحة')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20">
        <x-empty-state title="تعذّر فتح الصفحة" icon="feather">
            <p>لا يمكن فتح هذا الرابط بهذه الطريقة.</p>
            <p class="mt-1 text-xs">({{ $exception->getStatusCode() }})</p>
            <x-button :href="route('home')" class="mt-6">العودة إلى الرئيسية</x-button>
        </x-empty-state>
    </div>
@endsection
