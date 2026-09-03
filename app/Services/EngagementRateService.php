<?php

namespace App\Services;

use App\Models\MetaPost;

class EngagementRateService
{
    /**
     * Hitung Engagement Rate: (total interaksi / followers) x 100.
     *
     * @param  array{likes:int, comments:int, shares:int, saves:int}  $interactions
     */
    public function calculate(array $interactions, int|float $followers): array
    {
        $total = array_sum($interactions);

        $result = [
            'likes' => (int) ($interactions['likes'] ?? 0),
            'comments' => (int) ($interactions['comments'] ?? 0),
            'shares' => (int) ($interactions['shares'] ?? 0),
            'saves' => (int) ($interactions['saves'] ?? 0),
            'total_interactions' => $total,
            'followers' => (int) $followers,
            'rate' => 0.0,
            'grade' => 'N/A',
            'label' => 'Tidak ada data',
        ];

        if ($followers <= 0) {
            return $result;
        }

        $rate = ($total / $followers) * 100;
        $result['rate'] = round($rate, 2);

        $benchmarks = config('marketing.engagement_benchmarks');
        $result['grade'] = $this->grade($rate, $benchmarks);
        $result['label'] = $this->label($rate, $benchmarks);

        return $result;
    }

    /**
     * Hitung ER untuk MetaPost dari interaksi & followers yang tersimpan.
     */
    public function fromPost(MetaPost $post): array
    {
        return $this->calculate([
            'likes' => $post->likes,
            'comments' => $post->comments,
            'shares' => $post->shares,
            'saves' => $post->saves,
        ], $post->followers_count ?? 0);
    }

    public function grade(float $rate, array $benchmarks = []): string
    {
        $benchmarks = $benchmarks ?: config('marketing.engagement_benchmarks');

        return match (true) {
            $rate >= $benchmarks['excellent'] => 'Excellent',
            $rate >= $benchmarks['good'] => 'Good',
            $rate >= $benchmarks['average'] => 'Average',
            $rate >= $benchmarks['poor'] => 'Below Average',
            default => 'Poor',
        };
    }

    public function label(float $rate, array $benchmarks = []): string
    {
        return match ($this->grade($rate, $benchmarks)) {
            'Excellent' => 'Konten sangat engaging — pertahankan!',
            'Good' => 'Performa di atas rata-rata industri.',
            'Average' => 'Sesuai rata-rata industri.',
            'Below Average' => 'Di bawah rata-rata, coba optimalkan konten.',
            default => 'Performa rendah — perlu perbaikan strategi.',
        };
    }

    /**
     * Benchmark rate rata-rata dari seluruh post (dipakai dashboard).
     *
     * @param  \Illuminate\Support\Collection<int, MetaPost>  $posts
     */
    public function averageRate($posts): float
    {
        $rates = $posts
            ->filter(fn (MetaPost $p) => ($p->followers_count ?? 0) > 0)
            ->map(fn (MetaPost $p) => ($p->totalInteractions() / $p->followers_count) * 100);

        if ($rates->isEmpty()) {
            return 0.0;
        }

        return round($rates->avg(), 2);
    }
}