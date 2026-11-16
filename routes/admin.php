<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Auth (throttled)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [Admin\AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.submit');
    });

    Route::middleware(['auth:admin', 'admin.role'])->group(function () {
        Route::post('logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Enquiries
        Route::get('enquiries/export', [Admin\EnquiryController::class, 'export'])->name('enquiries.export');
        Route::resource('enquiries', Admin\EnquiryController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('enquiries/{enquiry}/ghl-retry', [Admin\EnquiryController::class, 'retryGhl'])->name('enquiries.ghl-retry');

        // Packages
        Route::put('packages-order', [Admin\PackageController::class, 'reorder'])->name('packages.reorder');
        Route::post('packages/bulk/{action}', [Admin\PackageController::class, 'bulk'])->whereIn('action', ['trash', 'restore', 'publish', 'unpublish', 'sample', 'unsample', 'feature', 'unfeature'])->name('packages.bulk');
        Route::resource('packages', Admin\PackageController::class)->except('show');
        Route::post('packages/{package}/duplicate', [Admin\PackageController::class, 'duplicate'])->name('packages.duplicate');
        Route::post('packages/{package}/toggle/{field}', [Admin\PackageController::class, 'toggle'])->whereIn('field', ['is_published', 'is_featured'])->name('packages.toggle');

        Route::resource('tour-types', Admin\TourTypeController::class)->except('show')->parameters(['tour-types' => 'tour_type']);
        Route::resource('destinations', Admin\DestinationController::class)->except('show');

        // Quiz
        Route::get('quiz', [Admin\QuizController::class, 'index'])->name('quiz.index');
        Route::post('quiz', [Admin\QuizController::class, 'store'])->name('quiz.store');
        Route::put('quiz/{step}', [Admin\QuizController::class, 'update'])->name('quiz.update');
        Route::delete('quiz/{step}', [Admin\QuizController::class, 'destroy'])->name('quiz.destroy');

        // Pages, testimonials, FAQs, blog
        Route::get('pages', [Admin\PageController::class, 'index'])->name('pages.index');
        Route::get('pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
        Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
        Route::resource('faqs', Admin\FaqController::class)->except('show');
        Route::resource('posts', Admin\PostController::class)->except('show');

        // Settings, users, media
        Route::get('settings', [Admin\SettingController::class, 'edit'])->name('settings.edit');
        Route::get('gohighlevel', [Admin\GhlController::class, 'edit'])->name('ghl.edit');
        Route::put('gohighlevel', [Admin\GhlController::class, 'update'])->name('ghl.update');
        Route::post('gohighlevel/test', [Admin\GhlController::class, 'test'])->name('ghl.test');
        Route::post('gohighlevel/setup', [Admin\GhlController::class, 'setup'])->name('ghl.setup');
        Route::put('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
        Route::resource('users', Admin\AdminUserController::class)->except('show');
        Route::get('media', [Admin\MediaController::class, 'index'])->name('media.index');
        Route::post('media', [Admin\MediaController::class, 'store'])->name('media.store');
        Route::put('media/{media}', [Admin\MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{media}', [Admin\MediaController::class, 'destroy'])->name('media.destroy');
    });
});
