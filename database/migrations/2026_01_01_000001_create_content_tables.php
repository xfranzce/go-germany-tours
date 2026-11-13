<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->string('password');
            $t->rememberToken();
            $t->timestamp('last_login_at')->nullable();
            $t->timestamps();
        });

        // Key/value site settings (brand, contact details, keys, SEO defaults)
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->timestamps();
        });

        // Uploaded images. "path" is the stem; variants are {path}-{width}.webp
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('path')->unique();
            $t->string('original_name')->nullable();
            $t->string('alt')->nullable();
            $t->unsignedInteger('width');
            $t->unsignedInteger('height');
            $t->json('widths');
            $t->unsignedInteger('bytes')->default(0);
            $t->timestamps();
        });

        // Editable pages: home, about, contact, terms, privacy
        Schema::create('pages', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->string('title');
            $t->longText('content')->nullable();
            $t->json('data')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->string('og_image')->nullable();
            $t->boolean('noindex')->default(false);
            $t->timestamps();
        });

        Schema::create('tour_types', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('icon')->nullable();
            $t->string('summary', 500)->nullable();
            $t->longText('description')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->timestamps();
        });

        Schema::create('destinations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('summary', 500)->nullable();
            $t->longText('description')->nullable();
            $t->string('image')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->timestamps();
        });

        Schema::create('packages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('tour_type_id')->constrained()->restrictOnDelete();
            $t->foreignId('destination_id')->constrained()->restrictOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->unsignedSmallInteger('nights');
            $t->decimal('price_from', 10, 2)->nullable();
            $t->string('currency', 3)->default('USD');
            $t->string('summary', 500)->nullable();
            $t->longText('description')->nullable();
            $t->json('highlights')->nullable();
            $t->json('itinerary')->nullable();   // [{title, body}]
            $t->json('inclusions')->nullable();
            $t->json('exclusions')->nullable();
            $t->boolean('is_published')->default(false);
            $t->boolean('is_featured')->default(false);
            $t->boolean('is_sample')->default(false);
            $t->unsignedInteger('sort')->default(0);
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->string('og_image')->nullable();
            $t->boolean('noindex')->default(false);
            $t->timestamps();
        });

        Schema::create('package_images', function (Blueprint $t) {
            $t->id();
            $t->foreignId('package_id')->constrained()->cascadeOnDelete();
            $t->string('path');
            $t->string('alt');
            $t->unsignedInteger('sort')->default(0);
            $t->timestamps();
        });

        // Homepage FAQs have package_id NULL; package FAQs reference a package.
        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('package_id')->nullable()->constrained()->cascadeOnDelete();
            $t->string('question');
            $t->text('answer');
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('location')->nullable();
            $t->text('quote');
            $t->boolean('is_placeholder')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('quiz_steps', function (Blueprint $t) {
            $t->id();
            $t->string('key');                       // answer key e.g. "destination"
            $t->string('question');
            $t->string('type')->default('tiles');    // tiles | dropdown
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('quiz_options', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quiz_step_id')->constrained()->cascadeOnDelete();
            $t->string('label');
            $t->string('icon')->nullable();          // emoji or icon name
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('posts', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('excerpt', 500)->nullable();
            $t->longText('body')->nullable();
            $t->string('image')->nullable();
            $t->boolean('is_published')->default(false);
            $t->timestamp('published_at')->nullable();
            $t->string('seo_title')->nullable();
            $t->string('meta_description', 320)->nullable();
            $t->boolean('noindex')->default(false);
            $t->timestamps();
        });

        Schema::create('enquiries', function (Blueprint $t) {
            $t->id();
            $t->string('form_name');                 // hero_quiz, header_modal, package_quote, package_question, contact
            $t->string('first_name');
            $t->string('last_name');
            $t->string('email');
            $t->string('phone_country', 8);          // e.g. +1
            $t->string('phone_e164', 20);
            $t->string('travel_date')->nullable();
            $t->string('second_quote')->nullable();
            $t->text('special_request')->nullable();
            $t->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $t->string('package_name')->nullable();
            $t->string('time_of_year')->nullable();
            $t->string('group_size')->nullable();
            $t->string('nights')->nullable();
            $t->json('quiz_answers')->nullable();

            $t->string('status')->default('new')->index();
            $t->text('internal_notes')->nullable();

            // Consent / chargeback evidence
            $t->boolean('consent_given');
            $t->text('consent_text');
            $t->string('terms_version', 32);
            $t->timestamp('consented_at');
            $t->string('ip_address', 45)->nullable();
            $t->text('user_agent')->nullable();
            $t->text('page_url')->nullable();

            // Tracking
            $t->string('source')->nullable()->index();
            $t->json('first_touch')->nullable();
            $t->json('last_touch')->nullable();

            // Integrations
            $t->timestamp('email_sent_at')->nullable();
            $t->string('ghl_status')->default('off');  // off | pending | synced | failed
            $t->string('ghl_contact_id')->nullable();
            $t->text('ghl_error')->nullable();
            $t->unsignedSmallInteger('ghl_attempts')->default(0);
            $t->timestamp('ghl_synced_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['enquiries', 'posts', 'quiz_options', 'quiz_steps', 'testimonials', 'faqs', 'package_images',
                     'packages', 'destinations', 'tour_types', 'pages', 'media', 'settings', 'admin_users'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
