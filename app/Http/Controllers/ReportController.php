<?php

namespace App\Http\Controllers;

use App\Enums\ComplianceStatus;
use App\Exports\AbsencesReportExport;
use App\Exports\ComplianceReportExport;
use App\Exports\HoursBankExport;
use App\Exports\OvertimeReportExport;
use App\Exports\PaymentReportExport;
use App\Exports\PayrollExport;
use App\Exports\TransportVoucherReportExport;
use App\Models\Absence;
use App\Models\Company;
use App\Models\EmployeeExam;
use App\Models\EmployeeTraining;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function payroll(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020|max:2100',
        ]);

        $company  = Company::findOrFail($request->integer('company_id'));
        $month    = $request->integer('month');
        $year     = $request->integer('year');
        $filename = 'folha_' . str_replace(' ', '_', $company->trade_name ?? $company->name) . "_{$month}_{$year}.xlsx";

        return Excel::download(new PayrollExport($company->id, $month, $year), $filename);
    }

    public function overtime(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020|max:2100',
        ]);

        $month    = $request->integer('month');
        $year     = $request->integer('year');
        $filename = "horas_extras_{$month}_{$year}.xlsx";

        return Excel::download(new OvertimeReportExport($request->integer('company_id'), $month, $year), $filename);
    }

    public function hoursBank(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2100',
        ]);

        $month    = $request->integer('month');
        $year     = $request->integer('year');
        $filename = "banco_horas_{$month}_{$year}.xlsx";

        return Excel::download(
            new HoursBankExport($month, $year, $request->integer('company_id') ?: null),
            $filename
        );
    }

    public function absencesXlsx(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2100',
        ]);

        $month    = $request->integer('month');
        $year     = $request->integer('year');
        $filename = "faltas_{$month}_{$year}.xlsx";

        return Excel::download(
            new AbsencesReportExport($month, $year, $request->integer('company_id') ?: null),
            $filename
        );
    }

    public function absencesPdf(Request $request): \Illuminate\Http\Response
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2100',
        ]);

        $month     = $request->integer('month');
        $year      = $request->integer('year');
        $companyId = $request->integer('company_id') ?: null;

        $query = Absence::with(['employee.position', 'employee.company'])
            ->whereYear('date', $year)
            ->whereMonth('date', $month);

        if ($companyId) {
            $query->whereHas('employee', fn ($q) => $q->where('company_id', $companyId));
        }

        $absences = $query->join('employees', 'absences.employee_id', '=', 'employees.id')
            ->orderBy('employees.name')
            ->orderBy('absences.date')
            ->select('absences.*')
            ->get();

        $company = $companyId ? Company::find($companyId)?->name : null;

        $pdf = Pdf::loadView('exports.absences', compact('absences', 'month', 'year', 'company'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("faltas_{$month}_{$year}.pdf");
    }

    public function transportVoucher(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'month'      => 'required|integer|between:1,12',
            'year'       => 'required|integer|min:2020|max:2100',
        ]);

        $month    = $request->integer('month');
        $year     = $request->integer('year');
        $filename = "vale_transporte_{$month}_{$year}.xlsx";

        return Excel::download(
            new TransportVoucherReportExport($month, $year, $request->integer('company_id')),
            $filename
        );
    }

    public function complianceXlsx(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filename = 'compliance_' . now()->format('d-m-Y') . '.xlsx';

        return Excel::download(
            new ComplianceReportExport(
                $request->integer('company_id') ?: null,
                $request->string('status_filter') ?: null,
            ),
            $filename
        );
    }

    public function compliancePdf(Request $request): \Illuminate\Http\Response
    {
        $companyId    = $request->integer('company_id') ?: null;
        $statusFilter = $request->string('status_filter') ?: null;

        $statuses = match ($statusFilter) {
            'expired'      => [ComplianceStatus::Expired->value],
            'expiring_15d' => [ComplianceStatus::Expiring15d->value],
            'expiring_30d' => [ComplianceStatus::Expiring30d->value],
            default        => [ComplianceStatus::Expired->value, ComplianceStatus::Expiring15d->value, ComplianceStatus::Expiring30d->value],
        };

        $examsQuery = EmployeeExam::with(['employee.position', 'employee.company', 'exam'])->whereIn('status', $statuses);
        $trainingsQuery = EmployeeTraining::with(['employee.position', 'employee.company', 'training'])->whereIn('status', $statuses);

        if ($companyId) {
            $examsQuery->whereHas('employee', fn ($q) => $q->where('company_id', $companyId));
            $trainingsQuery->whereHas('employee', fn ($q) => $q->where('company_id', $companyId));
        }

        $examItems = $examsQuery->get()->map(fn ($e) => [
            'type' => 'Exame', 'employee' => $e->employee,
            'item_name' => $e->exam->name ?? '', 'performed_date' => $e->performed_date,
            'expiration_date' => $e->expiration_date, 'status' => $e->status,
        ]);

        $trainingItems = $trainingsQuery->get()->map(fn ($t) => [
            'type' => 'Treinamento', 'employee' => $t->employee,
            'item_name' => $t->training->name ?? '', 'performed_date' => $t->performed_date,
            'expiration_date' => $t->expiration_date, 'status' => $t->status,
        ]);

        $items   = $examItems->merge($trainingItems)->sortBy(fn ($i) => $i['employee']->name ?? '');
        $company = $companyId ? Company::find($companyId)?->name : null;

        $pdf = Pdf::loadView('exports.compliance', compact('items', 'company'));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('compliance_' . now()->format('d-m-Y') . '.pdf');
    }

    public function payments(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year'  => 'required|integer|min:2020|max:2100',
        ]);

        $month    = $request->integer('month');
        $year     = $request->integer('year');
        $filename = "pagamentos_{$month}_{$year}.xlsx";

        return Excel::download(
            new PaymentReportExport($month, $year, $request->integer('company_id') ?: null),
            $filename
        );
    }
}
