/* ============================================================
   Freebuff Runtime — helpers, charts (Chart.js), animasi, toast
   ============================================================ */
window.FB = window.FB || {};

(function () {
  'use strict';

  /* ---------- Formatting ---------- */
  var PALETTE = ['#0A84FF', '#34C759', '#FF9500', '#AF52DE', '#FF2D55', '#5AC8FA', '#5856D6', '#FFCC00', '#8E8E93', '#00C7BE'];

  function fmtInt(n) {
    n = Number(n) || 0;
    return n.toLocaleString('id-ID');
  }

  function fmtCompact(n) {
    n = Number(n) || 0;
    if (Math.abs(n) >= 1000000000) return (n / 1000000000).toFixed(1).replace(/\.0$/, '') + ' M';
    if (Math.abs(n) >= 1000000) return (n / 1000000).toFixed(1).replace(/\.0$/, '') + ' Jt';
    if (Math.abs(n) >= 1000) return (n / 1000).toFixed(1).replace(/\.0$/, '') + ' Rb';
    return String(Math.round(n * 100) / 100);
  }

  function fmtCurrency(n, symbol) {
    n = Number(n) || 0;
    return (symbol || '$') + fmtInt(Math.round(n * 100) / 100);
  }

  function fmtPercent(n, decimals) {
    n = Number(n) || 0;
    return n.toFixed(decimals === undefined ? 2 : decimals) + '%';
  }

  function fmtDuration(seconds) {
    seconds = Number(seconds) || 0;
    var m = Math.floor(seconds / 60);
    var s = Math.round(seconds % 60);
    return (m > 0 ? m + 'm ' : '') + s + 's';
  }

  /* ---------- Chart.js factory ---------- */
  function chart(id, config) {
    var el = document.getElementById(id);
    if (!el || typeof Chart === 'undefined') return null;

    var type = config.type || 'line';
    var labels = config.labels || [];
    var datasets = (config.datasets || []).map(function (ds, i) {
      var color = ds.color || PALETTE[i % PALETTE.length];
      if (type === 'line' || type === 'bar') {
        return {
          label: ds.label || '',
          data: ds.data || [],
          borderColor: color,
          backgroundColor: type === 'line' ? color + '18' : color,
          fill: type === 'line' ? !!ds.fill : false,
          tension: 0.35,
          borderWidth: ds.borderWidth || 2.5,
          pointRadius: ds.pointRadius === undefined ? 0 : ds.pointRadius,
          pointHoverRadius: 5,
          borderRadius: type === 'bar' ? 6 : 0,
          borderSkipped: type === 'bar' ? false : undefined,
          maxBarThickness: 26,
        };
      }
      // doughnut / polarArea
      return {
        data: ds.data || [],
        backgroundColor: (ds.colors || PALETTE.slice(0, (ds.data || []).length)),
        borderColor: '#fff',
        borderWidth: 3,
        hoverOffset: 6,
      };
    });

    var scales = {};
    if (type === 'line' || type === 'bar') {
      scales = {
        x: { grid: { display: false }, ticks: { maxTicksLimit: 8, color: '#8E8E93', font: { size: 11 } }, border: { display: false } },
        y: { beginAtZero: true, grid: { color: 'rgba(60,60,67,0.06)' }, ticks: { color: '#8E8E93', font: { size: 11 }, callback: function (v) { return fmtCompact(v); } }, border: { display: false } },
      };
    }

    return new Chart(el, {
      type: type,
      data: { labels: labels, datasets: datasets },
      options: Object.assign({
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            display: !!config.legend,
            position: 'bottom',
            labels: { usePointStyle: true, pointStyle: 'circle', padding: 16, color: '#3C3C43', font: { size: 12, weight: '600' } },
          },
          tooltip: {
            backgroundColor: 'rgba(28,28,30,0.92)',
            padding: 12,
            cornerRadius: 12,
            titleFont: { weight: '700' },
            callbacks: {
              label: function (ctx) {
                var raw = ctx.raw === undefined || ctx.raw === null ? ctx.parsed.y : ctx.raw;
                if (config.tooltip === 'currency') return ' ' + fmtCurrency(raw, config.symbol);
                if (config.tooltip === 'percent') return ' ' + fmtPercent(raw, 2);
                if (config.tooltip === 'duration') return ' ' + fmtDuration(raw);
                return ' ' + fmtCompact(raw);
              },
            },
          },
        },
        scales: scales,
        cutout: type === 'doughnut' ? '68%' : undefined,
        animation: { duration: 900, easing: 'easeOutQuart' },
      }, config.options || {}),
    });
  }

  /* ---------- Animated counter ---------- */
  function countUp(el, target, opts) {
    opts = opts || {};
    var duration = opts.duration || 1200;
    var decimals = opts.decimals || 0;
    var suffix = opts.suffix || '';
    var prefix = opts.prefix || '';
    var format = opts.format || 'int';
    var start = null;

    function tick(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      p = 1 - Math.pow(1 - p, 3); // easeOutCubic
      var v = target * p;
      var text = prefix;
      if (format === 'compact') text += fmtCompact(v);
      else if (format === 'currency') text += fmtCurrency(v, opts.symbol);
      else if (format === 'percent') text += fmtPercent(v, decimals);
      else text += v.toLocaleString('id-ID', { maximumFractionDigits: decimals });
      text += suffix;
      el.textContent = text;
      if (p < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
  }

  function initCounters() {
    document.querySelectorAll('[data-count]').forEach(function (el) {
      var target = parseFloat(el.getAttribute('data-count')) || 0;
      countUp(el, target, {
        format: el.getAttribute('data-format') || 'int',
        suffix: el.getAttribute('data-suffix') || '',
        prefix: el.getAttribute('data-prefix') || '',
        decimals: parseInt(el.getAttribute('data-decimals') || '0', 10),
        symbol: el.getAttribute('data-symbol') || '$',
        duration: parseInt(el.getAttribute('data-duration') || '1200', 10),
      });
    });
  }

  /* ---------- Toast ---------- */
  var toastTimer = null;
  function toast(message, type) {
    var el = document.getElementById('fb-toast');
    if (!el) {
      el = document.createElement('div');
      el.id = 'fb-toast';
      el.className = 'fb-toast';
      document.body.appendChild(el);
    }
    el.className = 'fb-toast show';
    el.innerHTML = '';
    var dot = document.createElement('span');
    dot.className = 'fb-dot';
    dot.style.background = type === 'error' ? '#FF3B30' : type === 'success' ? '#34C759' : '#0A84FF';
    el.appendChild(dot);
    el.appendChild(document.createTextNode(message));
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { el.classList.remove('show'); }, 3200);
  }

  /* ---------- Helpers ---------- */
  function modal(id) {
    var el = document.getElementById(id);
    if (el) el.remove();
    var backdrop = document.createElement('div');
    backdrop.className = 'fb-modal-center';
    backdrop.id = id;
    backdrop.addEventListener('click', function (e) { if (e.target === backdrop) backdrop.remove(); });
    document.body.appendChild(backdrop);
    return backdrop;
  }

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  function initLazyCharts() {
    // Chart yang didefinisikan lewat window.PAGE_CHARTS
    (window.PAGE_CHARTS || []).forEach(function (c) { chart(c.id, c.config); });
  }

  window.FB = {
    palette: PALETTE,
    fmtInt: fmtInt,
    fmtCompact: fmtCompact,
    fmtCurrency: fmtCurrency,
    fmtPercent: fmtPercent,
    fmtDuration: fmtDuration,
    chart: chart,
    countUp: countUp,
    initCounters: initCounters,
    toast: toast,
    modal: modal,
    esc: esc,
    initLazyCharts: initLazyCharts,
  };

  document.addEventListener('DOMContentLoaded', function () {
    initCounters();
    initLazyCharts();
  });
})();