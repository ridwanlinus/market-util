<?php

namespace Database\Seeders;

use App\Models\Calculation;
use App\Models\Company;
use App\Models\Content;
use App\Models\GaInsight;
use App\Models\GaProperty;
use App\Models\GoogleAdsCampaign;
use App\Models\GoogleAdsInsight;
use App\Models\MetaInsight;
use App\Models\MetaPage;
use App\Models\MetaPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        mt_srand(42);

        // ================= Perusahaan & Users =================
        $company = Company::create([
            'name' => 'Nusantara Kreatif',
            'website' => 'https://nusantarakeatif.id',
            'industry' => 'F&B & Digital Agency',
            'description' => 'Agen kreatif yang menangani branding, social media, dan paid ads untuk brand F&B di Indonesia.',
        ]);

        $super = User::create([
            'name' => 'Ridwan Admin',
            'email' => 'super@freebuff.test',
            'password' => Hash::make('password'),
            'role' => 'super',
        ]);

        $companyUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'company@freebuff.test',
            'password' => Hash::make('password'),
            'role' => 'company',
            'company_id' => $company->id,
            'phone' => '0812-3456-7890',
        ]);

        $company->update(['created_by' => $companyUser->id]);

        // ================= Content Studio (Tool 1) =================
        $this->seedContents($company, $companyUser, $super);

        // ================= Meta (Tool 2 & 3) =================
        $this->seedMeta($company);

        // ================= Google Ads (Tool 4) =================
        $this->seedGoogleAds($company);

        // ================= Google Analytics (Tool 5) =================
        $this->seedAnalytics($company);

        $this->command?->info('Seed selesai!');
        $this->command?->info('Company : company@freebuff.test / password');
        $this->command?->info('Super   : super@freebuff.test / password');
    }

    private function seedContents(Company $company, User $user, User $super): void
    {
        $items = [
            ['title' => 'Promo Ramadhan — Diskon 40%', 'type' => 'carousel', 'slides' => 3, 'status' => 'approved', 'platform' => 'Instagram', 'caption' => 'Sambut Ramadhan dengan promo spesial! 🎉\n\nDiskon hingga 40% untuk menu favoritmu. Periode 1–30 Maret.\n\n#Ramadhan #Promo #Foodie'],
            ['title' => 'Launching Menu Baru: Es Kopi Susu Gula Aren', 'type' => 'single', 'slides' => 1, 'status' => 'approved', 'platform' => 'Instagram', 'caption' => 'Baru! Es Kopi Susu Gula Aren ☕✨\nSegarnya bikin nagih. Coba sekarang!'],
            ['title' => 'Behind The Scene — Dapur Kami', 'type' => 'carousel', 'slides' => 4, 'status' => 'pending', 'platform' => 'Instagram', 'caption' => 'Penasaran proses pembuatan di dapur kami? 🤔\nSwipe untuk lihat!'],
            ['title' => 'Testimoni Pelanggan: Kepuasan No.1', 'type' => 'single', 'slides' => 1, 'status' => 'pending', 'platform' => 'Facebook', 'caption' => '"Rasanya juara! Bakal balik lagi." — @anita\n\nYuk coba dan rasakan sendiri! 💛'],
            ['title' => 'Flash Sale 12.12 — Siap-Siap!', 'type' => 'single', 'slides' => 1, 'status' => 'draft', 'platform' => 'Instagram', 'caption' => 'Flash sale terbesar tahun ini akan segera hadir! 🔥'],
            ['title' => 'Edukasi: 5 Fakta Kopi yang Belum Kamu Tahu', 'type' => 'carousel', 'slides' => 5, 'status' => 'rejected', 'platform' => 'Instagram', 'caption' => 'Fakta menarik seputar kopi ☕', 'approval_note' => 'Mohon perbaiki kontras teks pada slide 3 dan 4, terlalu sulit dibaca.'],
            ['title' => 'Grand Opening Cabang Kelapa Gading', 'type' => 'single', 'slides' => 1, 'status' => 'approved', 'platform' => 'Instagram', 'caption' => 'Kami buka cabang baru di Kelapa Gading! 🎊\nDatang dan dapatkan promo spesial.'],
            ['title' => 'Tips: Cara Menikmati Cold Brew', 'type' => 'carousel', 'slides' => 3, 'status' => 'draft', 'platform' => 'TikTok', 'caption' => 'Cold brew di rumah? Gampang banget!'],
        ];

        $colors = [
            ['#0A84FF', '#5856D6'], ['#FF9500', '#FF2D55'], ['#34C759', '#5AC8FA'],
            ['#AF52DE', '#0A84FF'], ['#FF2D55', '#AF52DE'], ['#1C1C1E', '#5856D6'],
            ['#FFCC00', '#FF9500'], ['#00C7BE', '#0A84FF'],
        ];

        foreach ($items as $i => $item) {
            $slides = [];
            for ($s = 0; $s < $item['slides']; $s++) {
                $g = $colors[($i + $s) % count($colors)];
                $slides[] = [
                    'background' => ['kind' => 'gradient', 'colors' => $g, 'angle' => 135, 'solid' => '#1C1C1E', 'image' => null],
                    'layers' => [
                        ['id' => 's' . $i . 'l1', 'type' => 'text', 'text' => $item['title'], 'x' => 90, 'y' => 420, 'width' => 900, 'fontSize' => 64, 'color' => '#FFFFFF', 'align' => 'center', 'bold' => true, 'font' => 'sans'],
                        ['id' => 's' . $i . 'l2', 'type' => 'text', 'text' => 'Slide ' . ($s + 1) . ' dari ' . $item['slides'], 'x' => 90, 'y' => 620, 'width' => 900, 'fontSize' => 32, 'color' => 'rgba(255,255,255,0.75)', 'align' => 'center', 'bold' => false, 'font' => 'sans'],
                    ],
                ];
            }

            $status = $item['status'];
            $content = Content::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'title' => $item['title'],
                'type' => $item['type'],
                'slides_count' => $item['slides'],
                'status' => $status,
                'design' => ['version' => 1, 'ratio' => '4:5', 'width' => 1080, 'height' => 1350, 'slides' => $slides],
                'caption' => $item['caption'] ?? null,
                'platform' => $item['platform'],
                'scheduled_at' => $status === 'draft' ? now()->addDays(3 + $i) : null,
                'approval_note' => $item['approval_note'] ?? null,
                'approved_by' => $status === 'approved' ? $super->id : null,
                'approved_at' => $status === 'approved' ? now()->subDays(rand(2, 14)) : null,
                'created_at' => now()->subDays(rand(3, 30)),
            ]);

            // Generate cover image via GD
            $paths = [];
            for ($s = 0; $s < $item['slides']; $s++) {
                $g = $colors[($i + $s) % count($colors)];
                $path = $this->generateCover($company, $item['title'], $g[0], $g[1]);
                $paths[] = $path;
            }
            $content->update([
                'files' => $paths,
                'cover_path' => $paths[0],
            ]);
        }
    }

    private function generateCover(Company $company, string $title, string $c1, string $c2): string
    {
        $img = imagecreatetruecolor(540, 675);
        [$r1, $g1, $b1] = array_map('hexdec', [substr($c1, 1, 2), substr($c1, 3, 2), substr($c1, 5, 2)]);
        [$r2, $g2, $b2] = array_map('hexdec', [substr($c2, 1, 2), substr($c2, 3, 2), substr($c2, 5, 2)]);

        for ($y = 0; $y < 675; $y++) {
            $t = $y / 675;
            $r = (int) ($r1 + ($r2 - $r1) * $t);
            $g = (int) ($g1 + ($g2 - $g1) * $t);
            $b = (int) ($b1 + ($b2 - $b1) * $t);
            imageline($img, 0, $y, 540, $y, imagecolorallocate($img, $r, $g, $b));
        }

        // decorative circles
        for ($i = 0; $i < 6; $i++) {
            $alpha = rand(10, 35);
            $col = imagecolorallocatealpha($img, 255, 255, 255, 127 - $alpha);
            imagefilledellipse($img, rand(0, 540), rand(0, 675), rand(80, 260), rand(80, 260), $col);
        }

        $dir = 'contents/' . $company->id;
        $name = 'seed-' . substr(md5($title . rand()), 0, 10) . '.png';

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($dir . '/' . $name, $png);

        return $dir . '/' . $name;
    }

    private function seedMeta(Company $company): void
    {
        $pages = [
            ['name' => 'Nusantara Kreatif Official', 'page_id' => '123456789', 'followers_count' => 18450],
            ['name' => 'Nusantara Kreatif Community', 'page_id' => '987654321', 'followers_count' => 8720],
        ];

        $models = [];
        foreach ($pages as $p) {
            $models[] = MetaPage::create([
                'company_id' => $company->id,
                'name' => $p['name'],
                'page_id' => $p['page_id'],
                'followers_count' => $p['followers_count'],
                'connected_at' => now()->subMonths(3),
            ]);
        }

        // Insight harian 60 hari
        foreach ($models as $pi => $page) {
            for ($d = 59; $d >= 0; $d--) {
                $date = now()->subDays($d);
                $base = $pi === 0 ? 6200 : 3100;
                $weekend = in_array($date->dayOfWeek, [5, 6], true) ? 1.25 : 1;
                $growth = 1 + (59 - $d) * 0.008;
                $noise = 0.85 + mt_rand(0, 30) / 100;

                $impressions = (int) round($base * $weekend * $growth * $noise);
                $reach = (int) round($impressions * (0.62 + mt_rand(0, 12) / 100));
                $engagement = (int) round($reach * (0.035 + mt_rand(0, 25) / 1000));
                $clicks = (int) round($impressions * (0.008 + mt_rand(0, 6) / 1000));
                $spend = round(rand(120000, 480000) / 100) * 100;

                MetaInsight::create([
                    'company_id' => $company->id,
                    'meta_page_id' => $page->id,
                    'date' => $date,
                    'impressions' => $impressions,
                    'reach' => $reach,
                    'engagement' => $engagement,
                    'likes' => (int) round($engagement * 0.55),
                    'comments' => (int) round($engagement * 0.18),
                    'shares' => (int) round($engagement * 0.14),
                    'saves' => (int) round($engagement * 0.13),
                    'clicks' => $clicks,
                    'spend' => $spend,
                    'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : null,
                ]);
            }
        }

        // Post konten (untuk Engagement Rate per konten)
        $postIdeas = [
            'Rekomendasi menu baru bulan ini 😋',
            'Tips hemat ala anak kos 💡',
            'Giveaway merch spesial! 🎁',
            'Kisah di balik logo kami',
            'Resep rahasia saus sambal 🌶️',
            'Jam buka saat libur panjang',
            'Kolaborasi dengan content creator',
            'Behind the scene photoshoot 📸',
            'Kuis mingguan: tebak menu!',
            'Review pelanggan setia 💛',
            'Promo buy 1 get 1 akhir pekan',
            'Perkenalan tim baru di dapur',
            'Cara kami memilih bahan baku',
            'Voucher gratis ongkir!',
        ];

        foreach ($models as $pi => $page) {
            foreach ($postIdeas as $i => $idea) {
                $date = now()->subDays(rand(1, 55));
                $followers = $page->followers_count;
                $likes = (int) round($followers * (0.012 + mt_rand(0, 30) / 1000));
                $comments = (int) round($likes * (0.12 + mt_rand(0, 20) / 100));
                $shares = (int) round($likes * (0.08 + mt_rand(0, 18) / 100));
                $saves = (int) round($likes * (0.06 + mt_rand(0, 16) / 100));

                MetaPost::create([
                    'company_id' => $company->id,
                    'meta_page_id' => $page->id,
                    'post_id' => 'post-' . $page->id . '-' . ($i + 1),
                    'kind' => 'post',
                    'message' => $idea,
                    'posted_at' => $date,
                    'impressions' => (int) round($followers * (0.3 + mt_rand(0, 20) / 100)),
                    'reach' => (int) round($followers * (0.22 + mt_rand(0, 15) / 100)),
                    'likes' => $likes,
                    'comments' => $comments,
                    'shares' => $shares,
                    'saves' => $saves,
                    'video_views' => rand(0, 8000),
                    'link_clicks' => rand(20, 500),
                    'followers_count' => $followers,
                    'spend' => $pi === 1 ? rand(150000, 700000) : null,
                ]);
            }
        }

        // Kalkulasi ER tersimpan
        Calculation::create([
            'company_id' => $company->id,
            'user_id' => $company->users()->first()?->id,
            'name' => 'Rata-rata ER — Post Promo',
            'kind' => 'engagement_rate',
            'inputs' => ['likes' => 542, 'comments' => 87, 'shares' => 64, 'saves' => 41, 'followers' => 18450],
            'result' => round(((542 + 87 + 64 + 41) / 18450) * 100, 2),
        ]);
        Calculation::create([
            'company_id' => $company->id,
            'user_id' => $company->users()->first()?->id,
            'name' => 'Konten Giveaway — ER Tertinggi',
            'kind' => 'engagement_rate',
            'inputs' => ['likes' => 1204, 'comments' => 388, 'shares' => 217, 'saves' => 96, 'followers' => 18450],
            'result' => round(((1204 + 388 + 217 + 96) / 18450) * 100, 2),
        ]);
    }

    private function seedGoogleAds(Company $company): void
    {
        $campaigns = [
            ['name' => 'Search — Brand', 'status' => 'active'],
            ['name' => 'Search — Generic', 'status' => 'active'],
            ['name' => 'Display — Retargeting', 'status' => 'paused'],
        ];

        $models = [];
        foreach ($campaigns as $c) {
            $models[] = GoogleAdsCampaign::create([
                'company_id' => $company->id,
                'name' => $c['name'],
                'campaign_id' => 'gads-' . rand(1000000000, 9999999999),
                'status' => $c['status'],
            ]);
        }

        $profiles = [
            ['imp' => 3800, 'clicks' => 190, 'cost' => 480000, 'conv' => 14, 'val' => 2600000],
            ['imp' => 7200, 'clicks' => 260, 'cost' => 870000, 'conv' => 9, 'val' => 1900000],
            ['imp' => 9500, 'clicks' => 110, 'cost' => 320000, 'conv' => 4, 'val' => 640000],
        ];

        foreach ($models as $i => $campaign) {
            $p = $profiles[$i];
            for ($d = 59; $d >= 0; $d--) {
                $date = now()->subDays($d);
                $weekend = in_array($date->dayOfWeek, [5, 6], true) ? 0.8 : 1;
                $noise = 0.8 + mt_rand(0, 40) / 100;

                $impressions = (int) round($p['imp'] * $weekend * $noise);
                $clicks = (int) round($p['clicks'] * $weekend * $noise);
                $cost = round(($p['cost'] * $weekend * $noise) / 100) * 100;
                $conversions = max(0, (int) round($p['conv'] * $weekend * $noise));
                $value = $conversions > 0 ? round($p['val'] * $weekend * ($noise + 0.2)) : 0;

                GoogleAdsInsight::create([
                    'company_id' => $company->id,
                    'campaign_id' => $campaign->id,
                    'date' => $date,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 4) : null,
                    'cpc' => $clicks > 0 ? round($cost / $clicks, 2) : null,
                    'cost' => $cost,
                    'conversions' => $conversions,
                    'conversion_value' => $value,
                    'roas' => $cost > 0 ? round($value / $cost, 4) : null,
                ]);
            }
        }
    }

    private function seedAnalytics(Company $company): void
    {
        $property = GaProperty::create([
            'company_id' => $company->id,
            'name' => 'Website Utama',
            'property_id' => '1234567890',
            'website' => 'https://nusantarakeatif.id',
        ]);

        $channels = [
            ['name' => 'Organic Search', 'users' => 4210],
            ['name' => 'Direct', 'users' => 1890],
            ['name' => 'Instagram', 'users' => 2320],
            ['name' => 'Facebook', 'users' => 1150],
            ['name' => 'Referral', 'users' => 640],
            ['name' => 'Email', 'users' => 310],
        ];

        $topPages = [
            ['page' => '/', 'views' => 8420],
            ['page' => '/menu', 'views' => 6210],
            ['page' => '/promo', 'views' => 4980],
            ['page' => '/tentang-kami', 'views' => 3150],
            ['page' => '/blog/es-kopi-susu', 'views' => 2870],
            ['page' => '/kontak', 'views' => 2140],
            ['page' => '/karir', 'views' => 1280],
            ['page' => '/blog/cold-brew', 'views' => 980],
        ];

        for ($d = 59; $d >= 0; $d--) {
            $date = now()->subDays($d);
            $weekend = in_array($date->dayOfWeek, [5, 6], true) ? 1.18 : 1;
            $growth = 1 + (59 - $d) * 0.006;
            $noise = 0.85 + mt_rand(0, 30) / 100;

            $users = (int) round(380 * $weekend * $growth * $noise);
            $newUsers = (int) round($users * 0.58);
            $sessions = (int) round($users * 1.35);
            $pageviews = (int) round($sessions * 3.1);
            $duration = round(142 + mt_rand(0, 60) + $users * 0.01, 2);
            $bounce = round(38 + mt_rand(0, 18), 2);

            GaInsight::create([
                'company_id' => $company->id,
                'property_id' => $property->id,
                'date' => $date,
                'users' => $users,
                'new_users' => $newUsers,
                'sessions' => $sessions,
                'pageviews' => $pageviews,
                'avg_session_duration' => $duration,
                'bounce_rate' => $bounce,
                'top_pages' => $topPages,
                'channels' => $channels,
            ]);
        }
    }
}