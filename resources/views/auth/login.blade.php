@extends('layouts.auth')

@section('title', 'تسجيل الدخول')
@section('heading', 'أهلاً بعودتك')
@section('subheading', 'سجّل دخولك لتشارك اقتباساتك وتديرها.')

@section('form')
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <x-input name="email" type="email" label="البريد الإلكتروني" hint="بريد صحيح، مثل name@example.com"
            dir="ltr" required autocomplete="email" />

        <x-input name="password" type="password" label="كلمة المرور" hint="٨ أحرف على الأقل" dir="ltr"
            required minlength="8" autocomplete="current-password" />

        <x-button type="submit" class="h-12 w-full">دخول</x-button>

        <p class="text-center text-sm text-muted">
            ليس لديك حساب؟
            <a href="{{ route('signup') }}" class="font-medium text-accent hover:underline">أنشئ حساباً</a>
        </p>
    </form>
@endsection

@section('verse')
    <x-verse class="text-3xl" sadr="الخيلُ والليلُ والبيداءُ تعرفني" ajuz="والسيفُ والرمحُ والقرطاسُ والقلمُ"
        poet="المتنبي" />
@endsection
