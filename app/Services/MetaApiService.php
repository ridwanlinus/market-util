<?php

namespace App\Services;

use App\Models\MetaPage;
use Illuminate\Support\Facades\Http;

class MetaApiService
{
    public function hasToken(?MetaPage $page = null): bool
    {
        $token = $page?->access_token ?: config('marketing.meta.access_token');

        return (bool) $token;
    }

    /**
     * Ambil insight harian halaman Meta (impressions, reach, engagement, dll).
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchPageInsights(MetaPage $page, string $since, string $until): array
    {
        $token = $page->access_token ?: config('marketing.meta.access_token');

        if (! $token) {
            throw new \RuntimeException('Meta Access Token belum dikonfigurasi. Isi di halaman Settings.');
        }

        $version = config('marketing.meta.graph_version');

        $response = Http::get("https://graph.facebook.com/{$version}/{$page->page_id}/insights", [
            'metric' => 'page_impressions,page_impressions_unique,page_engaged_users,page_post_engagements,page_fan_adds,page_ctr,page_cpc,page_video_views,page_follows',
            'period' => 'day',
            'since' => $since,
            'until' => $until,
            'access_token' => $token,
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Terjadi kesalahan pada Meta API');
            throw new \RuntimeException($error);
        }

        $data = $response->json('data', []);
        $rows = [];

        foreach ($data as $metric) {
            foreach ($metric['values'] ?? [] as $point) {
                $date = $point['end_time'] ?? $point['value']['end_time'] ?? null;
                if (! $date) {
                    continue;
                }
                $date = substr($date, 0, 10);
                $rows[$date] ??= ['date' => $date];
                $rows[$date][$metric['name']] = (float) ($point['value'] ?? 0);
            }
        }

        ksort($rows);

        return array_values($rows);
    }

    /**
     * Ambil daftar halaman yang dimiliki token.
     */
    public function fetchPages(string $token): array
    {
        $version = config('marketing.meta.graph_version');

        $response = Http::get("https://graph.facebook.com/{$version}/me/accounts", [
            'access_token' => $token,
            'fields' => 'id,name,followers_count',
        ]);

        if ($response->failed()) {
            $error = $response->json('error.message', 'Terjadi kesalahan pada Meta API');
            throw new \RuntimeException($error);
        }

        return $response->json('data', []);
    }

    /**
     * Petakan metric Graph API ke kolom meta_insights.
     */
    public function mapToInsight(array $row): array
    {
        return [
            'impressions' => (int) ($row['page_impressions'] ?? 0),
            'reach' => (int) ($row['page_impressions_unique'] ?? 0),
            'engagement' => (int) ($row['page_engaged_users'] ?? 0),
            'likes' => (int) ($row['page_post_engagements'] ?? 0),
            'comments' => (int) ($row['page_fan_adds'] ?? 0),
            'shares' => (int) ($row['page_follows'] ?? 0),
            'clicks' => (int) ($row['page_ctr'] ?? 0),
            'ctr' => isset($row['page_ctr']) ? round((float) $row['page_ctr'] / 100, 4) : null,
        ];
    }
}