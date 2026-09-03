<?php

namespace App\Http\Controllers;

use App\Models\GoogleAdsCampaign;
use App\Models\GoogleAdsInsight;
use App\Services\CsvImportService;
use App\Services\InsightStats;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class GoogleAdsController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId();

        $from = Carbon::parse($request->get('from', now()->subDays(29)->format('Y-m-d')))->startOfDay();
        $to = Carbon::parse($request->get('to', now()->format('Y-m-d')))->endOfDay();

        $query = GoogleAdsInsight::where('company_id', $companyId)
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        $rows = $query->with('campaign')->orderBy('date')->get();

        $totals = InsightStats::totals($rows, ['impressions', 'clicks', 'cost', 'conversions', 'conversion_value']);
        $totals['ctr'] = $totals['impressions'] > 0 ? round(($totals['clicks'] / $totals['impressions']) * 100, 2) : 0;
        $totals['cpc'] = $totals['clicks'] > 0 ? round($totals['cost'] / $totals['clicks'], 2) : 0;
        $totals['roas'] = $totals['cost'] > 0 ? round($totals['conversion_value'] / $totals['cost'], 2) : 0;

        return view('tools.google-ads.index', [
            'rows' => $rows,
            'campaigns' => GoogleAdsCampaign::where('company_id', $companyId)->get(),
            'selectedCampaign' => $request->get('campaign_id'),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'totals' => $totals,
            'days' => $from->diffInDays($to) + 1,
            'impressionsSeries' => InsightStats::dailySeries($rows, 'impressions', $from->diffInDays($to) + 1),
            'clicksSeries' => InsightStats::dailySeries($rows, 'clicks', $from->diffInDays($to) + 1),
            'costSeries' => InsightStats::dailySeries($rows, 'cost', $from->diffInDays($to) + 1),
            'conversionsSeries' => InsightStats::dailySeries($rows, 'conversions', $from->diffInDays($to) + 1),
            'roasSeries' => InsightStats::dailySeries($rows, 'roas', $from->diffInDays($to) + 1),
            'byCampaign' => $this->prettyGroup($rows, 'campaign_id', 'cost'),
            'campaignClicks' => $this->prettyGroup($rows, 'campaign_id', 'clicks'),
        ]);
    }

    private function prettyGroup($rows, string $groupField, string $metric): array
    {
        $grouped = InsightStats::groupBy($rows, $groupField, $metric);

        if ($groupField === 'campaign_id') {
            $campaigns = GoogleAdsCampaign::where('company_id', $this->companyId())->pluck('name', 'id');
            $grouped['labels'] = array_map(
                fn ($id) => $campaigns[(int) $id] ?? 'Campaign #' . $id,
                $grouped['labels']
            );
        }

        return $grouped;
    }

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'campaign_id' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'in:active,paused,removed'],
        ]);

        GoogleAdsCampaign::create([
            'company_id' => $this->companyId(),
            'name' => $data['name'],
            'campaign_id' => $data['campaign_id'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return back()->with('success', 'Campaign Google Ads ditambahkan.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'campaign_id' => ['required', 'exists:google_ads_campaigns,id'],
            'impressions' => ['nullable', 'numeric', 'min:0'],
            'clicks' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'conversions' => ['nullable', 'numeric', 'min:0'],
            'conversion_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $campaign = GoogleAdsCampaign::where('company_id', $this->companyId())->findOrFail($data['campaign_id']);
        $impressions = (int) ($data['impressions'] ?? 0);
        $clicks = (int) ($data['clicks'] ?? 0);
        $cost = (float) ($data['cost'] ?? 0);
        $value = (float) ($data['conversion_value'] ?? 0);

        GoogleAdsInsight::updateOrCreate(
            ['company_id' => $this->companyId(), 'campaign_id' => $campaign->id, 'date' => $data['date']],
            [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : null,
                'cpc' => $clicks > 0 ? round($cost / $clicks, 2) : null,
                'cost' => $cost,
                'conversions' => $data['conversions'] ?? 0,
                'conversion_value' => $value,
                'roas' => $cost > 0 ? round($value / $cost, 4) : null,
            ]
        );

        return back()->with('success', 'Insight Google Ads disimpan.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            'campaign_id' => ['required', 'exists:google_ads_campaigns,id'],
        ]);

        $campaign = GoogleAdsCampaign::where('company_id', $this->companyId())->findOrFail($request->campaign_id);
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
            $cost = $csv->numeric($row, 'cost');
            $value = $csv->numeric($row, 'conversion_value');

            GoogleAdsInsight::updateOrCreate(
                ['company_id' => $this->companyId(), 'campaign_id' => $campaign->id, 'date' => $date],
                [
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : null,
                    'cpc' => $clicks > 0 ? round($cost / $clicks, 2) : null,
                    'cost' => $cost,
                    'conversions' => $csv->int($row, 'conversions'),
                    'conversion_value' => $value,
                    'roas' => $cost > 0 ? round($value / $cost, 4) : null,
                ]
            );
            $imported++;
        }

        return back()->with('success', "{$imported} baris insight Google Ads berhasil diimport.");
    }

    public function destroy(GoogleAdsInsight $insight)
    {
        abort_unless($insight->company_id === $this->companyId(), 403);
        $insight->delete();

        return back()->with('success', 'Data insight dihapus.');
    }

    public function destroyCampaign(GoogleAdsCampaign $campaign)
    {
        abort_unless($campaign->company_id === $this->companyId(), 403);
        $campaign->delete();

        return back()->with('success', 'Campaign dihapus.');
    }
}