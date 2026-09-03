@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div x-data="{ tab: 'profile' }">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-6 fb-fade-up">
        <div>
            <h1 class="fb-title">Settings ⚙️</h1>
            <p class="fb-subtitle">Profil perusahaan & koneksi API marketing tools.</p>
        </div>
        <div class="fb-segmented">
            <button :class="tab === 'profile' && 'active'" @click="tab = 'profile'">Profil</button>
            <button :class="tab === 'connections' && 'active'" @click="tab = 'connections'">Koneksi API</button>
        </div>
    </div>

    <!-- ===== PROFIL ===== -->
    <div x-show="tab === 'profile'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="fb-card fb-card-pad lg:col-span-1 h-fit fb-fade-up" style="animation-delay:0.05s">
                <div class="flex flex-col items-center text-center">
                    <img src="{{ $user->avatarUrl() }}" class="w-24 h-24 rounded-full object-cover shadow-lg mb-4" alt="">
                    <div class="text-[17px] font-bold">{{ $user->name }}</div>
                    <div class="text-[13px] text-ios-gray">{{ $user->email }}</div>
                    <div class="mt-2"><span class="fb-badge {{ $user->isSuper() ? 'fb-badge-purple' : 'fb-badge-blue' }}">{{ $user->isSuper() ? 'Super Admin' : 'Company User' }}</span></div>
                </div>
            </div>

            <div class="fb-card fb-card-pad lg:col-span-2 fb-fade-up" style="animation-delay:0.1s">
                <div class="fb-card-title mb-4">Profil Perusahaan</div>
                <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="fb-field"><label class="fb-label">Nama Anda</label><input type="text" name="name" class="fb-input" value="{{ old('name', $user->name) }}" required></div>
                        <div class="fb-field"><label class="fb-label">No. HP</label><input type="text" name="phone" class="fb-input" value="{{ old('phone', $user->phone) }}" placeholder="08xx"></div>
                        <div class="fb-field"><label class="fb-label">Nama Perusahaan</label><input type="text" name="company_name" class="fb-input" value="{{ old('company_name', $company?->name) }}" required></div>
                        <div class="fb-field"><label class="fb-label">Industri</label><input type="text" name="industry" class="fb-input" value="{{ old('industry', $company?->industry) }}" placeholder="F&B, Fashion..."></div>
                        <div class="fb-field"><label class="fb-label">Website</label><input type="url" name="website" class="fb-input" value="{{ old('website', $company?->website) }}" placeholder="https://"></div>
                        <div class="fb-field"><label class="fb-label">Foto Profil</label><input type="file" name="avatar" accept="image/*" class="fb-input !py-2.5"></div>
                    </div>
                    <div class="fb-field"><label class="fb-label">Deskripsi Perusahaan</label><textarea name="description" class="fb-textarea" rows="3" placeholder="Tentang perusahaan Anda...">{{ old('description', $company?->description) }}</textarea></div>
                    <button class="fb-btn fb-btn-primary">Simpan Profil</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== KONEKSI API ===== -->
    <div x-show="tab === 'connections'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.05s">
                <div class="flex items-center gap-3 mb-1">
                    <div class="fb-kpi-icon bg-blue-50 text-ios-blue"><i data-lucide="facebook" class="w-5 h-5"></i></div>
                    <div class="fb-card-title">Meta Graph API</div>
                </div>
                <p class="fb-card-sub mb-4">Untuk sinkronisasi otomatis insight halaman. Izin: pages_show_list, pages_read_engagement.</p>
                <form method="POST" action="{{ route('settings.connections') }}">
                    @csrf @method('PUT')
                    <div class="fb-field">
                        <label class="fb-label">Access Token</label>
                        <input type="password" name="meta_access_token" class="fb-input" value="{{ $saved['meta_access_token'] }}" placeholder="EAAG...">
                    </div>
                    <button class="fb-btn fb-btn-primary fb-btn-sm">Simpan</button>
                </form>
            </div>

            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.1s">
                <div class="flex items-center gap-3 mb-1">
                    <div class="fb-kpi-icon bg-orange-50 text-ios-orange"><i data-lucide="search" class="w-5 h-5"></i></div>
                    <div class="fb-card-title">Google Ads API</div>
                </div>
                <p class="fb-card-sub mb-4">Kredensial Google Ads. Data juga bisa diinput manual / import CSV.</p>
                <form method="POST" action="{{ route('settings.connections') }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="fb-field"><label class="fb-label">Developer Token</label><input type="password" name="gads_developer_token" class="fb-input" value="{{ $saved['gads_developer_token'] }}"></div>
                        <div class="fb-field"><label class="fb-label">Customer ID</label><input type="text" name="gads_customer_id" class="fb-input" value="{{ $saved['gads_customer_id'] }}" placeholder="123-456-7890"></div>
                        <div class="fb-field"><label class="fb-label">Client ID</label><input type="text" name="gads_client_id" class="fb-input" value="{{ $saved['gads_client_id'] }}"></div>
                        <div class="fb-field"><label class="fb-label">Client Secret</label><input type="password" name="gads_client_secret" class="fb-input" value="{{ $saved['gads_client_secret'] }}"></div>
                    </div>
                    <button class="fb-btn fb-btn-primary fb-btn-sm">Simpan</button>
                </form>
            </div>

            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.15s">
                <div class="flex items-center gap-3 mb-1">
                    <div class="fb-kpi-icon bg-purple-50 text-ios-purple"><i data-lucide="bar-chart-3" class="w-5 h-5"></i></div>
                    <div class="fb-card-title">Google Analytics 4</div>
                </div>
                <p class="fb-card-sub mb-4">GA4 Data API (property id + OAuth). Data juga bisa diinput manual / import CSV.</p>
                <form method="POST" action="{{ route('settings.connections') }}">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-3">
                        <div class="fb-field"><label class="fb-label">Property ID</label><input type="text" name="ga4_property_id" class="fb-input" value="{{ $saved['ga4_property_id'] }}"></div>
                        <div class="fb-field"><label class="fb-label">Client ID</label><input type="text" name="ga4_client_id" class="fb-input" value="{{ $saved['ga4_client_id'] }}"></div>
                        <div class="fb-field"><label class="fb-label">Client Secret</label><input type="password" name="ga4_client_secret" class="fb-input" value="{{ $saved['ga4_client_secret'] }}"></div>
                        <div class="fb-field"><label class="fb-label">Refresh Token</label><input type="password" name="ga4_refresh_token" class="fb-input" value="{{ $saved['ga4_refresh_token'] }}"></div>
                    </div>
                    <button class="fb-btn fb-btn-primary fb-btn-sm">Simpan</button>
                </form>
            </div>

            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.2s">
                <div class="flex items-center gap-3 mb-1">
                    <div class="fb-kpi-icon bg-green-50 text-green-600"><i data-lucide="sparkles" class="w-5 h-5"></i></div>
                    <div class="fb-card-title">OpenAI (opsional)</div>
                </div>
                <p class="fb-card-sub mb-4">Untuk AI copywriting & AI image generation di Content Studio (mendatang).</p>
                <form method="POST" action="{{ route('settings.connections') }}">
                    @csrf @method('PUT')
                    <div class="fb-field">
                        <label class="fb-label">API Key</label>
                        <input type="password" name="openai_api_key" class="fb-input" value="{{ $saved['openai_api_key'] }}" placeholder="sk-...">
                    </div>
                    <button class="fb-btn fb-btn-primary fb-btn-sm">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection