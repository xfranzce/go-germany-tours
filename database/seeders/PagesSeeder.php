<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $banner = '<p><strong>[CONFIRM: this is a starter template only. Have it reviewed by a qualified lawyer before launch.]</strong></p>';

        $pages = [
            'home' => ['title' => 'Home', 'content' => null, 'data' => [],
                'seo_title' => 'Go Germany Tours | Self Drive, Chauffeur and Coach Tours of Germany',
                'meta_description' => 'Self drive, chauffeur driven and coach tours of Quedlinburg, Saxon Switzerland, Cochem, Hiddensee and Gengenbach. Get a free quote in 30 seconds.'],
            'about' => ['title' => 'About Go Germany Tours', 'content' => '<p>Go Germany Tours plans Germany vacations for travelers from the United States, built around the fairytale towns, river valleys and island escapes that most itineraries skip.</p>'
                .'<p>Choose how you want to travel: drive yourself, relax with a chauffeur, or join a coach tour. Tell us what you have in mind and we will send a tailored quote.</p>'
                .'<p>[CONFIRM: company story, years in business, team, accreditation and licence details]</p>',
                'seo_title' => 'About Go Germany Tours', 'meta_description' => 'Learn about Go Germany Tours and how we plan self drive, chauffeur driven and coach vacations in Germany.'],
            'contact' => ['title' => 'Contact Us', 'content' => '<p>Tell us about your trip and we will come back to you with a personalised quote. Fill in the form and a member of the team will be in touch.</p>',
                'seo_title' => 'Contact Go Germany Tours', 'meta_description' => 'Contact Go Germany Tours for a free, no-obligation quote on your Germany vacation.'],
            'terms' => ['title' => 'Terms and Conditions', 'content' => $banner.<<<'HTML'
<p>These Terms and Conditions apply to your use of this website and to enquiries made through it. They are provided by {company}.</p>
<h2>1. Enquiries and quotes</h2>
<p>Submitting an enquiry does not create a booking. A booking is only confirmed when {company} confirms it in writing and any required deposit is received. Quotes are subject to availability and may change until confirmed.</p>
<h2>2. Prices</h2>
<p>Prices shown on this website are guides ("from" prices) and are not an offer. [CONFIRM: pricing basis, currency, deposit and payment terms]</p>
<h2>3. Cancellations and changes</h2>
<p>[CONFIRM: cancellation, amendment and refund policy]</p>
<h2>4. SMS and contact consent</h2>
<p>If you give us your phone number and tick the consent box, you agree to receive SMS messages from {company} about your enquiry, booking updates, itinerary updates, reminders and customer support. Message and data rates may apply. Reply STOP to unsubscribe or HELP for assistance. Consent is not a condition of purchase.</p>
<h2>5. Liability</h2>
<p>[CONFIRM: limitation of liability and governing law]</p>
<h2>6. Contact</h2>
<p>Questions about these terms can be sent to us through the contact page.</p>
HTML, 'seo_title' => 'Terms and Conditions', 'meta_description' => 'Terms and Conditions for enquiries and bookings with Go Germany Tours.'],
            'privacy' => ['title' => 'Privacy Policy', 'content' => $banner.<<<'HTML'
<p>This Privacy Policy explains how {company} collects and uses personal information when you use this website or send an enquiry.</p>
<h2>1. Information we collect</h2>
<p>Name, email address, phone number, travel preferences and any message you send. We also record technical and marketing information such as your IP address, browser, the page you were on, and campaign details (for example UTM parameters or Google click identifiers) so we can understand how enquiries reach us.</p>
<h2>2. How we use it</h2>
<p>To respond to your enquiry, prepare quotes, contact you about your trip (including by SMS where you have consented), improve our marketing, and meet legal obligations.</p>
<h2>3. Sharing</h2>
<p>We use service providers such as email, customer-relationship, analytics and advertising platforms. If you ask for a second quote from a partner, we may share your enquiry details with them. [CONFIRM: list of processors and partners]</p>
<h2>4. Retention and your rights</h2>
<p>[CONFIRM: retention periods and the rights available to you, including access, correction and deletion]</p>
<h2>5. Cookies</h2>
<p>We use cookies to keep forms secure and to remember the campaign that brought you here for up to 30 days. [CONFIRM: cookie and consent banner requirements for target markets]</p>
<h2>6. Contact</h2>
<p>Contact {company} through the contact page about any privacy question.</p>
HTML, 'seo_title' => 'Privacy Policy', 'meta_description' => 'How Go Germany Tours collects, uses and protects your personal information.'],
            'thank-you' => ['title' => 'Thank you', 'content' => null, 'data' => [], 'seo_title' => 'Thank you for your enquiry', 'meta_description' => '', 'noindex' => true],
        ];

        foreach ($pages as $key => $p) {
            Page::updateOrCreate(['key' => $key], $p);
        }
    }
}
