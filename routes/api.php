<?php

use App\Http\Controllers\CPOPController;
use App\Models\CPOP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// unsettled
Route::get('/upload-unsettled',[CPOPController::class, 'uploadUnsettled'])->name('import.content');
Route::get('/upload-unsettled-excel',[CPOPController::class, 'fidingMerchentMID'])->name('import.content.excel');
Route::get('/upload-unsettled-export-excel',[CPOPController::class, 'generateCpopUnsettled'])->name('import.export.excel');
Route::get('/upload-unsettled-sendemail',[CPOPController::class, 'sendEmailUnsettled'])->name('send.email.unsettled');

// settled
Route::get('/upload-settlement',[CPOPController::class, 'uploadSettled'])->name('import.export.excel');
Route::get('/upload-settled-excel',[CPOPController::class, 'fidingCpopGenerate'])->name('import.excel');
Route::get('/upload-settlement-excel',[CPOPController::class, 'generateReportSettled'])->name('import.export.excel.report');
Route::get('/upload-settlement-sendemail',[CPOPController::class, 'sendEmailSettled'])->name('send.email.settled');
