<?php

namespace App\Http\Controllers;

use App\Models\GaInsight;
use App\Models\GaProperty;
use App\Services\CsvImportService;
use App\Services\InsightStats;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId();

        $from = Carbon::parse($request->get('from', now()->subDays(29)->format('Y-m-d')))->startOfDay();
        $to = Carbon::parse($request->get('to', now()->format('Y-m-d')))->endOfDay();

        $query = GaInsight::where('company_id', $companyId)
            ->whereBetween('date', [$from, $to]);

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        $rows = $query->with('property')->orderBy('date')->get();

        $totals = InsightStats::totals($rows, ['users', 'new_users', 'sessions', 'pageviews']);
        $totals['avg_session_duration'] = round($rows->avg('avg_session_duration') ?? 0, 2);
        $totals['bounce_rate'] = round($rows->avg('bounce_rate') ?? 0, 2);
        $totals['pages_per_session'] = $totals['sessions'] > 0 ? round($totals['pageviews'] / $totals['sessions'], 2) : 0;

        $days = $from->diffInDays($to) + 1;

        // Top pages & channels: agregasi dari record terakhir (JSON) + akumulasi sederhana.
        $lastRecord = $rows->last();
        $channels = $lastRecord?->channels ?? [];
        $topPages = $lastRecord?->top_pages ?? [];

        return view('tools.analytics.index', [
            'rows' => $rows,
            'properties' => GaProperty::where('company_id', $companyId)->get(),
            'selectedProperty' => $request->get('property_id'),
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'totals' => $totals,
            'days' => $days,
            'usersSeries' => InsightStats::dailySeries($rows, 'users', $days),
            'sessionsSeries' => InsightStats::dailySeries($rows, 'sessions', $days),
            'pageviewsSeries' => InsightStats::dailySeries($rows, 'pageviews', $days),
            'newUsersSeries' => InsightStats::dailySeries($rows, 'new_users', $days),
            'channels' => $channels,
            'topPages' => $topPages,
        ]);
    }

    public function storeProperty(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'property_id' => ['nullable', 'string', 'max:64'],
            'website' => ['nullable', 'url', 'max:255'],
        ]);

        GaProperty::create([
            'company_id' => $this->companyId(),
            'name' => $data['name'],
            'property_id' => $data['property_id'] ?? null,
            'website' => $data['website'] ?? null,
        ]);

        return back()->with('success', 'Properti GA4 ditambahkan.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => ['required', 'date'],
            'property_id' => ['required', 'exists:ga_properties,id'],
            'users' => ['nullable', 'numeric', 'min:0'],
            'new_users' => ['nullable', 'numeric', 'min:0'],
            'sessions' => ['nullable', 'numeric', 'min:0'],
            'pageviews' => ['nullable', 'numeric', 'min:0'],
            'avg_session_duration' => ['nullable', 'numeric', 'min:0'],
            'bounce_rate' => ['nullable', 'numeric', 'min:0'],
            'channels' => ['nullable', 'json'],
            'top_pages' => ['nullable', 'json'],
        ]);

        $property = GaProperty::where('company_id', $this->companyId())->findOrFail($data['property_id']);

        GaInsight::updateOrCreate(
            ['company_id' => $this->companyId(), 'property_id' => $property->id, 'date' => $data['date']],
            [
                'users' => $data['users'] ?? 0,
                'new_users' => $data['new_users'] ?? 0,
                'sessions' => $data['sessions'] ?? 0,
                'pageviews' => $data['pageviews'] ?? 0,
                'avg_session_duration' => $data['avg_session_duration'] ?? 0,
                'bounce_rate' => $data['bounce_rate'] ?? 0,
                'channels' => $data['channels'] ? json_decode($data['channels'], true) : null,
                'top_pages' => $data['top_pages'] ? json_decode($data['top_pages'], true) : null,
            ]
        );

        return back()->with('success', 'Insight Google Analytics disimpan.');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv' => ['required', 'file', 'mimes:csv,txt'],
            'property_id' => ['required', 'exists:ga_properties,id'],
        ]);

        $property = GaProperty::where('company_id', $this->companyId())->findOrFail($request->property_id);
        $csv = new CsvImportService();
        $rows = $csv->parse($request->file('csv')->get());

        $imported = 0;
        foreach ($rows as $row) {
            $date = $csv->date($row, 'date', now()->format('Y-m-d'));
            if (! $date) {
                continue;
            }

            GaInsight::updateOrCreate(
                ['company_id' => $this->companyId(), 'property_id' => $property->id, 'date' => $date],
                [
                    'users' => $csv->int($row, 'users'),
                    'new_users' => $csv->int($row, 'new_users'),
                    'sessions' => $csv->int($row, 'sessions'),
                    'pageviews' => $csv->int($row, 'pageviews'),
                    'avg_session_duration' => $csv->numeric($row, 'avg_session_duration'),
                    'bounce_rate' => $csv->numeric($row, 'bounce_rate'),
                    'channels' => isset($row['channels']) && $row['channels'] !== '' ? json_decode($row['channels'], true) : null,
                    'top_pages' => isset($row['top_pages']) && $row['top_pages'] !== '' ? json_decode($row['top_pages'], true) : null,
                ]
            );
            $imported++;
        }

        return back()->with('success', "{$imported} baris insight Google Analytics berhasil diimport.");
    }

    public function destroy(GaInsight $insight)
    {
        abort_unless($insight->company_id === $this->companyId(), 403);
        $insight->delete();

        return back()->with('success', 'Data insight dihapus.');
    }

    public function destroyProperty(GaProperty $property)
    {
        abort_unless($property->company_id === $this->companyId(), 403);
        $property->delete();

        return back()->with('success', 'Properti dihapus.');
    }
}