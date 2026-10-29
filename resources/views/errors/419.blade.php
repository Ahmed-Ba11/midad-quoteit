@extends('layouts.app')

@section('title', 'انتهت صلاحية الصفحة')

@section('content')
    <div class="mx-auto max-w-xl px-4 py-20">
        <x-empty-state title="انتهت صلاحية الصفحة" icon="feather">
            <p>مرّ وقت طويل على فتح الصفحة. حدّثها ثم أعد المحاولة.</p>
            <p class="mt-1 text-xs">(419)</p>
            <x-button :href="route('home')" class="mt-6">العودة إلى الرئيسية</x-button>
        </x-empty-state>
    </div>
@endsection
