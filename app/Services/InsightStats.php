<?php

namespace App\Services;

use Illuminate\Support\Collection;

class InsightStats
{
    /**
     * Total metrik dari koleksi model.
     *
     * @param  Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
     * @param  string[]  $metrics
     */
    public static function totals(Collection $rows, array $metrics): array
    {
        $out = [];
        foreach ($metrics as $metric) {
            $out[$metric] = (float) $rows->sum($metric);
        }

        return $out;
    }

    /**
     * Rata-rata harian metrik.
     */
    public static function averages(Collection $rows, array $metrics): array
    {
        $out = [];
        $count = max($rows->count(), 1);
        foreach ($metrics as $metric) {
            $out[$metric] = round($rows->sum($metric) / $count, 2);
        }

        return $out;
    }

    /**
     * Persentase perubahan antara dua periode.
     */
    public static function delta(float $current, float $previous): ?float
    {
        if ($previous == 0) {
            return $current == 0 ? 0.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Deret harian tanggal -> metrik (mengisi hari kosong dengan 0).
     *
     * @param  Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
     * @return array{labels:string[], values:float[]}
     */
    public static function dailySeries(Collection $rows, string $metric, int $days = 30): array
    {
        $byDate = $rows->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $labels = [];
        $values = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $values[] = (float) ($byDate[$date]->{$metric} ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * Bandingkan dua periode terakhir (mis. 30 hari vs 30 hari sebelumnya).
     *
     * @param  Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
     */
    public static function periodTotals(Collection $rows, string $metric, int $days = 30): array
    {
        $cutoff = now()->subDays($days)->startOfDay();

        $current = $rows->filter(fn ($r) => $r->date->gte($cutoff))->sum($metric);
        $previous = $rows->filter(fn ($r) => $r->date->lt($cutoff))->sum($metric);

        return [
            'current' => (float) $current,
            'previous' => (float) $previous,
            'delta' => self::delta((float) $current, (float) $previous),
        ];
    }

    /**
     * Agregasi per kelompok (mis. per campaign) untuk donut/bar chart.
     *
     * @param  Collection<int, \Illuminate\Database\Eloquent\Model>  $rows
     * @return array{labels:string[], values:float[]}
     */
    public static function groupBy(Collection $rows, string $groupField, string $metric): array
    {
        $grouped = $rows->groupBy($groupField)->map(fn ($g) => (float) $g->sum($metric))->sortDesc();

        return [
            'labels' => $grouped->keys()->values()->all(),
            'values' => $grouped->values()->all(),
        ];
    }
}