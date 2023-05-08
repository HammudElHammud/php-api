<?php

class CalculateMaxDuration
{
    public static function calculate(string $startDate, ?string $endDate, ?string $durationUnit = 'DAYS'): ?float
    {
        $startDate = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $startDate);
        if ($endDate) {
            $endDate = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $endDate);
            $timeDiff = $startDate->diff($endDate);
            return match ($durationUnit) {
                'HOURS' => round($timeDiff->h + ($timeDiff->days * 24), 2),
                'WEEKS' => round($timeDiff->days / 7, 2),
                default => $timeDiff->days,
            };
        }
        return null;
    }
}