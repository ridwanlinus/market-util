@extends('layouts.app')

@section('title', 'Editor Konten')

@section('content')
    @php
        $editContent = $content ?? null;
        $initialDesign = $editContent?->design;
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4 mb-5 fb-fade-up">
        <div>
            <a href="{{ route('tools.content.index') }}" class="text-[13px] font-semibold text-ios-blue mb-1 inline-flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali
            </a>
            <h1 class="fb-title mt-1">{{ $editContent ? 'Edit: ' . $editContent->title : 'Buat Konten Baru' }}</h1>
            <p class="fb-subtitle">Canvas 1080×1350 (rasio 4:5) — single image atau carousel multi-slide.</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="fb-segmented" id="type-seg">
                <button data-type="single" class="active"><i data-lucide="image" class="w-3.5 h-3.5 inline -mt-0.5 mr-1"></i>Single</button>
                <button data-type="carousel"><i data-lucide="images" class="w-3.5 h-3.5 inline -mt-0.5 mr-1"></i>Carousel</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_320px] gap-5">
        <!-- ============ Canvas area ============ -->
        <div>
            <div class="fb-card p-4 fb-fade-up" style="animation-delay:0.05s">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-1" id="toolbar">
                        <button class="fb-editor-tool" data-tool="text" title="Tambah teks"><i data-lucide="type" class="w-5 h-5"></i></button>
                        <button class="fb-editor-tool" data-tool="image" title="Tambah gambar"><i data-lucide="image-plus" class="w-5 h-5"></i></button>
                        <button class="fb-editor-tool" data-tool="button" title="Tambah tombol CTA"><i data-lucide="mouse-pointer-click" class="w-5 h-5"></i></button>
                        <div class="w-px h-6 bg-black/10 mx-1"></div>
                        <button class="fb-editor-tool" data-tool="front" title="Ke depan"><i data-lucide="bring-to-front" class="w-5 h-5"></i></button>
                        <button class="fb-editor-tool" data-tool="back" title="Ke belakang"><i data-lucide="send-to-back" class="w-5 h-5"></i></button>
                        <button class="fb-editor-tool" data-tool="duplicate" title="Duplikat"><i data-lucide="copy" class="w-5 h-5"></i></button>
                        <button class="fb-editor-tool" data-tool="delete" title="Hapus layer"><i data-lucide="trash-2" class="w-5 h-5"></i></button>
                    </div>
                    <span class="text-[12px] text-ios-gray font-medium hidden sm:block" id="canvas-hint">Klik layer untuk pilih · drag untuk geser</span>
                </div>

                <div class="flex justify-center bg-[#0d0d12] rounded-2xl py-6 relative overflow-hidden">
                    <canvas id="editor-canvas" class="fb-editor-stage" style="max-width:360px; aspect-ratio:4/5; cursor:crosshair;"></canvas>
                </div>

                <!-- Slides strip -->
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-[13px] font-semibold text-ios-gray">Slide Carousel</div>
                        <button id="btn-add-slide" class="fb-btn fb-btn-ghost fb-btn-xs"><i data-lucide="plus" class="w-3.5 h-3.5"></i> Tambah Slide</button>
                    </div>
                    <div id="slides-strip" class="flex gap-3 overflow-x-auto pb-2"></div>
                </div>
            </div>

            <!-- Save bar -->
            <div class="fb-card p-4 mt-4 fb-fade-up" style="animation-delay:0.1s">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="fb-field !mb-0">
                        <label class="fb-label">Judul Konten *</label>
                        <input type="text" id="content-title" class="fb-input" placeholder="cth: Promo Akhir Tahun — Flash Sale" value="{{ $editContent?->title ?? old('title') }}">
                    </div>
                    <div class="fb-field !mb-0">
                        <label class="fb-label">Platform</label>
                        <input type="text" id="content-platform" class="fb-input" placeholder="Instagram / Facebook / TikTok" value="{{ $editContent?->platform ?? '' }}">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="fb-label">Caption</label>
                    <textarea id="content-caption" class="fb-textarea" rows="2" placeholder="Tulis caption konten di sini...">{{ $editContent?->caption ?? '' }}</textarea>
                </div>
                <div class="mt-3">
                    <label class="fb-label">Jadwal Publikasi (opsional)</label>
                    <input type="datetime-local" id="content-schedule" class="fb-input" value="{{ $editContent?->scheduled_at ? $editContent->scheduled_at->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-black/5">
                    <button id="btn-save" class="fb-btn fb-btn-primary">💾 Simpan Draft</button>
                    <button id="btn-save-submit" class="fb-btn fb-btn-green">🚀 Simpan & Kirim Approval</button>
                    <button id="btn-reset" class="fb-btn fb-btn-ghost ml-auto">Reset</button>
                </div>
            </div>
        </div>

        <!-- ============ Inspector ============ -->
        <div class="space-y-4">
            <!-- Templates -->
            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.08s">
                <div class="fb-card-title mb-3">Template Cepat</div>
                <div class="grid grid-cols-3 gap-2" id="templates-grid"></div>
            </div>

            <!-- Background -->
            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.12s">
                <div class="fb-card-title mb-3">Background Slide</div>
                <div class="fb-segmented w-full" id="bg-type">
                    <button data-bg="gradient" class="active flex-1">Gradient</button>
                    <button data-bg="solid" class="flex-1">Warna</button>
                    <button data-bg="image" class="flex-1">Gambar</button>
                </div>
                <div id="bg-controls" class="mt-3"></div>
            </div>

            <!-- Layer inspector -->
            <div class="fb-card fb-card-pad fb-fade-up" style="animation-delay:0.16s">
                <div class="fb-card-title mb-1">Inspector</div>
                <div class="fb-card-sub mb-3">Pilih layer pada canvas</div>
                <div id="layer-inspector" class="min-h-[160px]">
                    <p class="text-[13px] text-ios-gray">Belum ada layer terpilih.</p>
                </div>
            </div>
        </div>
    </div>

    <input type="file" id="file-input" accept="image/*" class="hidden">
@endsection

@section('scripts')
<script>
    window.EDITOR_CONFIG = {
        editId: {{ $editContent?->id ?? 'null' }},
        initialDesign: @json($initialDesign),
        initialTitle: @json($editContent?->title ?? ''),
        initialCaption: @json($editContent?->caption ?? ''),
        initialPlatform: @json($editContent?->platform ?? ''),
        initialType: @json($editContent?->type ?? 'single'),
        initialFiles: @json($editContent?->files ?? []),
        storeUrl: @json(route('tools.content.store')),
        updateUrl: @json($editContent ? route('tools.content.update', $editContent) : null),
        uploadUrl: @json(route('uploads.store')),
        submitUrl: null,
    };
</script>
<script src="{{ asset('js/editor.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        FBEditor.init(window.EDITOR_CONFIG);
    });
</script>
@endsection