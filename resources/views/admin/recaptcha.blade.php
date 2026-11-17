@extends('admin.layout')
@section('title', 'reCAPTCHA')
@section('heading', 'reCAPTCHA keys')

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="rounded-xl bg-white p-5 shadow">
        @if($testKeys)
            <p class="rounded-lg border border-brand-red bg-red-50 p-3 font-bold text-brand-red-dark" role="alert">The forms are using Google's public TEST keys. Every check passes, so spam is NOT blocked. Enter your real keys below before running ads.</p>
        @elseif($source)
            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-800"><span class="size-2 rounded-full bg-green-600"></span> Live keys in use ({{ $source === 'admin' ? 'saved here' : 'from the server .env file' }})</span>
        @else
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900"><span class="size-2 rounded-full bg-amber-600"></span> No keys set: enquiries cannot be submitted</span>
        @endif
        <p class="mt-3 text-sm text-ink-soft">If Google cannot be reached, the check fails closed: the enquiry is rejected, never let through.</p>
    </div>

    @if(session('recaptcha_error'))
        <p class="rounded-lg border border-brand-red bg-red-50 p-3 font-bold text-brand-red-dark" role="alert">{{ session('recaptcha_error') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.recaptcha.update') }}" class="grid gap-4 rounded-xl bg-white p-5 shadow sm:grid-cols-2" autocomplete="off">
        @csrf @method('PUT')
        <h2 class="text-xl sm:col-span-2">Your keys</h2>
        <x-admin.field name="recaptcha_site_key" label="Site key" :value="$siteKey" placeholder="6Lc..." help="The public key. Shown on the website."/>
        <div>
            <x-admin.field name="recaptcha_secret_key" label="Secret key" type="password" :placeholder="$secretHint ?: '6Lc...'"
                help="{{ $source === 'admin' && $secretHint ? 'A secret is saved ('.$secretHint.'). Leave blank to keep it, or paste a new one to replace it.' : 'Stored encrypted and never shown again.' }}"/>
            @if($source === 'admin' && $secretHint)
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_secret" value="1" class="size-4 accent-brand-red"> Remove the saved secret</label>
            @endif
        </div>
        <div class="sm:col-span-2"><button class="btn btn-primary">Save keys</button></div>
        <p class="text-sm text-ink-soft sm:col-span-2">Leave both empty to use the keys in the server <code>.env</code> file (<code>RECAPTCHA_SITE_KEY</code> / <code>RECAPTCHA_SECRET_KEY</code>).</p>
    </form>

    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="text-xl">Check the secret key</h2>
        <form method="POST" action="{{ route('admin.recaptcha.test') }}" class="mt-3 space-y-2">
            @csrf
            <button class="btn btn-outline btn-sm">Test secret key</button>
            <p class="text-sm text-ink-soft">Asks Google whether the saved secret is valid. It cannot check the site key, so after saving, open the website and tick the box once to confirm the widget shows and an enquiry sends.</p>
        </form>
    </div>

    <details class="rounded-xl bg-white p-5 shadow">
        <summary class="cursor-pointer font-bold">How to get the keys</summary>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-ink-soft">
            <li>Open <b>google.com/recaptcha/admin</b> and sign in with your Google account.</li>
            <li>Create a site. Choose <b>reCAPTCHA v2</b> and the <b>"I'm not a robot" checkbox</b> type.</li>
            <li>Under Domains add <b>{{ $domain }}</b> (and <b>www.{{ $domain }}</b> if you use it).</li>
            <li>Copy the <b>Site key</b> and the <b>Secret key</b> into the boxes above and save.</li>
            <li>Press <b>Test secret key</b>, then open the website and send a test enquiry.</li>
        </ol>
    </details>
</div>
@endsection
