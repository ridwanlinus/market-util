<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Meta (Facebook / Instagram) API
    |--------------------------------------------------------------------------
    | Access token dengan izin pages_show_list, pages_read_engagement,
    | dan instagram_basic. Bisa juga diisi per-company via halaman Settings.
    */
    'meta' => [
        'access_token' => env('META_ACCESS_TOKEN', ''),
        'graph_version' => env('META_GRAPH_VERSION', 'v21.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Ads API
    |--------------------------------------------------------------------------
    */
    'google_ads' => [
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN', ''),
        'client_id' => env('GOOGLE_ADS_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET', ''),
        'customer_id' => env('GOOGLE_ADS_CUSTOMER_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Analytics 4 Data API
    |--------------------------------------------------------------------------
    */
    'google_analytics' => [
        'property_id' => env('GA4_PROPERTY_ID', ''),
        'client_id' => env('GA4_CLIENT_ID', ''),
        'client_secret' => env('GA4_CLIENT_SECRET', ''),
        'refresh_token' => env('GA4_REFRESH_TOKEN', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (opsional, untuk AI copywriting / AI image pada Content Studio)
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Benchmark Engagement Rate (industri social media)
    |--------------------------------------------------------------------------
    */
    'engagement_benchmarks' => [
        'excellent' => 5.0,
        'good' => 3.0,
        'average' => 1.5,
        'poor' => 0.5,
    ],
];