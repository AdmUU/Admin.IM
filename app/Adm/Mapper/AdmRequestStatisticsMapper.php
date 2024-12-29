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

namespace App\Adm\Mapper;

use App\Adm\Model\AdmRequestStatistics;
use Hyperf\DbConnection\Db;
use Mine\Abstracts\AbstractMapper;

/**
 * Class AdmRequestStatisticsMapper.
 */
class AdmRequestStatisticsMapper extends AbstractMapper
{
    /**
     * @var AdmRequestStatistics
     */
    public $model;

    public function assignModel()
    {
        $this->model = AdmRequestStatistics::class;
    }

    /**
     * Record a new request.
     */
    public function saveRecord(string $optype, int $count = 1): bool|int
    {
        $today = date('Y-m-d');
        $exists = $this->model::query()
            ->where('optype', $optype)
            ->where('date', $today)
            ->first();

        if ($exists) {
            return $this->model::query()
                ->where('optype', $optype)
                ->where('date', $today)
                ->increment('request_count', $count);
        }

        $statistics = new AdmRequestStatistics();
        $statistics->optype = $optype;
        $statistics->date = $today;
        $statistics->year = (int) date('Y');
        $statistics->month = (int) date('m');
        $statistics->day = (int) date('d');
        $statistics->request_count = $count;
        return $statistics->save();
    }

    /**
     * Get daily statistics within date range.
     */
    public function getDayStatistics(string $optype, string $startDate, string $endDate): array
    {
        return $this->model::query()
            ->where('optype', $optype)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->orderBy('date', 'asc')
            ->get(['date as period', 'request_count'])
            ->toArray();
    }

    /**
     * Get monthly statistics within date range.
     */
    public function getMonthStatistics(string $optype, string $startMonth, string $endMonth): array
    {
        [$startYear, $startMonthNum] = explode('-', $startMonth);
        [$endYear, $endMonthNum] = explode('-', $endMonth);

        return $this->model::query()
            ->select([
                'year',
                'month',
                Db::raw('SUM(request_count) as total_requests'),
            ])
            ->where('optype', $optype)
            ->where(function ($query) use ($startYear, $startMonthNum, $endYear, $endMonthNum) {
                if ($startYear == $endYear) {
                    $query->where('year', $startYear)
                        ->whereBetween('month', [$startMonthNum, $endMonthNum]);
                } else {
                    $query->where(function ($q) use ($startYear, $startMonthNum) {
                        $q->where('year', $startYear)
                            ->where('month', '>=', $startMonthNum);
                    })->orWhere(function ($q) use ($endYear, $endMonthNum) {
                        $q->where('year', $endYear)
                            ->where('month', '<=', $endMonthNum);
                    })->orWhere(function ($q) use ($startYear, $endYear) {
                        $q->where('year', '>', $startYear)
                            ->where('year', '<', $endYear);
                    });
                }
            })
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => sprintf('%d-%02d', $item->year, $item->month),
                    'request_count' => $item->total_requests,
                ];
            })
            ->toArray();
    }

    /**
     * Get yearly statistics within year range.
     */
    public function getYearStatistics(string $optype, string $startYear, string $endYear): array
    {
        return $this->model::query()
            ->select([
                'year',
                Db::raw('SUM(request_count) as total_requests'),
            ])
            ->where('optype', $optype)
            ->where('year', '>=', $startYear)
            ->where('year', '<=', $endYear)
            ->groupBy('year')
            ->orderBy('year', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'period' => (string) $item->year,
                    'request_count' => $item->total_requests,
                ];
            })
            ->toArray();
    }
}
