@extends('layouts.auth')

@section('title', 'حساب جديد')
@section('heading', 'انضم إلى رقيم')
@section('subheading', 'أنشئ حسابك وشارك أول اقتباس في أقل من دقيقة.')

@section('form')
    <form method="POST" action="{{ route('signup') }}" class="space-y-5">
        @csrf

        <x-input name="name" label="الاسم" hint="من ٢ إلى ٥٠ حرفاً، حروف عربية أو إنجليزية فقط" required
            minlength="2" maxlength="50" pattern="[A-Za-zء-غف-ْٱ-ۓ ]+" autocomplete="name" />

        <x-input name="email" type="email" label="البريد الإلكتروني" hint="بريد صحيح، مثل name@example.com"
            dir="ltr" required autocomplete="email" />

        <x-input name="password" type="password" label="كلمة المرور" hint="٨ أحرف على الأقل" dir="ltr"
            required minlength="8" autocomplete="new-password" />

        <x-button type="submit" class="h-12 w-full">إنشاء الحساب</x-button>

        <p class="text-center text-sm text-muted">
            لديك حساب؟
            <a href="{{ route('login') }}" class="font-medium text-accent hover:underline">سجّل دخولك</a>
        </p>
    </form>
@endsection

@section('verse')
    <x-epigraph context="beginning" layout="verse" class="text-3xl" />
@endsection
