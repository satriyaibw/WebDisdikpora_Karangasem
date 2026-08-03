<?php

use App\Http\Controllers\Public\AgendaController;
use App\Http\Controllers\Public\AnnouncementController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\DownloadController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\NewsController;
use App\Http\Controllers\Public\PpidController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\ServiceController;
use App\Http\Controllers\Public\SopController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::get('/profil', [ProfileController::class, 'index'])->name('profil');
Route::get('/profil/struktur', [ProfileController::class, 'struktur'])->name('profil.struktur');

Route::get('/layanan', [ServiceController::class, 'index'])->name('layanan.index');
Route::get('/layanan/{service:slug}', [ServiceController::class, 'show'])->name('layanan.show');

Route::get('/sop', [SopController::class, 'index'])->name('sop.index');
Route::get('/sop/{sopDocument:slug}', [SopController::class, 'show'])->name('sop.show');
Route::get('/sop/{sopDocument:slug}/download', [SopController::class, 'download'])->name('sop.download');

Route::get('/ppid', [PpidController::class, 'index'])->name('ppid.index');
Route::get('/ppid/{ppidDocument}/download', [PpidController::class, 'download'])->name('ppid.download');

Route::get('/berita', [NewsController::class, 'index'])->name('berita.index');
Route::get('/berita/{news:slug}', [NewsController::class, 'show'])->name('berita.show');

Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('pengumuman.index');

Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri.index');
Route::get('/galeri/{album}', [GalleryController::class, 'show'])->name('galeri.show');

Route::get('/unduhan', [DownloadController::class, 'index'])->name('unduhan.index');
Route::get('/unduhan/{downloadFile:slug}/download', [DownloadController::class, 'download'])->name('unduhan.download');

Route::get('/layanan/{service:slug}/unduh-formulir', [ServiceController::class, 'download'])->name('layanan.unduh-formulir');
Route::get('/pengumuman/{announcement}/unduh-lampiran', [AnnouncementController::class, 'download'])->name('pengumuman.unduh-lampiran');

Route::get('/kontak', ContactController::class)->name('kontak');
