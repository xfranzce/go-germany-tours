@extends('admin.layout')
@section('title', 'Google Sheets')
@section('heading', 'Google Sheets')

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="rounded-xl bg-white p-5 shadow">
        @if($enabled && $hasUrl)
            <span class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-sm font-bold text-green-800"><span class="size-2 rounded-full bg-green-600"></span> On: every enquiry is added to your sheet</span>
        @elseif($hasUrl)
            <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-900"><span class="size-2 rounded-full bg-amber-600"></span> Connected but switched off</span>
        @else
            <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-700"><span class="size-2 rounded-full bg-slate-500"></span> Not connected</span>
        @endif
        <p class="mt-3 text-sm text-ink-soft">Each form submission (hero quiz, pop-up, package pages, contact) adds one row to the first tab of your Google Sheet, including UTM and Google Ads tracking. Spreadsheet problems never stop an enquiry from being saved or emailed.</p>
    </div>

    @if(session('sheets_error'))
        <p class="rounded-lg border border-brand-red bg-red-50 p-3 font-bold text-brand-red-dark" role="alert">{{ session('sheets_error') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.sheets.update') }}" class="grid gap-4 rounded-xl bg-white p-5 shadow" autocomplete="off">
        @csrf @method('PUT')
        <h2 class="text-xl">Connection</h2>
        <div>
            <x-admin.field name="sheets_webhook_url" label="Web app URL" type="password" :placeholder="$urlHint ?: 'https://script.google.com/macros/s/.../exec'"
                help="{{ $hasUrl ? 'A URL is saved ('.$urlHint.'). Leave blank to keep it, or paste a new one to replace it.' : 'From step 6 below. Stored encrypted and never shown again.' }}"/>
            @if($hasUrl)
                <label class="mt-2 flex items-center gap-2 text-sm"><input type="checkbox" name="remove_url" value="1" class="size-4 accent-brand-red"> Remove the saved URL</label>
            @endif
        </div>
        <label class="flex items-center gap-2 font-bold"><input type="checkbox" name="sheets_enabled" value="1" class="size-4 accent-brand-red" @checked($enabled)> Send new enquiries to the sheet</label>
        <div><button class="btn btn-primary">Save</button></div>
    </form>

    <div class="rounded-xl bg-white p-5 shadow">
        <h2 class="text-xl">Check the connection</h2>
        <form method="POST" action="{{ route('admin.sheets.test') }}" class="mt-3 space-y-2">
            @csrf
            <button class="btn btn-outline btn-sm">Add a test row</button>
            <p class="text-sm text-ink-soft">Adds one clearly marked row to the sheet so you can see it work. Delete it afterwards.</p>
        </form>
    </div>

    <div class="rounded-xl bg-white p-5 shadow" x-data="{ copied: false }">
        <h2 class="text-xl">Set it up (about 3 minutes, no Google Cloud needed)</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-ink-soft">
            <li>Sign in to the Google account (your Gmail) that should own the sheet, and create a new blank <b>Google Sheet</b>. Name it, for example, "Go Germany Tours enquiries".</li>
            <li>In the sheet open <b>Extensions &rarr; Apps Script</b>.</li>
            <li>Delete the sample code and paste the script below. Click <b>Save</b>.</li>
            <li>Click <b>Deploy &rarr; New deployment</b>, choose the gear and <b>Web app</b>. Set <b>Execute as: Me</b> and <b>Who has access: Anyone</b>. Click <b>Deploy</b>.</li>
            <li>Approve the permissions when Google asks (<b>Advanced &rarr; Go to project</b>). It only needs access to this sheet.</li>
            <li>Copy the <b>Web app URL</b> (ends in <code>/exec</code>), paste it above, tick <b>Send new enquiries to the sheet</b> and save.</li>
            <li>Press <b>Add a test row</b>. The header row is created automatically.</li>
        </ol>
        <p class="mt-4 text-sm font-bold">Script to paste (contains your private token, keep it to yourself):</p>
        <textarea readonly rows="14" x-ref="code" class="mt-2 w-full rounded-lg border border-slate-300 bg-slate-50 p-3 font-mono text-xs" @focus="$el.select()">{{ $script }}</textarea>
        <button type="button" class="btn btn-outline btn-sm mt-2" @click="navigator.clipboard.writeText($refs.code.value); copied = true; setTimeout(() => copied = false, 2000)" x-text="copied ? 'Copied' : 'Copy script'">Copy script</button>
        <p class="mt-3 text-xs text-ink-soft">If you change the script later, use <b>Deploy &rarr; Manage deployments &rarr; Edit &rarr; New version</b> so the same URL keeps working. Add new columns only to the right of the existing ones.</p>
    </div>
</div>
@endsection
