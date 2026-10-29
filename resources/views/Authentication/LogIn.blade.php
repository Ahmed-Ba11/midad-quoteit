@extends('Layout.AuthImg')
@extends('Layout.app')

@section('title', 'Log In')
@section('content')
@section('form')

    <div class="flex items-center justify-center p-8">
        <form method="post" class="mx-auto mt-5 w-full max-w-xl rounded-lg p-9">
            @csrf

            <h2 class="mb-2 text-center text-2xl font-bold text-white">Log In</h2>

            <div class="mb-4">
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" class="input peer user-invalid:border-red-500" required
                    pattern="[^@\s]+@[^@\s]+\.[A-Za-z]{2,}">
                <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">بريد صحيح، مثل name@example.com</p>
                    @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror

            </div>

            <div class="mb-4">
                <label for="password" class="label">Password</label>
                <input id="password" name="password" type="password" class="input peer user-invalid:border-red-500" required
                    minlength="8">
                <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">٨ أحرف على الأقل</p>
                    @error('password') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full">Log In</button>
        </form>
    </div>


@endsection
@endsection

