<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        $companyId = $company?->id;

        $saved = [
            'meta_access_token' => Setting::get('meta_access_token', $companyId, config('marketing.meta.access_token')),
            'ga4_property_id' => Setting::get('ga4_property_id', $companyId, config('marketing.google_analytics.property_id')),
            'ga4_client_id' => Setting::get('ga4_client_id', $companyId, config('marketing.google_analytics.client_id')),
            'ga4_client_secret' => Setting::get('ga4_client_secret', $companyId, config('marketing.google_analytics.client_secret')),
            'ga4_refresh_token' => Setting::get('ga4_refresh_token', $companyId, config('marketing.google_analytics.refresh_token')),
            'gads_developer_token' => Setting::get('gads_developer_token', $companyId, config('marketing.google_ads.developer_token')),
            'gads_client_id' => Setting::get('gads_client_id', $companyId, config('marketing.google_ads.client_id')),
            'gads_client_secret' => Setting::get('gads_client_secret', $companyId, config('marketing.google_ads.client_secret')),
            'gads_customer_id' => Setting::get('gads_customer_id', $companyId, config('marketing.google_ads.customer_id')),
            'openai_api_key' => Setting::get('openai_api_key', $companyId, config('marketing.openai.api_key')),
        ];

        return view('settings.index', compact('user', 'company', 'saved'));
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'company_name' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $user->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($user->company) {
            $user->company->update([
                'name' => $data['company_name'],
                'website' => $data['website'] ?? null,
                'industry' => $data['industry'] ?? null,
                'description' => $data['description'] ?? null,
            ]);
        }

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->update(['avatar' => $path]);
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updateConnections(Request $request)
    {
        $companyId = Auth::user()->company_id;

        $fields = [
            'meta_access_token',
            'ga4_property_id',
            'ga4_client_id',
            'ga4_client_secret',
            'ga4_refresh_token',
            'gads_developer_token',
            'gads_client_id',
            'gads_client_secret',
            'gads_customer_id',
            'openai_api_key',
        ];

        $secrets = [
            'meta_access_token',
            'ga4_client_secret',
            'ga4_refresh_token',
            'gads_developer_token',
            'gads_client_secret',
            'openai_api_key',
        ];

        foreach ($fields as $field) {
            $value = trim((string) $request->input($field, ''));
            Setting::put($field, $value === '' ? null : $value, $companyId, in_array($field, $secrets, true));
        }

        return back()->with('success', 'Koneksi API berhasil disimpan.');
    }
}