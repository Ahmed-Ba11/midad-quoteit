@extends('Layout.AuthImg')
@extends('Layout.app')

@section('title', 'Sign Up')
@section('content')

@section('form')

        <div class=" items-center justify-center p-8">
            <form method="post" class="mx-auto  mt-5 max-w-xl rounded-lg  p-9 ">
                @csrf
                <h2 class="mb-4 text-2xl font-bold  text-center text-white">Sign Up</h2>
                <div class="mb-4">
                    <label for="name" class="label">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="input peer user-invalid:border-red-500"
    required minlength="2" maxlength="50" pattern="[A-Za-z\u0600-\u06FF ]+">

                    <p class="mt-1 text-xs text-white peer-user-invalid:text-red-600">من ٢ إلى ٥٠ حرفاً، حروف عربية أو إنجليزية فقط</p>
                    @error('name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
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
              
                <p class="mb-4 text-sm text-white">
                   You Already have an account? <a href="/login" class="text-blue-500 hover:underline">Log In</a>
                </p>

                <button type="submit" class="btn-primary w-full">Sign Up</button>
            </form>
        </div>
        
@endsection
@endsection

