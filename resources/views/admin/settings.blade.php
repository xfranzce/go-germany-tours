@extends('admin.layout')
@section('title', 'Site settings')
@section('heading', 'Site settings')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="space-y-6 max-w-5xl">
    @csrf @method('PUT')

    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">Brand and legal</h2>
        <x-admin.field name="brand_name" label="Brand name" :value="$site['brand_name']" required/>
        <x-admin.field name="company_name" label="Legal company name" :value="$site['company_name']" required help="Used in the consent text next to every form, in the footer and in the legal pages. Must be the real legal entity."/>
        <x-admin.field name="tagline" label="Tagline" :value="$site['tagline']" class="sm:col-span-2"/>
        <x-admin.field name="terms_version" label="Terms version label" :value="\App\Models\Setting::get('terms_version', config('gogermany.terms_version'))" help="Stored with every enquiry. Change it whenever the Terms, Privacy Policy or consent wording changes."/>
        <x-admin.field name="modal_discount_percent" label="Modal offer: save up to X%" type="number" :value="$site['modal_discount_percent']" help="Leave blank unless you have a real offer. Blank shows a neutral heading instead."/>
        <x-admin.image name="logo" label="Logo" :current="$site['logo']" :alt-required="false" class="sm:col-span-1"/>
        <x-admin.image name="favicon" label="Favicon (square works best)" :current="$site['favicon']" :alt-required="false"/>
    </section>

    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">Contact details</h2>
        <x-admin.field name="phone" label="Phone (used for tap-to-call)" :value="$site['phone']" help="Leave blank to hide the phone number everywhere."/>
        <x-admin.field name="phone_display" label="Phone as shown on the page" :value="$site['phone_display']" help="e.g. +1 (555) 123-4567"/>
        <x-admin.field name="email" label="Public email" type="email" :value="$site['email']"/>
        <x-admin.field name="address" label="Address" :value="$site['address']"/>
        <x-admin.field name="facebook_url" label="Facebook URL" :value="$site['facebook_url']"/>
        <x-admin.field name="instagram_url" label="Instagram URL" :value="$site['instagram_url']"/>
        <x-admin.field name="youtube_url" label="YouTube URL" :value="$site['youtube_url']"/>
        <x-admin.field name="footer_text" label="Footer text" :value="$site['footer_text']"/>
    </section>

    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">Enquiries, tracking and security</h2>
        <x-admin.field name="notification_emails" label="Send new-enquiry emails to" :value="$site['notification_emails']" class="sm:col-span-2" help="One or more addresses separated by commas. If empty, no staff email is sent."/>
        <x-admin.field name="gtm_id" label="Google Tag Manager ID" :value="$site['gtm_id']" placeholder="GTM-XXXXXXX" help="The GTM snippet is only added to pages when this is filled in."/>
        <div></div>
        <x-admin.field name="recaptcha_site_key" label="reCAPTCHA v2 site key" :value="\App\Models\Setting::get('recaptcha_site_key')" help="Blank uses the key in .env (Google's test key locally)."/>
        <x-admin.field name="recaptcha_secret_key" label="reCAPTCHA v2 secret key" type="password" :help="$hasSecret ? 'A secret key is saved. Type a new one only to replace it.' : 'Blank uses the key in .env.'"/>
        <div class="sm:col-span-2 rounded-lg bg-gray-50 p-3 text-sm">
            <strong>GoHighLevel:</strong> {{ $ghlOn ? 'ON (token and location found in .env)' : 'OFF: add GHL_API_TOKEN and GHL_LOCATION_ID to the .env file, then run php artisan ghl:setup.' }}
            The token is never shown or stored here.
        </div>
    </section>

    <section class="bg-white rounded-xl shadow p-5 grid gap-4 sm:grid-cols-2">
        <h2 class="text-xl sm:col-span-2">Default SEO</h2>
        <x-admin.field name="default_meta_title" label="Default page title (max 70)" :value="$site['default_meta_title']" maxlength="70"/>
        <x-admin.field name="default_meta_description" label="Default meta description (max 320)" :value="$site['default_meta_description']" maxlength="320"/>
        <x-admin.image name="og" label="Default social share image" :current="$site['default_og_image']" :alt-required="true" class="sm:col-span-2"/>
    </section>
    <button class="btn btn-primary">Save settings</button>
</form>
@endsection
