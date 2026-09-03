<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\GaInsight;
use App\Models\GoogleAdsInsight;
use App\Models\MetaInsight;
use App\Models\MetaPost;
use App\Services\EngagementRateService;
use App\Services\InsightStats;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(EngagementRateService $er)
    {
        $companyId = $this->companyId();

        // ---- Content Studio ----
        $contents = Content::where('company_id', $companyId)
            ->with('user')
            ->latest()
            ->limit(6)
            ->get();

        $contentStats = Content::where('company_id', $companyId)
            ->selectRaw("count(*) as total, sum(case when status='approved' then 1 else 0 end) as approved, sum(case when status='pending' then 1 else 0 end) as pending, sum(case when status='rejected' then 1 else 0 end) as rejected, sum(case when status='draft' then 1 else 0 end) as drafts")
            ->first();

        // ---- Meta ----
        $metaRows = MetaInsight::where('company_id', $companyId)->get();
        $metaTotals = InsightStats::totals($metaRows, ['impressions', 'reach', 'engagement', 'clicks', 'spend']);
        $metaTrend = InsightStats::dailySeries($metaRows->where('date', '>=', now()->subDays(29)), 'impressions', 30);
        $metaPeriod = InsightStats::periodTotals($metaRows, 'impressions', 30);

        $posts = MetaPost::where('company_id', $companyId)->with('metaPage')->latest('posted_at')->limit(100)->get();
        $avgEr = $er->averageRate($posts);
        $erSeries = $posts->sortBy('posted_at')->values()->map(fn ($p) => [
            'label' => optional($p->posted_at)->format('d M'),
            'value' => ($p->followers_count ?? 0) > 0 ? round(($p->totalInteractions() / $p->followers_count) * 100, 2) : 0,
        ]);

        // ---- Google Ads ----
        $gaRows = GoogleAdsInsight::where('company_id', $companyId)->with('campaign')->get();
        $gaTotals = InsightStats::totals($gaRows, ['impressions', 'clicks', 'cost', 'conversions', 'conversion_value']);
        $gaTrend = InsightStats::dailySeries($gaRows->where('date', '>=', now()->subDays(29)), 'clicks', 30);
        $gaPeriod = InsightStats::periodTotals($gaRows, 'clicks', 30);
        $roas = $gaTotals['cost'] > 0 ? round($gaTotals['conversion_value'] / $gaTotals['cost'], 2) : 0;

        // ---- Google Analytics ----
        $analyticsRows = GaInsight::where('company_id', $companyId)->get();
        $analyticsTotals = InsightStats::totals($analyticsRows, ['users', 'new_users', 'sessions', 'pageviews']);
        $analyticsTrend = InsightStats::dailySeries($analyticsRows->where('date', '>=', now()->subDays(29)), 'users', 30);
        $analyticsPeriod = InsightStats::periodTotals($analyticsRows, 'users', 30);

        // ---- KPI terbaru ----
        $latest = [
            'content' => Content::where('company_id', $companyId)->latest()->value('created_at'),
            'meta' => MetaInsight::where('company_id', $companyId)->max('date'),
            'ads' => GoogleAdsInsight::where('company_id', $companyId)->max('date'),
            'ga' => GaInsight::where('company_id', $companyId)->max('date'),
        ];

        $latest = array_map(fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v) : null, $latest);

        return view('dashboard.index', compact(
            'contents',
            'contentStats',
            'metaTotals',
            'metaTrend',
            'metaPeriod',
            'avgEr',
            'erSeries',
            'posts',
            'gaTotals',
            'gaTrend',
            'gaPeriod',
            'roas',
            'analyticsTotals',
            'analyticsTrend',
            'analyticsPeriod',
            'latest'
        ));
    }
}