<?php

use App\Http\Controllers\MailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// send email unsettled
Route::get('send-email-attachment', [MailController::class, 'attachment'])->name('send.mail.attachment');
Route::get('send-email', [MailController::class, 'index'])->name('send.email');

// send email notification unsettled
Route::get('/kirim-email', [MailController::class, 'notification'])->name('kirim');

// send email settled
Route::get('send-email-settle', [MailController::class, 'sendsettle'])->name('send.email.settle');








