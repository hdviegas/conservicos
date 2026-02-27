<?php

namespace App\Services;

use App\Enums\DayType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DsrCalculatorService
{
    /**
     * Calcula o DSR (Descanso Semanal Remunerado) para um funcionário no período.
     *
     * Fórmula:
     * DSR bruto = (salário_base / dias_úteis_do_mês) * (domingos + feriados no mês)
     * Desconto DSR = Para cada semana com falta injustificada:
     *   (valor_diário_DSR) * faltas_injustificadas_na_semana
     *
     * @param  float       $baseSalary  Salário base do funcionário
     * @param  int         $month       Mês de referência (1-12)
     * @param  int         $year        Ano de referência
     * @param  Collection  $timeRecords Registros de ponto do período (já filtrados por employee)
     * @return array{base: float, discount: float, final: float}
     */
    public function calculate(
        float $baseSalary,
        int $month,
        int $year,
        Collection $timeRecords
    ): array {
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $periodEnd   = Carbon::create($year, $month, 1)->endOfMonth();

        $workingDays = $this->countWorkingDays($periodStart, $periodEnd, $timeRecords);

        if ($workingDays === 0 || $baseSalary <= 0) {
            return ['base' => 0.0, 'discount' => 0.0, 'final' => 0.0];
        }

        $dsrDays    = $this->countDsrDays($timeRecords);
        $dsrBase    = round(($baseSalary / $workingDays) * $dsrDays, 2);
        $dsrDiscount = $this->calculateDsrDiscount($baseSalary, $workingDays, $timeRecords);
        $dsrFinal   = max(0.0, $dsrBase - $dsrDiscount);

        return [
            'base'     => $dsrBase,
            'discount' => $dsrDiscount,
            'final'    => round($dsrFinal, 2),
        ];
    }

    /**
     * Conta dias úteis do mês (segunda a sábado, excluindo feriados).
     */
    private function countWorkingDays(Carbon $start, Carbon $end, Collection $timeRecords): int
    {
        $holidayDates = $timeRecords
            ->filter(fn ($r) => $this->getDayTypeValue($r) === DayType::Holiday->value)
            ->pluck('date')
            ->map(fn ($d) => $d instanceof Carbon ? $d->format('Y-m-d') : (string) $d)
            ->toArray();

        $count  = 0;
        $period = CarbonPeriod::create($start, $end);

        foreach ($period as $day) {
            if ($day->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }
            if (in_array($day->format('Y-m-d'), $holidayDates)) {
                continue;
            }
            $count++;
        }

        return $count;
    }

    /**
     * Conta dias de DSR: domingos + feriados presentes nos registros do funcionário.
     */
    private function countDsrDays(Collection $timeRecords): int
    {
        return $timeRecords->filter(function ($record) {
            return in_array($this->getDayTypeValue($record), [
                DayType::Sunday->value,
                DayType::Holiday->value,
            ]);
        })->count();
    }

    /**
     * Calcula o desconto de DSR por faltas injustificadas por semana.
     *
     * Desconto semanal = valor_diário_DSR * número_de_faltas_na_semana
     */
    private function calculateDsrDiscount(
        float $baseSalary,
        int $workingDays,
        Collection $timeRecords
    ): float {
        $dailyDsrValue  = $baseSalary / $workingDays;
        $totalDiscount  = 0.0;

        $recordsByWeek = $timeRecords->groupBy(function ($record) {
            $date = $record->date instanceof Carbon
                ? $record->date
                : Carbon::parse($record->date);

            return $date->format('Y-W');
        });

        foreach ($recordsByWeek as $weekRecords) {
            $unjustifiedAbsences = $weekRecords->filter(
                fn ($r) => $this->getDayTypeValue($r) === DayType::Absence->value
            )->count();

            if ($unjustifiedAbsences > 0) {
                $totalDiscount += $dailyDsrValue * $unjustifiedAbsences;
            }
        }

        return round($totalDiscount, 2);
    }

    private function getDayTypeValue(mixed $record): string
    {
        $type = $record->day_type;

        return $type instanceof DayType ? $type->value : (string) $type;
    }
}
