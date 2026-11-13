<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login | {{ $site['brand_name'] }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-brand-navy grid place-items-center p-4">
    <main class="w-full max-w-sm bg-white rounded-2xl shadow-card p-7 border-t-8 border-brand-gold">
        <h1 class="text-2xl">{{ $site['brand_name'] }} admin</h1>
        <p class="text-ink-soft mt-1">Sign in to manage the website.</p>
        <form method="POST" action="{{ route('admin.login.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="field-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field-input">
            </div>
            <div>
                <label for="password" class="field-label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="field-input">
            </div>
            @error('email')<p class="field-error" role="alert">{{ $message }}</p>@enderror
            @if(session('status'))<p class="text-green-800 font-bold">{{ session('status') }}</p>@endif
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="accent-brand-red"> Keep me signed in</label>
            <button class="btn btn-primary w-full" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
