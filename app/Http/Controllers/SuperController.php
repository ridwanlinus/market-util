<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Content;
use Illuminate\Http\Request;

class SuperController extends Controller
{
    public function index()
    {
        $pending = Content::with(['company', 'user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        $stats = [
            'companies' => Company::count(),
            'users' => \App\Models\User::count(),
            'contents' => Content::count(),
            'pending' => Content::where('status', 'pending')->count(),
            'approved' => Content::where('status', 'approved')->count(),
            'rejected' => Content::where('status', 'rejected')->count(),
        ];

        $recent = Content::with(['company', 'user'])->latest()->limit(8)->get();

        return view('super.index', compact('pending', 'stats', 'recent'));
    }

    public function approve(Request $request, Content $content)
    {
        $data = $request->validate([
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $content->update([
            'status' => 'approved',
            'approval_note' => $data['note'] ?? null,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', "Konten \"{$content->title}\" disetujui.");
    }

    public function reject(Request $request, Content $content)
    {
        $data = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $content->update([
            'status' => 'rejected',
            'approval_note' => $data['note'],
        ]);

        return back()->with('success', "Konten \"{$content->title}\" ditolak.");
    }

    public function companies()
    {
        $companies = Company::withCount(['users', 'contents'])
            ->withCount(['metaPages', 'googleAdsCampaigns', 'gaProperties'])
            ->orderByDesc('contents_count')
            ->get();

        $summary = $companies->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'website' => $c->website,
            'industry' => $c->industry,
            'users' => $c->users_count,
            'contents' => $c->contents_count,
            'meta_pages' => $c->meta_pages_count,
            'ads_campaigns' => $c->google_ads_campaigns_count,
            'ga_properties' => $c->ga_properties_count,
            'pending' => Content::where('company_id', $c->id)->where('status', 'pending')->count(),
            'approved' => Content::where('company_id', $c->id)->where('status', 'approved')->count(),
        ]);

        return view('super.companies', compact('companies', 'summary'));
    }
}