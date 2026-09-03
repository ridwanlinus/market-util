<?php

namespace App\Http\Controllers;

use App\Models\MetaInsight;
use App\Models\MetaPage;
use App\Services\CsvImportService;
use App\Services\InsightStats;
use App\Services\MetaApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MetaInsightController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId();

        $from = Carbon::parse($request->get('from', now()->subDays(29)->format('Y-m-d')))->startOfDay();
        $to = Carbon::parse($request->get('to', now()->format('Y-m-d')))->endOfDay();

        $query = MetaInsight::where('company_id', $companyId)
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('page_id')) {
            $query->where('meta_page_id', $request->page_id);
        }

        $rows = $query->with('metaPage')->orderBy('date')->get();

        $totals = InsightStats::totals($rows, ['impressions', 'reach', 'engagement', 'clicks', 'spend']);
        $totals['ctr'] = $totals['impressions'] > 0 ? round(($totals['clicks'] / $totals['impressions']) * 100, 2) : 0;

        $allRows = MetaInsight::where('company_id', $companyId)->get();
        $period = InsightStats::periodTotals($allRows, 'engagement', 30);
        $impressionsPeriod = InsightStats::periodTotals($allRows, 'impressions', 30);
        $spendPeriod = InsightStats::periodTotals($allRows, 'spend', 30);

        return view('tools.meta.index', [
            'rows' => $rows,
            'pages' => MetaPage::where('company_id', $companyId)->get(),
            'selectedPage' => $request->get('page_id'),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'totals' => $totals,
            'period' => $period,
            'impressionsPeriod' => $impressionsPeriod,
            'spendPeriod' => $spendPeriod,
            'impressionsSeries' => InsightStats::dailySeries($rows, 'impressions', $from->diffInDays($to) + 1),
            'reachSeries' => InsightStats::dailySeries($rows, 'reach', $from->diffInDays($to) + 1),
            'engagementSeries' => InsightStats::dailySeries($rows, 'engagement', $from->diffInDays($to) + 1),
            'spendSeries' => InsightStats::dailySeries($rows, 'spend', $from->diffInDays($to) + 1),
            'interactionDonut' => [
                'labels' => ['Likes', 'Comments', 'Shares', 'Saves'],
                'values' => [
                    $rows->sum('likes'),
                    $rows->sum('comments'),
                    $rows->sum('shares'),
                    $rows->sum('saves'),
                ],
            ],
            'byPage' => $this->prettyGroup($rows, 'meta_page_id', 'engagement'),
        ]);
    }

    private function prettyGroup($rows, string $groupField, string $metric): array
    {
        $grouped = InsightStats::groupBy($rows, $groupField, $metric);

        if ($groupField === 'meta_page_id') {
            $pages = MetaPage::where('company_id', $this->companyId())->pluck('name', 'id');
            $grouped['labels'] = array_map(
                fn ($id) => $pages[(int) $id] ?? 'Halaman #' . $id,
                $grouped['labels']
            );
        }

        return $grouped;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'meta_page_id' => ['required', 'exists:meta_pages,id'],
            'impressions' => ['nullable', 'numeric', 'min:0'],
            'reach' => ['nullable', 'numeric', 'min:0'],
            'engagement' => ['nullable', 'numeric', 'min:0'],
            'likes' => ['nullable', 'numeric', 'min:0'],
            'comments' => ['nullable', 'numeric', 'min:0'],
            'shares' => ['nullable', 'numeric', 'min:0'],
            'saves' => ['nullable', 'numeric', 'min:0'],
            'clicks' => ['nullable', 'numeric', 'min:0'],
            'spend' => ['nullable', 'numeric', 'min:0'],
        ]);

        $page = MetaPage::where('company_id', $this->companyId())->findOrFail($data['meta_page_id']);

        MetaInsight::updateOrCreate(
            ['company_id' => $this->companyId(), 'meta_page_id' => $page->id, 'date' => $data['date']],
            [
                'impressions' => $data['impressions'] ?? 0,
                'reach' => $data['reach'] ?? 0,
                'engagement' => $data['engagement'] ?? 0,
                'likes' => $data['likes'] ?? 0,
                'comments' => $data['comments'] ?? 0,
                'shares' => $data['shares'] ?? 0,
                'saves' => $data['saves'] ?? 0,
                'clicks' => $data['clicks'] ?? 0,
                'spend' => $data['spend'] ?? 0,
                'ctr' => isset($data['impressions']) && (int) $data['impressions'] > 0 ? round(((float) ($data['clicks'] ?? 0) / (int) $data['impressions']) * 100, 4) : null,
            ]
        );

        return back()->with('success', 'Insight Meta disimpan.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            'meta_page_id' => ['required', 'exists:meta_pages,id'],
        ]);

        $page = MetaPage::where('company_id', $this->companyId())->findOrFail($request->meta_page_id);
        $csv = new CsvImportService();
        $rows = $csv->parse($request->file('csv')->get());

        $imported = 0;
        foreach ($rows as $row) {
            $date = $csv->date($row, 'date', now()->format('Y-m-d'));
            if (! $date) {
                continue;
            }

            $impressions = $csv->int($row, 'impressions');
            $clicks = $csv->int($row, 'clicks');

            MetaInsight::updateOrCreate(
                ['company_id' => $this->companyId(), 'meta_page_id' => $page->id, 'date' => $date],
                [
                    'impressions' => $impressions,
                    'reach' => $csv->int($row, 'reach'),
                    'engagement' => $csv->int($row, 'engagement'),
                    'likes' => $csv->int($row, 'likes'),
                    'comments' => $csv->int($row, 'comments'),
                    'shares' => $csv->int($row, 'shares'),
                    'saves' => $csv->int($row, 'saves'),
                    'clicks' => $clicks,
                    'spend' => $csv->numeric($row, 'spend'),
                    'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : null,
                ]
            );
            $imported++;
        }

        return back()->with('success', "{$imported} baris insight Meta berhasil diimport.");
    }

    public function sync(Request $request)
    {
        $request->validate([
            'meta_page_id' => ['required', 'exists:meta_pages,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ]);

        $page = MetaPage::where('company_id', $this->companyId())->findOrFail($request->meta_page_id);

        try {
            $rows = app(MetaApiService::class)->fetchPageInsights(
                $page,
                Carbon::parse($request->from)->format('Y-m-d'),
                Carbon::parse($request->to)->format('Y-m-d')
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $imported = 0;
        foreach ($rows as $row) {
            MetaInsight::updateOrCreate(
                ['company_id' => $this->companyId(), 'meta_page_id' => $page->id, 'date' => $row['date']],
                app(MetaApiService::class)->mapToInsight($row)
            );
            $imported++;
        }

        return back()->with('success', "Sinkronisasi selesai: {$imported} hari data dari Meta Graph API.");
    }

    public function storePage(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'page_id' => ['nullable', 'string', 'max:64'],
            'followers_count' => ['nullable', 'numeric', 'min:0'],
            'access_token' => ['nullable', 'string'],
        ]);

        MetaPage::create([
            'company_id' => $this->companyId(),
            'name' => $data['name'],
            'page_id' => $data['page_id'] ?? null,
            'followers_count' => $data['followers_count'] ?? 0,
            'access_token' => $data['access_token'] ?? null,
            'connected_at' => now(),
        ]);

        return back()->with('success', 'Halaman Meta ditambahkan.');
    }

    public function connectPages(Request $request)
    {
        $token = $request->input('access_token') ?: config('marketing.meta.access_token');

        if (! $token) {
            return back()->with('error', 'Masukkan Access Token terlebih dahulu.');
        }

        try {
            $pages = app(MetaApiService::class)->fetchPages($token);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        $added = 0;
        foreach ($pages as $p) {
            MetaPage::updateOrCreate(
                ['company_id' => $this->companyId(), 'page_id' => $p['id']],
                [
                    'name' => $p['name'],
                    'followers_count' => $p['followers_count'] ?? 0,
                    'access_token' => $token,
                    'connected_at' => now(),
                ]
            );
            $added++;
        }

        return back()->with('success', "{$added} halaman Meta berhasil terhubung.");
    }

    public function destroy(MetaInsight $insight)
    {
        abort_unless($insight->company_id === $this->companyId(), 403);
        $insight->delete();

        return back()->with('success', 'Data insight dihapus.');
    }

    public function destroyPage(MetaPage $page)
    {
        abort_unless($page->company_id === $this->companyId(), 403);
        $page->delete();

        return back()->with('success', 'Halaman Meta dihapus.');
    }
}