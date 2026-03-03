<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::prefix('admin/reports')->name('reports.')->middleware(['web', 'auth'])->group(function () {
    Route::post('payroll', [ReportController::class, 'payroll'])->name('payroll');
    Route::post('overtime', [ReportController::class, 'overtime'])->name('overtime');
    Route::post('hours-bank', [ReportController::class, 'hoursBank'])->name('hours-bank');
    Route::post('absences-xlsx', [ReportController::class, 'absencesXlsx'])->name('absences-xlsx');
    Route::post('absences-pdf', [ReportController::class, 'absencesPdf'])->name('absences-pdf');
    Route::post('transport-voucher', [ReportController::class, 'transportVoucher'])->name('transport-voucher');
    Route::post('compliance-xlsx', [ReportController::class, 'complianceXlsx'])->name('compliance-xlsx');
    Route::post('compliance-pdf', [ReportController::class, 'compliancePdf'])->name('compliance-pdf');
    Route::post('payments', [ReportController::class, 'payments'])->name('payments');
});

Route::get('/admin/payment-batches/{batch}/download-cnab', function (\App\Models\PaymentBatch $batch) {
    abort_unless(\Illuminate\Support\Facades\Auth::check(), 401);

    if (!$batch->file_path || !\Illuminate\Support\Facades\Storage::exists($batch->file_path)) {
        abort(404, 'Arquivo CNAB não encontrado.');
    }

    return \Illuminate\Support\Facades\Storage::download(
        $batch->file_path,
        basename($batch->file_path)
    );
})->middleware(['web', 'auth'])->name('payment-batches.download-cnab');
