<?php

namespace App\Services;

use App\Enums\WorkScheduleType;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WorkingDaysCalculatorService
{
    /**
     * Calculate working days in a period using the Mon–Sat minus holidays baseline.
     * Used to populate the reference field in the "Gerar Vales" modal.
     */
    public function calculateBaseline(Carbon $periodStart, Carbon $periodEnd): int
    {
        $holidayDates = $this->loadHolidayDates($periodStart, $periodEnd);

        return $this->countWeekDays($periodStart, $periodEnd, 6, $holidayDates);
    }

    /**
     * Adjust the user-confirmed workable days for the period by applying the employee's
     * work schedule pattern. Called during voucher generation after the user confirms
     * the baseline days in the modal.
     *
     * - Regular Mon–Sat (6 days): baseline is already Mon–Sat, use as-is.
     * - Regular Mon–Fri (5 days): recalculate from scratch for Mon–Fri minus holidays.
     * - Scale12x36: approx. every other day → ceil(baseline / 2).
     * - Scale6x2:   work 6, rest 2 cycle → floor(baseline × 6/8).
     * - Null/unknown: use baseline unchanged.
     */
    public function adjustForSchedule(?WorkSchedule $schedule, int $baselineDays, Carbon $periodStart, Carbon $periodEnd): int
    {
        if (! $schedule) {
            return $baselineDays;
        }

        return match ($schedule->type) {
            WorkScheduleType::Regular    => $this->adjustRegular($schedule, $baselineDays, $periodStart, $periodEnd),
            WorkScheduleType::Scale12x36 => (int) ceil($baselineDays / 2),
            WorkScheduleType::Scale6x2   => (int) floor($baselineDays * 6 / 8),
            default                      => $baselineDays,
        };
    }

    /**
     * @deprecated Use calculateBaseline() for the modal reference, adjustForSchedule() for generation.
     */
    public function calculate(?WorkSchedule $schedule, Carbon $periodStart, Carbon $periodEnd): int
    {
        $holidayDates = $this->loadHolidayDates($periodStart, $periodEnd);

        if (! $schedule) {
            return $this->countWeekDays($periodStart, $periodEnd, 6, $holidayDates);
        }

        return match ($schedule->type) {
            WorkScheduleType::Regular    => $this->calculateRegular($schedule, $periodStart, $periodEnd, $holidayDates),
            WorkScheduleType::Scale12x36 => $this->calculateScale12x36($periodStart, $periodEnd, $holidayDates),
            WorkScheduleType::Scale6x2   => $this->calculateScale6x2($periodStart, $periodEnd, $holidayDates),
            default                      => $this->countWeekDays($periodStart, $periodEnd, 6, $holidayDates),
        };
    }

    /**
     * Load active holiday dates (as 'Y-m-d' strings) for the given period from the holidays table.
     *
     * @return Collection<int, string>
     */
    private function loadHolidayDates(Carbon $start, Carbon $end): Collection
    {
        return DB::table('holidays')
            ->where('active', true)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('date')
            ->map(fn ($d) => Carbon::parse($d)->toDateString());
    }

    /**
     * Adjust baseline days for a Regular schedule.
     *
     * Mon–Sat (6 days): the baseline was already calculated as Mon–Sat, use it directly.
     * Mon–Fri (5 days): recalculate from the period dates to get the correct Mon–Fri count.
     */
    private function adjustRegular(WorkSchedule $schedule, int $baselineDays, Carbon $start, Carbon $end): int
    {
        $weeklyWorkDays = $schedule->weekly_work_days ?? 6;

        if ($weeklyWorkDays === 6) {
            return $baselineDays;
        }

        // Recalculate for Mon–Fri, since baseline was Mon–Sat.
        $holidayDates = $this->loadHolidayDates($start, $end);

        return $this->countWeekDays($start, $end, $weeklyWorkDays, $holidayDates);
    }

    /**
     * Regular schedule: count Mon–Fri (5 days) or Mon–Sat (6 days), excluding holidays.
     * Defaults to 6 days when weekly_work_days is not configured.
     */
    private function calculateRegular(WorkSchedule $schedule, Carbon $start, Carbon $end, Collection $holidayDates): int
    {
        return $this->countWeekDays($start, $end, $schedule->weekly_work_days ?? 6, $holidayDates);
    }

    /**
     * 12x36 schedule: every-other-day approximation, then subtract holidays that
     * would fall on working days (roughly half of all holidays in the period).
     */
    private function calculateScale12x36(Carbon $start, Carbon $end, Collection $holidayDates): int
    {
        $totalDays    = $start->diffInDays($end) + 1;
        $workingDays  = (int) ceil($totalDays / 2);
        $holidayCount = (int) round($holidayDates->count() / 2);

        return max(0, $workingDays - $holidayCount);
    }

    /**
     * 6x2 schedule: 6/8 ratio approximation, then subtract holidays proportionally.
     */
    private function calculateScale6x2(Carbon $start, Carbon $end, Collection $holidayDates): int
    {
        $totalDays    = $start->diffInDays($end) + 1;
        $workingDays  = (int) floor($totalDays * 6 / 8);
        $holidayCount = (int) round($holidayDates->count() * 6 / 8);

        return max(0, $workingDays - $holidayCount);
    }

    /**
     * Count days in the period that:
     *   - Fall within the schedule's work-week pattern (Mon–Fri or Mon–Sat)
     *   - Are NOT public holidays
     *
     * @param Collection<int, string> $holidayDates  Date strings in 'Y-m-d' format
     * @param int $weeklyWorkDays  5 = Mon–Fri, 6 = Mon–Sat
     */
    private function countWeekDays(Carbon $start, Carbon $end, int $weeklyWorkDays, Collection $holidayDates): int
    {
        $count = 0;

        foreach (CarbonPeriod::create($start->startOfDay(), $end->startOfDay()) as $day) {
            $dow      = $day->dayOfWeek; // 0 = Sun, 1 = Mon, …, 6 = Sat
            $dateStr  = $day->toDateString();

            // Skip weekend days based on schedule pattern.
            if ($weeklyWorkDays === 5 && in_array($dow, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                continue;
            }
            if ($weeklyWorkDays === 6 && $dow === Carbon::SUNDAY) {
                continue;
            }

            // Skip public holidays.
            if ($holidayDates->contains($dateStr)) {
                continue;
            }

            $count++;
        }

        return $count;
    }
}
