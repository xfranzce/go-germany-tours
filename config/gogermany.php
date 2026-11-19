<?php

/*
|--------------------------------------------------------------------------
| Go Germany Tours: brand-level configuration
|--------------------------------------------------------------------------
| Anything an editor should change lives in the `settings` table (Admin >
| Site settings). The values below are the defaults used until it is set.
| Values written as [CONFIRM: ...] are placeholders listed in
| docs/reference/CONTENT_TO_CONFIRM.md. Never invent business facts here.
*/

return [

    // Bump when the consent wording or Terms/Privacy change materially.
    'terms_version' => '2026-11-draft-1',

    // Stored with every enquiry as evidence. {company} is replaced by the
    // legal company name. DO NOT edit the wording without legal sign-off.
    'consent_text' => 'I agree to the Terms and Conditions and Privacy Policy provided by {company}. '
        .'By providing my phone number, I consent to receive SMS messages from {company} regarding my enquiry, '
        .'booking updates, itinerary updates, reminders, and customer support. Message and data rates may apply. '
        .'Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.',

    // First-touch tracking cookie lifetime in days.
    'tracking_days' => 30,
    'tracking_keys' => ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid', 'utm_device', 'utm_adcreative', 'utm_adposition'],

    // Enquiries allowed per IP per minute / per hour.
    'rate_limit_per_minute' => 3,
    'rate_limit_per_hour' => 10,

    'form_names' => [
        'hero_quiz' => 'Hero quiz',
        'header_modal' => 'Header modal',
        'package_quote' => 'Package quote',
        'package_question' => 'Ask a question',
        'contact' => 'Contact page',
    ],

    'statuses' => ['new' => 'New', 'contacted' => 'Contacted', 'quoted' => 'Quoted', 'won' => 'Won', 'lost' => 'Lost'],

    // Default site settings (override in admin).
    'defaults' => [
        'brand_name' => 'Go Germany Tours',
        'company_name' => 'Go German Tours',
        'tagline' => 'Fairytale towns, castles and river valleys, planned for you',
        'phone' => '',
        'phone_display' => '',
        'email' => '',
        'address' => '',
        'facebook_url' => '',
        'instagram_url' => '',
        'youtube_url' => '',
        'notification_emails' => '',
        'gtm_id' => '',
        'recaptcha_site_key' => '',
        'recaptcha_secret_key' => '',
        'modal_discount_percent' => '',
        'footer_text' => 'Tailor-made Germany vacations for US travelers. Choose a self-drive, chauffeur-driven or small-group coach tour of fairytale towns, river valleys and the Baltic coast, planned around your dates, group and budget. Get a free, no-obligation quote in under a minute.',
        'default_meta_title' => 'Go Germany Tours | Self Drive, Chauffeur and Coach Tours of Germany',
        'default_meta_description' => 'Self drive, chauffeur driven and coach tours of Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach. Get a free quote in 30 seconds.',
        'default_og_image' => '',
        'logo' => '',
        'favicon' => '',
    ],
];
