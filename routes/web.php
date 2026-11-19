<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TourTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Unlisted design test page: no menu link, noindex, not in the sitemap.
Route::get('/test-home', \App\Http\Controllers\TestHomeController::class)->name('test-home');

Route::get('/packages', [PackageController::class, 'index'])->name('packages.index');
Route::get('/packages/{package:slug}', [PackageController::class, 'show'])->name('packages.show');
Route::get('/destinations/{slug}', [DestinationController::class, 'show'])->name('destinations.show');
Route::get('/tours/{slug}', [TourTypeController::class, 'show'])->name('tours.show');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/meet-our-team', [PageController::class, 'team'])->name('team');
Route::get('/testimonials', [PageController::class, 'testimonials'])->name('testimonials');
Route::get('/faqs', [PageController::class, 'faqs'])->name('faqs');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/photo-credits', [PageController::class, 'credits'])->name('credits');

Route::get('/guides', [BlogController::class, 'index'])->name('blog.index');
Route::get('/guides/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Every enquiry form posts here.
Route::post('/enquiry', [EnquiryController::class, 'store'])->middleware('throttle:enquiries')->name('enquiry.store');
Route::get('/thank-you', [EnquiryController::class, 'thankYou'])->name('thank-you');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

require __DIR__.'/admin.php';

// Unknown URLs run through the web middleware (session, CSRF) so the custom 404 page can show the enquiry modal.
Route::fallback(fn () => abort(404));
