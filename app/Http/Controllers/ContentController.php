<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = Content::where('company_id', $this->companyId())->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $contents = $query->latest()->paginate(12)->withQueryString();

        return view('tools.content.index', [
            'contents' => $contents,
            'stats' => Content::where('company_id', $this->companyId())
                ->selectRaw("count(*) as total, sum(case when status='approved' then 1 else 0 end) as approved, sum(case when status='pending' then 1 else 0 end) as pending")
                ->first(),
        ]);
    }

    public function create(Request $request)
    {
        $content = null;

        if ($request->filled('edit')) {
            $content = Content::where('company_id', $this->companyId())->find($request->edit);

            if ($content && ! in_array($content->status, ['draft', 'rejected'], true)) {
                $content = null;
            }
        }

        return view('tools.content.editor', compact('content'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:single,carousel'],
            'design' => ['required', 'json'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['string'],
            'cover' => ['nullable', 'string'],
            'caption' => ['nullable', 'string', 'max:2200'],
            'platform' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $files = $data['files'] ?? [];

        $content = Content::create([
            'company_id' => $this->companyId(),
            'user_id' => Auth::id(),
            'title' => $data['title'],
            'type' => $data['type'],
            'slides_count' => count($files),
            'status' => 'draft',
            'design' => json_decode($data['design'], true),
            'files' => $files,
            'cover_path' => $data['cover'] ?? $files[0] ?? null,
            'caption' => $data['caption'] ?? null,
            'platform' => $data['platform'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Konten berhasil disimpan.',
            'content' => $content,
            'redirect' => route('tools.content.show', $content),
        ]);
    }

    public function show(Content $content)
    {
        $this->authorizeCompany($content);

        return view('tools.content.show', compact('content'));
    }

    public function update(Request $request, Content $content)
    {
        $this->authorizeOwner($content);

        if (! in_array($content->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Konten yang sudah diproses tidak bisa diubah.');
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:single,carousel'],
            'design' => ['required', 'json'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['string'],
            'cover' => ['nullable', 'string'],
            'caption' => ['nullable', 'string', 'max:2200'],
            'platform' => ['nullable', 'string', 'max:100'],
            'scheduled_at' => ['nullable', 'date'],
        ]);

        $files = $data['files'];

        $content->update([
            'title' => $data['title'],
            'type' => $data['type'],
            'slides_count' => count($files),
            'status' => 'draft',
            'design' => json_decode($data['design'], true),
            'files' => $files,
            'cover_path' => $data['cover'] ?? $files[0],
            'caption' => $data['caption'] ?? null,
            'platform' => $data['platform'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'approval_note' => null,
        ]);

        return response()->json(['ok' => true, 'message' => 'Konten diperbarui.', 'redirect' => route('tools.content.show', $content)]);
    }

    public function submit(Content $content)
    {
        $this->authorizeOwner($content);

        if ($content->status === 'draft' || $content->status === 'rejected') {
            $content->update(['status' => 'pending']);
        }

        return back()->with('success', 'Konten dikirim untuk approval Super Admin.');
    }

    public function destroy(Content $content)
    {
        $this->authorizeOwner($content);

        $content->delete();

        return back()->with('success', 'Konten dihapus.');
    }

    private function authorizeCompany(Content $content): void
    {
        // Super Admin boleh melihat preview konten dari semua perusahaan.
        if (auth()->user()->isSuper()) {
            return;
        }

        abort_unless($content->company_id === $this->companyId(), 403);
    }

    private function authorizeOwner(Content $content): void
    {
        abort_unless($content->company_id === $this->companyId(), 403);
    }
}