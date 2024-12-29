<?php

declare(strict_types=1);
/**
 * This file is part of Admin.IM
 *
 * @link     https://www.admin.im
 * @github   https://github.com/AdmUU/Admin.IM
 * @contact  dev@admin.im
 * @license  https://github.com/AdmUU/Admin.IM/blob/main/LICENSE
 */

namespace App\Adm\Service;

use App\Adm\Mapper\AdmRequestStatisticsMapper;
use Carbon\Carbon;
use Hyperf\Cache\Annotation\Cacheable;
use Mine\Abstracts\AbstractService;

/**
 * Request Statistics service.
 */
class AdmRequestStatisticsService extends AbstractService
{
    /**
     * @var AdmRequestStatisticsMapper
     */
    public $mapper;

    public function __construct(AdmRequestStatisticsMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Cache request statistics.
     */
    public function add(string $optype): void
    {
        $rstatKey = 'rstat:' . $optype . ':' . date('Y-m-d');
        $redis = redis();
        if (! $redis->exists($rstatKey)) {
            $redis->set($rstatKey, 0, ['nx', 'ex' => strtotime('tomorrow 00:10:00')]);
        }
        $redis->incr($rstatKey);
    }

    /**
     * Record a new request.
     */
    public function saveRecord(string $optype): void
    {
        $rstatKey = 'rstat:' . $optype . ':' . date('Y-m-d', time() - 300);
        $redis = redis();
        $count = (int) $redis->get($rstatKey);
        if ($count > 0) {
            $this->mapper->saveRecord($optype, $count);
            $redis->incr($rstatKey, -$count);
        }
    }

    /**
     * Validate date range parameters and get statistics data.
     */
    public function getRecord(array $data): array
    {
        $optype = $data['optype'] ?? 'ping';
        $qtype = $data['qtype'] ?? 'day';
        if (! in_array($qtype, ['day', 'month', 'year'])) {
            return ['error' => 'Unsupported query type'];
        }

        $datePattern = match ($qtype) {
            'year' => '/^\d{4}$/',
            'month' => '/^\d{4}-\d{2}$/',
            'day' => '/^\d{4}-\d{2}-\d{2}$/',
        };

        $defaultEnd = match ($qtype) {
            'year' => date('Y'),
            'month' => date('Y-m'),
            'day' => date('Y-m-d'),
        };

        $defaultStart = match ($qtype) {
            'year' => date('Y', strtotime('-1 year')),
            'month' => date('Y-m', strtotime('-11 month')),
            'day' => date('Y-m-d', strtotime('-9 days')),
        };

        $start = $data['start'] ?? $defaultStart;
        $end = $data['end'] ?? $defaultEnd;

        if (! preg_match($datePattern, $start) || ! preg_match($datePattern, $end)) {
            $formatExample = match ($qtype) {
                'year' => 'YYYY',
                'month' => 'YYYY-MM',
                'day' => 'YYYY-MM-DD',
            };
            return ['error' => "Invalid date format. Required format: {$formatExample}"];
        }

        if ($start > $end) {
            return ['error' => 'Start date cannot be greater than end date'];
        }

        return $this->statistics($optype, $qtype, $start, $end);
    }

    /**
     * Get statistics data based on type and date range.
     */
    #[Cacheable(prefix: 'adm:cache:stat:req', value: '_#{optype}_#{qtype}_#{start}_#{end}', ttl: 120)]
    public function statistics(string $optype, string $qtype, string $start, string $end): array
    {
        $rawData = match ($qtype) {
            'year' => $this->mapper->getYearStatistics($optype, $start, $end),
            'month' => $this->mapper->getMonthStatistics($optype, $start, $end),
            default => $this->mapper->getDayStatistics($optype, $start, $end),
        };

        return $this->fillPeriods($rawData, $qtype, $start, $end);
    }

    /**
     * Fill missing periods.
     */
    private function fillPeriods(array $data, string $qtype, string $start, string $end): array
    {
        $dataMap = array_column($data, 'request_count', 'period');

        $periods = match ($qtype) {
            'year' => $this->generateYearPeriods($start, $end),
            'month' => $this->generateMonthPeriods($start, $end),
            default => $this->generateDayPeriods($start, $end),
        };

        $result = [];
        foreach ($periods as $period) {
            $result[] = [
                'period' => $period,
                'request_count' => $dataMap[$period] ?? 0,
            ];
        }

        return $result;
    }

    /**
     * Generate missing day periods.
     */
    private function generateDayPeriods(string $start, string $end): array
    {
        $periods = [];
        $current = Carbon::parse($start);
        $endDate = Carbon::parse($end);

        while ($current <= $endDate) {
            $periods[] = $current->format('Y-m-d');
            $current->addDay();
        }

        return $periods;
    }

    /**
     * Generate missing month periods.
     */
    private function generateMonthPeriods(string $start, string $end): array
    {
        $periods = [];
        $current = Carbon::parse($start)->startOfMonth();
        $endDate = Carbon::parse($end)->startOfMonth();

        while ($current <= $endDate) {
            $periods[] = $current->format('Y-m');
            $current->addMonth();
        }

        return $periods;
    }

    /**
     * Generate missing year periods.
     */
    private function generateYearPeriods(string $start, string $end): array
    {
        $periods = [];
        $startYear = (int) Carbon::parse($start)->format('Y');
        $endYear = (int) Carbon::parse($end)->format('Y');

        for ($year = $startYear; $year <= $endYear; ++$year) {
            $periods[] = (string) $year;
        }

        return $periods;
    }
}
