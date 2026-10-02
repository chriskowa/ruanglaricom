<?php

use App\Http\Controllers\KalenderPelari\AssetController;
use App\Http\Controllers\KalenderPelari\CheckoutController;
use App\Http\Controllers\KalenderPelari\EditorController;
use App\Http\Controllers\KalenderPelari\LandingController;
use App\Http\Controllers\KalenderPelari\PrintDownloadController;
use App\Http\Controllers\KalenderPelari\ProjectApiController;
use App\Http\Controllers\KalenderPelari\TemplateGalleryController;
use App\Http\Controllers\KalenderPelari\TrialEditorController;
use App\Http\Controllers\KalenderPelari\WizardController;
use Illuminate\Support\Facades\Route;

$anonThrottle = 'throttle:kp-anon-trial';
$userThrottle = 'throttle:kp-asset-upload';

Route::group([
    'prefix' => 'kalender-pelari',
    'as' => 'kalender-pelari.',
], function () use ($anonThrottle, $userThrottle) {

    Route::get('/', [LandingController::class, 'index'])
        ->name('landing')
        ->middleware($anonThrottle);

    Route::get('/buat', [WizardController::class, 'index'])
        ->name('wizard')
        ->middleware($anonThrottle);
    Route::post('/buat', [WizardController::class, 'store'])
        ->name('wizard.store')
        ->middleware($anonThrottle);

    Route::get('/editor/trial/{anonymousUuid}', [TrialEditorController::class, 'index'])
        ->name('trial.editor')
        ->middleware($anonThrottle)
        ->whereUuid('anonymousUuid');

    Route::post('/api/projects/trial/autosave', [ProjectApiController::class, 'autosaveTrial'])
        ->name('api.trial.autosave')
        ->middleware($anonThrottle);

    Route::post('/api/assets/trial/mark', [AssetController::class, 'markLocalOnly'])
        ->name('api.asset.local-only')
        ->middleware($anonThrottle);
});

Route::group([
    'prefix' => 'kalender-pelari',
    'as' => 'kalender-pelari.',
    'middleware' => ['auth', 'role:runner,user,admin,coach,eo', $userThrottle],
], function () {

    Route::get('/proyek-saya', [EditorController::class, 'myProjects'])
        ->name('my-projects');

    Route::get('/editor/{project}', [EditorController::class, 'index'])
        ->name('editor')
        ->can('update', 'project');

    Route::delete('/proyek/{project}', [EditorController::class, 'destroy'])
        ->name('destroy')
        ->can('delete', 'project');

    Route::post('/api/projects/autosave', [ProjectApiController::class, 'autosave'])
        ->name('api.autosave');

    Route::post('/api/projects/trial/convert', [ProjectApiController::class, 'convertTrial'])
        ->name('api.trial.convert')
        ->middleware('throttle:kp-anon-trial');

    Route::get('/api/runner/data/monthly', [ProjectApiController::class, 'runnerMonthlyData'])
        ->name('api.runner.monthly');

    Route::post('/api/runner/activity/manual', [ProjectApiController::class, 'storeManualActivity'])
        ->name('api.runner.activity.manual');

    Route::get('/cetak/{project}', [PrintDownloadController::class, 'preview'])
        ->name('print.preview')
        ->can('view', 'project')
        ->middleware('throttle:kp-pdf-heavy');

    Route::get('/unduh/{project}/pdf', [PrintDownloadController::class, 'downloadPdf'])
        ->name('print.download')
        ->can('view', 'project')
        ->middleware('throttle:kp-pdf-heavy');

    Route::get('/checkout/{project}', [CheckoutController::class, 'index'])
        ->name('checkout')
        ->can('view', 'project')
        ->middleware('throttle:kp-payment');

    Route::post('/checkout/{project}', [CheckoutController::class, 'store'])
        ->name('checkout.store')
        ->can('view', 'project')
        ->middleware('throttle:kp-payment');

    Route::get('/template-galeri', [TemplateGalleryController::class, 'index'])
        ->name('templates.gallery');
});
