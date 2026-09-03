<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\MetaPost;
use App\Services\EngagementRateService;
use Illuminate\Http\Request;

class EngagementController extends Controller
{
    public function index(EngagementRateService $er)
    {
        $companyId = $this->companyId();

        $posts = MetaPost::where('company_id', $companyId)
            ->with('metaPage')
            ->orderByDesc('posted_at')
            ->limit(30)
            ->get();

        $saved = Calculation::where('company_id', $companyId)
            ->with('metaPost')
            ->latest()
            ->limit(50)
            ->get();

        $avgEr = $er->averageRate($posts);

        $erSeries = $posts->sortBy('posted_at')->values()->map(fn ($p) => [
            'label' => optional($p->posted_at)->format('d M'),
            'value' => ($p->followers_count ?? 0) > 0 ? round(($p->totalInteractions() / $p->followers_count) * 100, 2) : 0,
        ]);

        $interactionTotals = [
            'likes' => $posts->sum('likes'),
            'comments' => $posts->sum('comments'),
            'shares' => $posts->sum('shares'),
            'saves' => $posts->sum('saves'),
        ];

        return view('tools.engagement.index', compact('posts', 'saved', 'avgEr', 'erSeries', 'interactionTotals'));
    }

    public function calculate(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'likes' => ['nullable', 'numeric', 'min:0'],
            'comments' => ['nullable', 'numeric', 'min:0'],
            'shares' => ['nullable', 'numeric', 'min:0'],
            'saves' => ['nullable', 'numeric', 'min:0'],
            'followers' => ['required', 'numeric', 'min:1'],
            'post_id' => ['nullable', 'exists:meta_posts,id'],
        ]);

        $postId = $data['post_id'] ?? null;

        // Bila dipilih dari post, ambil interaksi dari post tersebut.
        if ($postId) {
            $post = MetaPost::where('company_id', $this->companyId())->findOrFail($postId);
            $inputs = [
                'likes' => $post->likes,
                'comments' => $post->comments,
                'shares' => $post->shares,
                'saves' => $post->saves,
                'followers' => $post->followers_count ?? $data['followers'],
            ];
        } else {
            $inputs = [
                'likes' => (int) ($data['likes'] ?? 0),
                'comments' => (int) ($data['comments'] ?? 0),
                'shares' => (int) ($data['shares'] ?? 0),
                'saves' => (int) ($data['saves'] ?? 0),
                'followers' => (float) $data['followers'],
            ];
        }

        $result = app(EngagementRateService::class)->calculate($inputs, $inputs['followers']);

        $calculation = Calculation::create([
            'company_id' => $this->companyId(),
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'kind' => 'engagement_rate',
            'inputs' => $inputs,
            'result' => $result['rate'],
            'meta_post_id' => $postId,
        ]);

        return back()->with([
            'success' => 'Perhitungan disimpan.',
            'last_result' => $result + ['id' => $calculation->id],
        ]);
    }

    public function destroy(Calculation $calculation)
    {
        abort_unless($calculation->company_id === $this->companyId(), 403);
        $calculation->delete();

        return back()->with('success', 'Perhitungan dihapus.');
    }
}