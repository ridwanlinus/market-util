/* ============================================================
   Freebuff Content Studio — Canvas Editor (1080x1350, 4:5)
   ============================================================ */
(function () {
  'use strict';

  var W = 1080, H = 1350;
  var FONT_STACK = '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", Roboto, sans-serif';
  var FONTS = {
    sans: FONT_STACK,
    rounded: '"SF Pro Rounded", "Nunito", -apple-system, sans-serif',
    serif: 'Georgia, "Times New Roman", serif',
    mono: '"SF Mono", "JetBrains Mono", Menlo, monospace',
  };
  var GRADIENTS = [
    ['#0A84FF', '#5856D6'],
    ['#FF9500', '#FF2D55'],
    ['#34C759', '#5AC8FA'],
    ['#AF52DE', '#0A84FF'],
    ['#FF2D55', '#AF52DE'],
    ['#1C1C1E', '#5856D6'],
    ['#FFCC00', '#FF9500'],
    ['#00C7BE', '#0A84FF'],
  ];
  var TEMPLATES = [
    { name: 'Promo', bg: ['#FF9500', '#FF2D55'], dark: false,
      layers: [
        { type: 'text', text: 'MEGA PROMO', x: 90, y: 250, width: 900, fontSize: 92, color: '#FFFFFF', align: 'center', bold: true, font: 'sans' },
        { type: 'text', text: 'Diskon hingga 50% untuk semua produk pilihan.', x: 120, y: 450, width: 840, fontSize: 42, color: '#FFFFFF', align: 'center', bold: false, font: 'sans' },
        { type: 'button', text: 'Belanja Sekarang', x: 330, y: 620, width: 420, height: 96, fontSize: 34, bg: '#FFFFFF', color: '#FF2D55', radius: 48 },
        { type: 'text', text: '#Promo #FlashSale #Terbatas', x: 90, y: 1100, width: 900, fontSize: 30, color: '#FFFFFF', align: 'center', bold: false, font: 'sans' },
      ] },
    { name: 'Quotes', bg: ['#F2F2F7', '#E5E5EA'], dark: true,
      layers: [
        { type: 'text', text: '“', x: 90, y: 180, width: 900, fontSize: 180, color: '#0A84FF', align: 'center', bold: true, font: 'serif' },
        { type: 'text', text: 'Kesuksesan dimulai dari langkah kecil hari ini.', x: 110, y: 430, width: 860, fontSize: 56, color: '#1C1C1E', align: 'center', bold: true, font: 'serif' },
        { type: 'text', text: '— Tim Marketing', x: 90, y: 700, width: 900, fontSize: 32, color: '#8E8E93', align: 'center', bold: false, font: 'sans' },
      ] },
    { name: 'Launch', bg: ['#1C1C1E', '#5856D6'], dark: false,
      layers: [
        { type: 'text', text: '● NEW LAUNCH', x: 90, y: 260, width: 900, fontSize: 40, color: '#5AC8FA', align: 'center', bold: true, font: 'mono' },
        { type: 'text', text: 'Produk Baru\nHadir untuk Anda', x: 90, y: 400, width: 900, fontSize: 78, color: '#FFFFFF', align: 'center', bold: true, font: 'sans' },
        { type: 'text', text: 'Pre-order mulai 10 September', x: 90, y: 760, width: 900, fontSize: 34, color: '#C7C7CC', align: 'center', bold: false, font: 'sans' },
        { type: 'button', text: 'Cari Tahu', x: 360, y: 880, width: 360, height: 92, fontSize: 32, bg: '#5AC8FA', color: '#1C1C1E', radius: 46 },
      ] },
    { name: 'Info', bg: ['#0A84FF', '#00C7BE'], dark: false,
      layers: [
        { type: 'text', text: 'INFO PENTING', x: 90, y: 320, width: 900, fontSize: 84, color: '#FFFFFF', align: 'center', bold: true, font: 'sans' },
        { type: 'text', text: 'Jam operasional berubah mulai pekan depan', x: 140, y: 520, width: 800, fontSize: 40, color: '#FFFFFF', align: 'center', bold: false, font: 'sans' },
        { type: 'button', text: 'Lihat Detail', x: 360, y: 700, width: 360, height: 92, fontSize: 32, bg: '#FFFFFF', color: '#0A84FF', radius: 46 },
      ] },
    { name: 'Minimal', bg: ['#FFFFFF', '#FFFFFF'], dark: true,
      layers: [
        { type: 'text', text: 'LESS IS MORE', x: 90, y: 420, width: 900, fontSize: 68, color: '#1C1C1E', align: 'center', bold: true, font: 'sans' },
        { type: 'text', text: 'Konten simpel, pesan jelas.', x: 90, y: 580, width: 900, fontSize: 34, color: '#8E8E93', align: 'center', bold: false, font: 'sans' },
      ] },
    { name: 'Urgent', bg: ['#FF3B30', '#FF9500'], dark: false,
      layers: [
        { type: 'text', text: '🔥 HABISKAN STOK!', x: 90, y: 330, width: 900, fontSize: 76, color: '#FFFFFF', align: 'center', bold: true, font: 'sans' },
        { type: 'text', text: 'Hanya 3 hari lagi', x: 90, y: 520, width: 900, fontSize: 44, color: '#FFFFFF', align: 'center', bold: true, font: 'sans' },
        { type: 'button', text: 'Klaim Sekarang', x: 330, y: 680, width: 420, height: 96, fontSize: 34, bg: '#FFFFFF', color: '#FF3B30', radius: 48 },
      ] },
  ];

  var cfg, canvas, ctx, cssW, cssH, dpr;
  var state = { slides: [], current: 0, selectedId: null, type: 'single' };
  var stripEl, inspectorEl, bgControlsEl, templatesEl;
  var fileInput, fileMode = null;
  var dragging = null;

  /* ---------------- helpers ---------------- */
  function uid() { return 'l' + Math.random().toString(36).slice(2, 10); }

  function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

  function bgGradientColors(bg) {
    if (bg.colors && bg.colors.length === 2) return bg.colors;
    return ['#0A84FF', '#5856D6'];
  }

  function defaultSlide(i) {
    var g = GRADIENTS[i % GRADIENTS.length];
    return {
      id: uid(),
      background: { kind: 'gradient', colors: g.slice(), angle: 135, solid: '#1C1C1E', image: null },
      layers: [],
    };
  }

  function textLayer(text, x, y, fontSize, color, bold) {
    return { id: uid(), type: 'text', text: text, x: x, y: y, width: 840, fontSize: fontSize, color: color, align: 'center', bold: !!bold, font: 'sans' };
  }

  function buttonLayer(text) {
    return { id: uid(), type: 'button', text: text, x: 340, y: 620, width: 400, height: 92, fontSize: 32, bg: '#0A84FF', color: '#FFFFFF', radius: 46 };
  }

  function imageLayer(url) {
    return { id: uid(), type: 'image', url: url, x: 290, y: 425, width: 500, height: 500 };
  }

  /* ---------------- text measuring ---------------- */
  function fontOf(layer, size) {
    var weight = layer.bold ? 700 : 400;
    var family = FONTS[layer.font] || FONTS.sans;
    return weight + ' ' + (size || layer.fontSize) + 'px ' + family;
  }

  function wrapText(c, text, maxWidth) {
    var lines = [];
    String(text).split('\n').forEach(function (para) {
      var words = para.split(/\s+/);
      var line = '';
      words.forEach(function (word) {
        if (word === '') return;
        var test = line ? line + ' ' + word : word;
        if (line && c.measureText(test).width > maxWidth) {
          lines.push(line);
          line = word;
        } else {
          line = test;
        }
      });
      if (line) lines.push(line);
    });
    return lines;
  }

  function textHeight(c, layer, lines) {
    return lines.length * (layer.fontSize * 1.25);
  }

  /* ---------------- drawing ---------------- */
  function drawBackground(bg, c) {
    if (bg.kind === 'solid') {
      c.fillStyle = bg.solid || '#1C1C1E';
      c.fillRect(0, 0, W, H);
    } else if (bg.kind === 'image' && bg.image) {
      c.fillStyle = '#1C1C1E';
      c.fillRect(0, 0, W, H);
      if (bg._img && bg._img.src === bg.image) {
        c.drawImage(bg._img, 0, 0, W, H);
        if (bg.overlay) { c.fillStyle = bg.overlay; c.fillRect(0, 0, W, H); }
      } else {
        var img = new Image();
        img.onload = function () { bg._img = img; render(); };
        img.src = bg.image;
      }
    } else {
      var colors = bgGradientColors(bg);
      var grad = c.createLinearGradient(0, 0, W * Math.cos(bg.angle * Math.PI / 180), H * Math.sin(bg.angle * Math.PI / 180));
      grad.addColorStop(0, colors[0]);
      grad.addColorStop(1, colors[1]);
      c.fillStyle = grad;
      c.fillRect(0, 0, W, H);
    }
  }

  function drawTextLayer(layer, c) {
    c.save();
    c.font = fontOf(layer);
    c.textAlign = layer.align || 'center';
    c.textBaseline = 'top';
    c.fillStyle = layer.color || '#FFFFFF';

    var x = layer.x;
    if (layer.align === 'center') x += layer.width / 2;
    else if (layer.align === 'right') x += layer.width;

    var lines = wrapText(c, layer.text, layer.width);
    var lh = layer.fontSize * 1.25;
    lines.forEach(function (line, i) {
      c.fillText(line, x, layer.y + i * lh);
    });
    c.restore();
  }

  function drawButtonLayer(layer, c) {
    c.save();
    var r = Math.min(layer.radius || 46, layer.height / 2);
    c.beginPath();
    c.roundRect ? c.roundRect(layer.x, layer.y, layer.width, layer.height, r) : c.rect(layer.x, layer.y, layer.width, layer.height);
    c.fillStyle = layer.bg || '#0A84FF';
    c.fill();
    c.font = (layer.bold ? '700 ' : '400 ') + (layer.fontSize || 30) + 'px ' + FONT_STACK;
    c.fillStyle = layer.color || '#FFFFFF';
    c.textAlign = 'center';
    c.textBaseline = 'middle';
    c.fillText(layer.text || 'Tombol', layer.x + layer.width / 2, layer.y + layer.height / 2 + 1);
    c.restore();
  }

  function drawImageLayer(layer, c) {
    if (!layer.url) return;
    c.save();
    c.fillStyle = '#E5E5EA';
    c.fillRect(layer.x, layer.y, layer.width, layer.height);
    c.restore();
    if (layer._img && layer._img.src === layer.url) {
      c.drawImage(layer._img, layer.x, layer.y, layer.width, layer.height);
    } else {
      var img = new Image();
      img.onload = function () { layer._img = img; render(); };
      img.src = layer.url;
    }
  }

  function drawLayer(layer, c, highlight) {
    if (layer.type === 'text') drawTextLayer(layer, c);
    else if (layer.type === 'button') drawButtonLayer(layer, c);
    else if (layer.type === 'image') drawImageLayer(layer, c);

    if (highlight) {
      c.save();
      c.strokeStyle = '#0A84FF';
      c.lineWidth = 6;
      c.setLineDash([14, 10]);
      var box = layerBox(layer, c);
      c.strokeRect(box.x, box.y, box.w, box.h);
      // handles
      c.setLineDash([]);
      c.fillStyle = '#FFFFFF';
      c.strokeStyle = '#0A84FF';
      c.lineWidth = 4;
      [[box.x, box.y], [box.x + box.w, box.y], [box.x, box.y + box.h], [box.x + box.w, box.y + box.h]].forEach(function (p) {
        c.beginPath();
        c.arc(p[0], p[1], 10, 0, Math.PI * 2);
        c.fill();
        c.stroke();
      });
      c.restore();
    }
  }

  function layerBox(layer, c) {
    if (layer.type === 'text') {
      c.font = fontOf(layer);
      var lines = wrapText(c, layer.text, layer.width);
      var h = textHeight(c, layer, lines);
      var w = layer.width;
      var x = layer.x;
      if (layer.align === 'center') x = layer.x + layer.width / 2 - w / 2;
      if (layer.align === 'right') x = layer.x + layer.width - w;
      return { x: x, y: layer.y, w: w, h: h };
    }
    return { x: layer.x, y: layer.y, w: layer.width, h: layer.height };
  }

  /* ---------------- render ---------------- */
  function drawSlide(slide, c) {
    c.clearRect(0, 0, W, H);
    drawBackground(slide.background, c);
    slide.layers.forEach(function (layer) {
      drawLayer(layer, c, layer.id === state.selectedId);
    });
  }

  function render() {
    if (!ctx) return;
    drawSlide(state.slides[state.current], ctx);
    renderStrip();
  }

  function renderStrip() {
    stripEl.innerHTML = '';
    state.slides.forEach(function (slide, i) {
      var thumb = document.createElement('canvas');
      thumb.width = 64; thumb.height = 80;
      var tctx = thumb.getContext('2d');
      tctx.scale(64 / W, 80 / H);
      // background only (layers tanpa image async)
      drawBackground(slide.background, tctx);
      slide.layers.forEach(function (l) {
        if (l.type === 'image') {
          var img = new Image();
          img.onload = function () { tctx.drawImage(img, l.x, l.y, l.width, l.height); stripEl.querySelector('[data-slide="' + i + '"] img').src = thumb.toDataURL(); };
          img.src = l.url;
        } else {
          drawLayer(l, tctx, false);
        }
      });

      var wrap = document.createElement('div');
      wrap.className = 'fb-slide-thumb' + (i === state.current ? ' active' : '');
      wrap.dataset.slide = i;
      var img = document.createElement('img');
      img.src = thumb.toDataURL();
      wrap.appendChild(img);
      if (state.slides.length > 1) {
        var rm = document.createElement('span');
        rm.className = 'remove';
        rm.innerHTML = '×';
        rm.title = 'Hapus slide';
        rm.addEventListener('click', function (e) { e.stopPropagation(); removeSlide(i); });
        wrap.appendChild(rm);
      }
      var num = document.createElement('span');
      num.className = 'absolute bottom-1 right-1 bg-black/60 text-white text-[9px] font-bold rounded px-1';
      num.textContent = (i + 1) + '/' + state.slides.length;
      wrap.appendChild(num);
      wrap.addEventListener('click', function () { gotoSlide(i); });
      stripEl.appendChild(wrap);
    });
  }

  /* ---------------- interaction ---------------- */
  function hitTest(slide, px, py) {
    for (var i = slide.layers.length - 1; i >= 0; i--) {
      var layer = slide.layers[i];
      var box = layerBox(layer, ctx);
      if (px >= box.x && px <= box.x + box.w && py >= box.y && py <= box.y + box.h) {
        return layer;
      }
    }
    return null;
  }

  function setupCanvas() {
    canvas = document.getElementById('editor-canvas');
    ctx = canvas.getContext('2d');
    cssW = 360; cssH = 450;
    dpr = window.devicePixelRatio || 1;
    canvas.style.width = cssW + 'px';
    canvas.style.height = cssH + 'px';
    canvas.width = cssW * 2;
    canvas.height = cssH * 2;
    ctx.scale(canvas.width / W, canvas.height / H);

    canvas.addEventListener('pointerdown', function (e) {
      var rect = canvas.getBoundingClientRect();
      var px = (e.clientX - rect.left) * (W / rect.width);
      var py = (e.clientY - rect.top) * (H / rect.height);
      var slide = state.slides[state.current];
      var layer = hitTest(slide, px, py);
      if (layer) {
        state.selectedId = layer.id;
        dragging = { id: layer.id, offX: px - layer.x, offY: py - layer.y };
        canvas.style.cursor = 'grabbing';
      } else {
        state.selectedId = null;
      }
      render(); renderInspector();
      canvas.setPointerCapture(e.pointerId);
    });

    canvas.addEventListener('pointermove', function (e) {
      if (!dragging) return;
      var rect = canvas.getBoundingClientRect();
      var px = (e.clientX - rect.left) * (W / rect.width);
      var py = (e.clientY - rect.top) * (H / rect.height);
      var slide = state.slides[state.current];
      var layer = slide.layers.find(function (l) { return l.id === dragging.id; });
      if (layer) {
        var box = layerBox(layer, ctx);
        layer.x = clamp(px - dragging.offX, -box.w + 40, W - 40);
        layer.y = clamp(py - dragging.offY, 0, H - 40);
        render();
      }
    });

    canvas.addEventListener('pointerup', function () {
      dragging = null;
      canvas.style.cursor = 'crosshair';
    });

    canvas.addEventListener('pointerleave', function () {
      if (!dragging) canvas.style.cursor = 'crosshair';
    });

    document.addEventListener('keydown', function (e) {
      var tag = document.activeElement && document.activeElement.tagName;
      if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return;
      var slide = state.slides[state.current];
      var layer = slide.layers.find(function (l) { return l.id === state.selectedId; });
      if (!layer) return;
      var step = e.shiftKey ? 10 : 2;
      if (e.key === 'ArrowLeft') { layer.x -= step; e.preventDefault(); }
      else if (e.key === 'ArrowRight') { layer.x += step; e.preventDefault(); }
      else if (e.key === 'ArrowUp') { layer.y -= step; e.preventDefault(); }
      else if (e.key === 'ArrowDown') { layer.y += step; e.preventDefault(); }
      else if (e.key === 'Delete' || e.key === 'Backspace') { deleteLayer(); e.preventDefault(); }
      else return;
      render();
    });
  }

  /* ---------------- slide ops ---------------- */
  function gotoSlide(i) {
    state.current = clamp(i, 0, state.slides.length - 1);
    state.selectedId = null;
    render(); renderInspector(); renderBackgroundControls();
  }

  function addSlide() {
    state.slides.push(defaultSlide(state.slides.length));
    gotoSlide(state.slides.length - 1);
  }

  function duplicateSlide() {
    var src = state.slides[state.current];
    var copy = JSON.parse(JSON.stringify(src));
    copy.id = uid();
    copy.layers.forEach(function (l) { l.id = uid(); });
    state.slides.push(copy);
    gotoSlide(state.slides.length - 1);
  }

  function removeSlide(i) {
    if (state.slides.length <= 1) { FB.toast('Minimal satu slide.', 'error'); return; }
    state.slides.splice(i, 1);
    state.current = clamp(state.current, 0, state.slides.length - 1);
    state.selectedId = null;
    render(); renderInspector(); renderBackgroundControls();
  }

  /* ---------------- layer ops ---------------- */
  function currentSlide() { return state.slides[state.current]; }

  function selectedLayer() {
    var slide = currentSlide();
    return slide.layers.find(function (l) { return l.id === state.selectedId; }) || null;
  }

  function selectLayer(id) { state.selectedId = id; render(); renderInspector(); }

  function addText() {
    var slide = currentSlide();
    var l = textLayer('Ketik teks Anda di sini', 120, 300, 56, '#FFFFFF', true);
    slide.layers.push(l);
    selectLayer(l.id);
  }

  function addButton() {
    var slide = currentSlide();
    var l = buttonLayer('Aksi Sekarang');
    slide.layers.push(l);
    selectLayer(l.id);
  }

  function addImage(url) {
    var slide = currentSlide();
    var l = imageLayer(url);
    slide.layers.push(l);
    selectLayer(l.id);
  }

  function deleteLayer() {
    var slide = currentSlide();
    var idx = slide.layers.findIndex(function (l) { return l.id === state.selectedId; });
    if (idx === -1) return;
    slide.layers.splice(idx, 1);
    state.selectedId = null;
    render(); renderInspector();
  }

  function duplicateLayer() {
    var slide = currentSlide();
    var layer = selectedLayer();
    if (!layer) return;
    var copy = JSON.parse(JSON.stringify(layer));
    copy.id = uid();
    copy.x += 30; copy.y += 30;
    slide.layers.push(copy);
    selectLayer(copy.id);
  }

  function zOrder(dir) {
    var slide = currentSlide();
    var idx = slide.layers.findIndex(function (l) { return l.id === state.selectedId; });
    if (idx === -1) return;
    var layer = slide.layers.splice(idx, 1)[0];
    if (dir === 'front') slide.layers.push(layer);
    else slide.layers.unshift(layer);
    render();
  }

  /* ---------------- inspector ---------------- */
  function renderInspector() {
    var layer = selectedLayer();
    if (!layer) {
      inspectorEl.innerHTML = '<p class="text-[13px] text-ios-gray">Klik layer pada canvas untuk mengedit propertinya.</p>';
      return;
    }
    var html = '';
    if (layer.type === 'text') {
      html += field('label', 'Teks');
      html += field('textarea', layer.text, 'layer.text');
      html += '<div class="grid grid-cols-2 gap-2">' + field('number', layer.fontSize, 'layer.fontSize', 12, 200) + field('color', layer.color, 'layer.color') + '</div>';
      html += '<div class="flex items-center justify-between mt-2">' +
        '<span class="fb-label !mb-0">Bold</span>' +
        '<label class="fb-switch"><input type="checkbox" data-bind="layer.bold"' + (layer.bold ? ' checked' : '') + '><span class="track"></span></label></div>';
      html += '<div class="mt-3"><span class="fb-label">Font</span><div class="fb-segmented w-full" data-bind-select="layer.font">' +
        '<button data-v="sans" class="flex-1' + (layer.font === 'sans' ? ' active' : '') + '">Sans</button>' +
        '<button data-v="serif" class="flex-1' + (layer.font === 'serif' ? ' active' : '') + '">Serif</button>' +
        '<button data-v="mono" class="flex-1' + (layer.font === 'mono' ? ' active' : '') + '">Mono</button>' +
        '</div></div>';
      html += '<div class="mt-3"><span class="fb-label">Align</span><div class="fb-segmented w-full" data-bind-select="layer.align">' +
        '<button data-v="left" class="flex-1' + (layer.align === 'left' ? ' active' : '') + '">Kiri</button>' +
        '<button data-v="center" class="flex-1' + (layer.align === 'center' ? ' active' : '') + '">Tengah</button>' +
        '<button data-v="right" class="flex-1' + (layer.align === 'right' ? ' active' : '') + '">Kanan</button>' +
        '</div></div>';
    } else if (layer.type === 'button') {
      html += field('label', 'Teks Tombol') + field('text', layer.text, 'layer.text');
      html += '<div class="grid grid-cols-2 gap-2">' + field('color', layer.bg, 'layer.bg') + field('color', layer.color, 'layer.color') + '</div>';
      html += '<div class="grid grid-cols-2 gap-2 mt-2">' + field('number', layer.fontSize, 'layer.fontSize', 12, 100) + field('number', layer.radius, 'layer.radius', 0, 60) + '</div>';
      html += '<div class="grid grid-cols-2 gap-2 mt-2">' + field('number', layer.width, 'layer.width', 100, 1080) + field('number', layer.height, 'layer.height', 40, 500) + '</div>';
    } else if (layer.type === 'image') {
      html += field('label', 'Gambar');
      html += '<img src="' + layer.url + '" class="w-full h-40 object-cover rounded-xl mb-2">';
      html += '<div class="grid grid-cols-2 gap-2">' + field('number', layer.width, 'layer.width', 40, 1080) + field('number', layer.height, 'layer.height', 40, 1350) + '</div>';
    }
    html += '<div class="grid grid-cols-2 gap-2 mt-3">' + field('number', layer.x, 'layer.x', 0, 1080) + field('number', layer.y, 'layer.y', 0, 1350) + '</div>';
    inspectorEl.innerHTML = html;

    inspectorEl.querySelectorAll('[data-bind]').forEach(function (el) {
      el.addEventListener('input', function () {
        var key = el.getAttribute('data-bind').split('.')[1];
        if (el.type === 'checkbox') layer[key] = el.checked;
        else if (el.type === 'number') layer[key] = parseFloat(el.value) || 0;
        else layer[key] = el.value;
        render();
      });
    });
    inspectorEl.querySelectorAll('[data-bind-select]').forEach(function (seg) {
      seg.querySelectorAll('button').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var key = seg.getAttribute('data-bind-select').split('.')[1];
          layer[key] = btn.dataset.v;
          seg.querySelectorAll('button').forEach(function (b) { b.classList.remove('active'); });
          btn.classList.add('active');
          render();
        });
      });
    });
  }

  function field(kind, value, bind, min, max) {
    var id = 'f-' + Math.random().toString(36).slice(2, 6);
    if (kind === 'label') return '<span class="fb-label">' + value + '</span>';
    if (kind === 'textarea') {
      return '<textarea id="' + id + '" class="fb-textarea" rows="2" data-bind="' + bind + '">' + String(value).replace(/</g, '&lt;') + '</textarea>';
    }
    if (kind === 'text') return '<input id="' + id + '" type="text" class="fb-input" value="' + String(value).replace(/"/g, '&quot;') + '" data-bind="' + bind + '">';
    if (kind === 'number') {
      return '<div><span class="fb-label">' + bind.split('.')[1].toUpperCase() + '</span><input type="number" min="' + (min || 0) + '" max="' + (max || 9999) + '" class="fb-input" value="' + value + '" data-bind="' + bind + '"></div>';
    }
    if (kind === 'color') {
      return '<div><span class="fb-label">' + bind.split('.')[1].toUpperCase() + '</span><input type="color" class="fb-input !h-[46px] !p-1" value="' + value + '" data-bind="' + bind + '"></div>';
    }
    return '';
  }

  /* ---------------- background controls ---------------- */
  function renderBackgroundControls() {
    var bg = currentSlide().background;
    var html = '';
    if (bg.kind === 'gradient') {
      html += '<div class="grid grid-cols-2 gap-2 mb-2">' +
        '<div><span class="fb-label">Warna 1</span><input type="color" class="fb-input !h-[42px] !p-1" id="bg-c1" value="' + bg.colors[0] + '"></div>' +
        '<div><span class="fb-label">Warna 2</span><input type="color" class="fb-input !h-[42px] !p-1" id="bg-c2" value="' + bg.colors[1] + '"></div>' +
        '</div>';
      html += '<div class="grid grid-cols-4 gap-2">' + GRADIENTS.map(function (g, i) {
        return '<button data-grad="' + i + '" class="h-9 rounded-lg border-2 ' + (bg.colors[0] === g[0] ? 'border-ios-blue' : 'border-transparent') + '" style="background:linear-gradient(135deg,' + g[0] + ',' + g[1] + ')"></button>';
      }).join('') + '</div>';
    } else if (bg.kind === 'solid') {
      html += '<div class="grid grid-cols-2 gap-2 items-end">' +
        '<div><span class="fb-label">Warna</span><input type="color" class="fb-input !h-[42px] !p-1" id="bg-solid" value="' + (bg.solid || '#1C1C1E') + '"></div>' +
        '<button id="bg-random" class="fb-btn fb-btn-ghost fb-btn-sm">Acak</button></div>';
    } else {
      html += '<div class="flex items-center gap-2">' +
        '<button id="bg-upload" class="fb-btn fb-btn-ghost fb-btn-sm flex-1">' + (bg.image ? 'Ganti Gambar' : 'Upload Gambar') + '</button>' +
        (bg.image ? '<button id="bg-clear" class="fb-btn fb-btn-danger fb-btn-sm">Hapus</button>' : '') +
        '</div>';
      if (bg.image) html += '<img src="' + bg.image + '" class="w-full h-24 object-cover rounded-xl mt-2">';
    }
    bgControlsEl.innerHTML = html;

    var bind = function (id, fn) {
      var el = document.getElementById(id);
      if (el) el.addEventListener('input', function () { fn(el); render(); });
    };
    if (bg.kind === 'gradient') {
      bind('bg-c1', function (el) { bg.colors[0] = el.value; renderBackgroundControls(); });
      bind('bg-c2', function (el) { bg.colors[1] = el.value; renderBackgroundControls(); });
      bgControlsEl.querySelectorAll('[data-grad]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          bg.colors = GRADIENTS[parseInt(btn.dataset.grad, 10)].slice();
          renderBackgroundControls(); render();
        });
      });
    } else if (bg.kind === 'solid') {
      bind('bg-solid', function (el) { bg.solid = el.value; });
      var rand = document.getElementById('bg-random');
      if (rand) rand.addEventListener('click', function () {
        var colors = ['#0A84FF', '#34C759', '#FF9500', '#FF2D55', '#AF52DE', '#5856D6', '#1C1C1E', '#00C7BE', '#8E8E93', '#FFCC00'];
        bg.solid = colors[Math.floor(Math.random() * colors.length)];
        renderBackgroundControls(); render();
      });
    } else {
      var up = document.getElementById('bg-upload');
      if (up) up.addEventListener('click', function () { fileMode = 'bg'; fileInput.click(); });
      var clr = document.getElementById('bg-clear');
      if (clr) clr.addEventListener('click', function () { bg.image = null; renderBackgroundControls(); render(); });
    }
  }

  function setBgKind(kind) {
    currentSlide().background.kind = kind;
    document.querySelectorAll('#bg-type button').forEach(function (b) { b.classList.toggle('active', b.dataset.bg === kind); });
    renderBackgroundControls(); render();
  }

  /* ---------------- templates ---------------- */
  function renderTemplates() {
    templatesEl.innerHTML = '';
    TEMPLATES.forEach(function (t, i) {
      var btn = document.createElement('button');
      btn.className = 'rounded-xl overflow-hidden relative text-left group';
      btn.innerHTML = '<div class="aspect-[4/5] w-full flex flex-col items-center justify-center gap-1" style="background:linear-gradient(135deg,' + t.bg[0] + ',' + t.bg[1] + ')">' +
        '<span class="text-white font-bold text-[11px] leading-tight px-2 text-center">' + t.name + '</span>' +
        '<span class="text-white/70 text-[8px]">4:5</span></div>';
      btn.addEventListener('click', function () { applyTemplate(i); });
      templatesEl.appendChild(btn);
    });
  }

  function applyTemplate(i) {
    var t = TEMPLATES[i];
    var slide = currentSlide();
    slide.background = { kind: 'gradient', colors: t.bg.slice(), angle: 135, solid: '#1C1C1E', image: null };
    slide.layers = JSON.parse(JSON.stringify(t.layers)).map(function (l) { l.id = uid(); return l; });
    state.selectedId = null;
    render(); renderInspector(); renderBackgroundControls();
    FB.toast('Template "' + t.name + '" diterapkan.');
  }

  /* ---------------- export & save ---------------- */
  function preloadAll(slides) {
    var jobs = [];
    slides.forEach(function (slide) {
      if (slide.background.kind === 'image' && slide.background.image && !slide.background._img) {
        jobs.push(new Promise(function (resolve) {
          var im = new Image();
          im.onload = function () { slide.background._img = im; resolve(); };
          im.onerror = resolve;
          im.src = slide.background.image;
        }));
      }
      slide.layers.forEach(function (layer) {
        if (layer.type === 'image' && !layer._img) {
          jobs.push(new Promise(function (resolve) {
            var im = new Image();
            im.onload = function () { layer._img = im; resolve(); };
            im.onerror = resolve;
            im.src = layer.url;
          }));
        }
      });
    });
    return Promise.all(jobs);
  }

  function renderSlideToDataURL(slide) {
    var off = document.createElement('canvas');
    off.width = W; off.height = H;
    var c = off.getContext('2d');
    drawSlide(slide, c);
    return off.toDataURL('image/png');
  }

  function uploadAll(slides) {
    return preloadAll(slides).then(function () {
      var queue = slides.map(function (slide) { return renderSlideToDataURL(slide); });
      return queue.reduce(function (promise, dataUrl, i) {
        return promise.then(function (paths) {
          return fetch(cfg.uploadUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify({ filename: 'slide-' + (i + 1) + '.png', data_url: dataUrl }),
          }).then(function (r) { return r.json(); }).then(function (res) {
            if (!res.ok) throw new Error(res.message || 'Upload gagal');
            paths.push(res.path);
            return paths;
          });
        });
      }, Promise.resolve([]));
    });
  }

  function save(submit) {
    var title = document.getElementById('content-title').value.trim();
    if (!title) { FB.toast('Judul konten wajib diisi.', 'error'); return; }

    var btn = submit ? document.getElementById('btn-save-submit') : document.getElementById('btn-save');
    btn.disabled = true; btn.innerHTML = 'Menyimpan…';

    uploadAll(state.slides).then(function (paths) {
      var design = {
        version: 1, ratio: '4:5', width: W, height: H,
        slides: state.slides.map(function (s) { return { background: s.background, layers: s.layers }; }),
      };
      var payload = {
        title: title,
        type: state.type,
        design: JSON.stringify(design),
        files: paths,
        cover: paths[0],
        caption: document.getElementById('content-caption').value,
        platform: document.getElementById('content-platform').value,
        scheduled_at: document.getElementById('content-schedule').value || null,
      };
      var url = cfg.updateUrl || cfg.storeUrl;
      var method = cfg.updateUrl ? 'PUT' : 'POST';

      return fetch(url, {
        method: method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: JSON.stringify(payload),
      }).then(function (r) { return r.json(); }).then(function (res) {
        if (!res.ok) throw new Error(res.message || 'Gagal menyimpan');
        if (submit) {
          return fetch(res.redirect.replace('/content', '/content') + '/submit', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': csrf() },
          }).then(function () { return res; });
        }
        return res;
      }).then(function (res) {
        window.location.href = res.redirect;
      });
    }).catch(function (err) {
      FB.toast(err.message || 'Terjadi kesalahan.', 'error');
      btn.disabled = false;
      btn.innerHTML = submit ? '🚀 Simpan & Kirim Approval' : '💾 Simpan Draft';
    });
  }

  function csrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.content : '';
  }

  /* ---------------- init ---------------- */
  function init(opts) {
    cfg = opts;
    state.type = cfg.initialType || 'single';

    // build slides
    if (cfg.initialDesign && cfg.initialDesign.slides && cfg.initialDesign.slides.length) {
      state.slides = cfg.initialDesign.slides.map(function (s) {
        return { id: uid(), background: s.background, layers: s.layers.map(function (l) { l.id = uid(); return l; }) };
      });
    } else {
      state.slides = [defaultSlide(0)];
    }
    state.current = 0;

    stripEl = document.getElementById('slides-strip');
    inspectorEl = document.getElementById('layer-inspector');
    bgControlsEl = document.getElementById('bg-controls');
    templatesEl = document.getElementById('templates-grid');
    fileInput = document.getElementById('file-input');

    // type segmented
    document.querySelectorAll('#type-seg button').forEach(function (btn) {
      btn.classList.toggle('active', btn.dataset.type === state.type);
      btn.addEventListener('click', function () {
        state.type = btn.dataset.type;
        document.querySelectorAll('#type-seg button').forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        if (state.type === 'single' && state.slides.length > 1) {
          state.slides = state.slides.slice(0, 1);
          state.current = 0;
          render();
        }
      });
    });

    // toolbar
    document.getElementById('toolbar').querySelectorAll('[data-tool]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var tool = btn.dataset.tool;
        if (tool === 'text') addText();
        else if (tool === 'image') { fileMode = 'layer'; fileInput.click(); }
        else if (tool === 'button') addButton();
        else if (tool === 'front') zOrder('front');
        else if (tool === 'back') zOrder('back');
        else if (tool === 'duplicate') duplicateLayer();
        else if (tool === 'delete') deleteLayer();
      });
    });

    fileInput.addEventListener('change', function () {
      var file = fileInput.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function () {
        var url = reader.result;
        if (fileMode === 'bg') { currentSlide().background.image = url; renderBackgroundControls(); render(); }
        else if (fileMode === 'layer') addImage(url);
        fileInput.value = '';
      };
      reader.readAsDataURL(file);
    });

    document.getElementById('btn-add-slide').addEventListener('click', addSlide);
    document.getElementById('btn-save').addEventListener('click', function () { save(false); });
    document.getElementById('btn-save-submit').addEventListener('click', function () { save(true); });
    document.getElementById('btn-reset').addEventListener('click', function () {
      if (!confirm('Reset semua slide ke keadaan awal?')) return;
      state.slides = [defaultSlide(0)];
      state.current = 0;
      state.selectedId = null;
      render(); renderInspector(); renderBackgroundControls();
    });

    document.querySelectorAll('#bg-type button').forEach(function (btn) {
      btn.addEventListener('click', function () { setBgKind(btn.dataset.bg); });
    });

    setupCanvas();
    renderTemplates();
    render();
    renderInspector();
    renderBackgroundControls();
  }

  window.FBEditor = { init: init };
})();