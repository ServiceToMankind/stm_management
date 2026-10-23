<?php
include 'includes/header.php';

$rid = isset($_GET['rid']) && is_numeric($_GET['rid']) ? (int)$_GET['rid'] : 0;
if ($rid < 1) {
    set_flash('danger', 'No requirement was specified.');
    redirect('blood_requests');
}

$resp = json_decode(get_api_data("$api_url/blood/requests?rid=$rid"), true);
$r    = $resp['data'][0] ?? null;

if (!$r) {
    set_flash('danger', 'That requirement no longer exists.');
    redirect('blood_requests');
}

$urgency_word = ['critical' => 'URGENT', 'urgent' => 'URGENTLY NEEDED', 'routine' => 'BLOOD NEEDED'];

// The poster is drawn in the browser, so everything it needs goes over as data
// rather than being baked into an image on the server.
$poster = [
    'group'     => $r['blood_group'],
    'units'     => units_text($r['units_short'] > 0 ? $r['units_short'] : $r['units_needed']),
    'urgency'   => $r['urgency'],
    'banner'    => $urgency_word[$r['urgency']] ?? 'BLOOD NEEDED',
    'patient'   => trim(($r['patient_name'] ?: '') . ($r['patient_age'] ? ', ' . $r['patient_age'] : '')),
    'hospital'  => $r['hospital'],
    'place'     => trim(($r['area'] ?: '') . ($r['area'] && $r['city'] ? ', ' : '') . ($r['city'] ?: '')),
    'neededOn'  => $r['needed_on'] ? date('d M Y', strtotime($r['needed_on'])) : '',
    'contact'   => $r['contact_name'],
    'mobile'    => $r['contact_mobile'],
    'altMobile' => $r['alt_mobile'] ?: '',
    'requestNo' => $r['request_no'] ?: ('#' . $r['id']),
    'reason'    => trim((string)($r['reason'] ?? '')),
    'logo'      => 'assets/images/stm-logo.png',
];

// The same appeal as plain text, for pasting into a WhatsApp group.
$share_lines = [];
$share_lines[] = '🩸 ' . $poster['banner'] . ': ' . $poster['group'] . ' BLOOD';
$share_lines[] = '';
$share_lines[] = $poster['units'] . ' unit(s) needed' . ($poster['neededOn'] ? ' by ' . $poster['neededOn'] : '');
if ($poster['patient'])  $share_lines[] = 'Patient: ' . $poster['patient'];
if ($poster['reason'])   $share_lines[] = 'Why: ' . $poster['reason'];
$share_lines[] = 'Hospital: ' . $poster['hospital'];
if ($poster['place'])    $share_lines[] = 'Location: ' . $poster['place'];
$share_lines[] = '';
$share_lines[] = 'Please contact ' . $poster['contact'] . ' — ' . $poster['mobile'];
if ($poster['altMobile']) $share_lines[] = 'Or: ' . $poster['altMobile'];
$share_lines[] = '';
$share_lines[] = 'Kindly forward this to anyone who can help. 🙏';
$share_lines[] = '— Service To Mankind (STM), stmorg.in';
$share_text = implode("\n", $share_lines);

$wa_url = 'https://wa.me/?text=' . rawurlencode($share_text);
?>


<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-image-outline"></i></span>
      Shareable Poster
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="blood_request?rid=<?= $rid ?>" class="btn btn-sm btn-light border">
        <i class="mdi mdi-arrow-left me-1"></i>Requirement
      </a>
      <a href="blood_requests" class="btn btn-sm btn-light border">All Requirements</a>
    </div>
  </div>

  <div class="row">

    <!-- ── The poster ───────────────────────────────────────────────────── -->
    <div class="col-xl-7 col-lg-6 mb-4">
      <div class="card border-0">
        <div class="card-body">
          <div class="poster-stage">
            <canvas id="posterCanvas" width="1080" height="1350"></canvas>
          </div>
          <p class="text-muted text-center mb-0 mt-3" style="font-size:0.78rem;">
            Drawn in your browser — nothing is uploaded. The download is the full 1080px artwork.
          </p>
        </div>
      </div>
    </div>

    <!-- ── Controls ─────────────────────────────────────────────────────── -->
    <div class="col-xl-5 col-lg-6 mb-4">

      <div class="card border-0 mb-4">
        <div class="card-body">
          <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
            <i class="mdi mdi-tune me-2" style="color:var(--stm-navy);"></i>Shape &amp; Wording
          </h6>

          <div class="form-group mb-3">
            <label class="form-label fw-bold" style="font-size:0.82rem;">Size</label>
            <div class="filter-chips" id="sizeChips">
              <a href="#" class="filter-chip is-active" data-w="1080" data-h="1350">Portrait · post</a>
              <a href="#" class="filter-chip" data-w="1080" data-h="1080">Square</a>
              <a href="#" class="filter-chip" data-w="1080" data-h="1920">Story</a>
            </div>
          </div>

          <div class="form-group mb-3">
            <label class="form-label fw-bold" style="font-size:0.82rem;">Colour</label>
            <div class="filter-chips" id="themeChips">
              <a href="#" class="filter-chip is-active" data-theme="crimson">Crimson</a>
              <a href="#" class="filter-chip" data-theme="navy">STM Navy</a>
              <a href="#" class="filter-chip" data-theme="ink">Midnight</a>
            </div>
          </div>

          <div class="row">
            <div class="col-sm-6 form-group mb-3">
              <label for="headline" class="form-label fw-bold" style="font-size:0.82rem;">Headline</label>
              <input type="text" id="headline" class="form-control form-control-sm" maxlength="28"
                     value="<?= h($poster['banner']) ?>">
            </div>
            <div class="col-sm-6 form-group mb-3">
              <label for="footnote" class="form-label fw-bold" style="font-size:0.82rem;">Closing Line</label>
              <input type="text" id="footnote" class="form-control form-control-sm" maxlength="52"
                     value="Please forward — someone you know can help.">
            </div>
          </div>

          <div class="form-group mb-0">
            <label class="form-label fw-bold d-block" style="font-size:0.82rem;">Show on the poster</label>
            <div class="d-flex flex-wrap gap-3">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="showPatient"
                       <?= $poster['patient'] ? 'checked' : '' ?> <?= $poster['patient'] ? '' : 'disabled' ?>>
                <label class="form-check-label" for="showPatient" style="font-size:0.84rem;">
                  <?= $poster['patient'] ? "Patient's name" : 'No patient name recorded' ?>
                </label>
              </div>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="showNote"
                       <?= $poster['reason'] ? 'checked' : 'disabled' ?>>
                <label class="form-check-label" for="showNote" style="font-size:0.84rem;">
                  <?= $poster['reason'] ? 'Why it is needed' : 'No reason recorded' ?>
                </label>
              </div>
            </div>
            <small class="text-muted" style="font-size:0.74rem;">
              Leave the name off when the family would rather not be named publicly.
            </small>
          </div>
        </div>
      </div>

      <div class="card border-0">
        <div class="card-body">
          <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
            <i class="mdi mdi-share-variant me-2" style="color:var(--stm-navy);"></i>Share It
          </h6>

          <div class="d-grid gap-2 mb-3">
            <button type="button" class="btn btn-gradient-danger fw-bold" id="downloadBtn">
              <i class="mdi mdi-download me-1"></i>Download Poster (PNG)
            </button>
            <a href="<?= h($wa_url) ?>" target="_blank" rel="noopener" class="btn btn-gradient-success fw-bold">
              <i class="mdi mdi-whatsapp me-1"></i>Open in WhatsApp
            </a>
            <button type="button" class="btn btn-light border" id="copyBtn">
              <i class="mdi mdi-content-copy me-1"></i>Copy the Appeal Text
            </button>
          </div>

          <label class="form-label fw-bold" style="font-size:0.82rem;">Appeal Text</label>
          <textarea id="shareText" class="form-control" rows="11"
                    style="font-size:0.8rem;font-family:inherit;"><?= h($share_text) ?></textarea>
          <small class="text-muted" style="font-size:0.72rem;">
            Edit freely before copying. The poster image is separate from this text.
          </small>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
(function () {
    "use strict";

    var DATA = <?= json_encode($poster, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var canvas = document.getElementById('posterCanvas');
    var ctx    = canvas.getContext('2d');

    /* The STM mark is served from this site, not stmorg.in: a cross-origin
       image taints the canvas and toBlob() then refuses to export it. */
    var logo = new Image();
    var logoReady = false;
    logo.onload  = function () { logoReady = true; render(); };
    logo.onerror = function () { logoReady = false; render(); };
    logo.src = DATA.logo;

    var THEMES = {
        crimson: { a: '#7f1d16', b: '#c0392b', ink: '#8e2318', band: '#a02a1e', soft: '#ffe3de' },
        navy:    { a: '#161a3c', b: '#3a3f7a', ink: '#24285b', band: '#1f2350', soft: '#dfe2f5' },
        ink:     { a: '#111827', b: '#374151', ink: '#111827', band: '#1f2937', soft: '#e5e7eb' }
    };

    var state = { w: 1080, h: 1350, theme: 'crimson' };

    // ── Primitives ──────────────────────────────────────────────────────────

    function roundRect(x, y, w, h, r) {
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y,     x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x,     y + h, r);
        ctx.arcTo(x,     y + h, x,     y,     r);
        ctx.arcTo(x,     y,     x + w, y,     r);
        ctx.closePath();
    }

    function drop(cx, cy, size) {
        ctx.beginPath();
        ctx.moveTo(cx, cy - size);
        ctx.bezierCurveTo(cx + size * 0.95, cy - size * 0.1,
                          cx + size * 0.72, cy + size * 0.78,
                          cx, cy + size * 0.78);
        ctx.bezierCurveTo(cx - size * 0.72, cy + size * 0.78,
                          cx - size * 0.95, cy - size * 0.1,
                          cx, cy - size);
        ctx.closePath();
    }

    function text(str, x, y, opts) {
        opts = opts || {};
        ctx.save();
        ctx.fillStyle    = opts.color  || '#ffffff';
        ctx.textAlign    = opts.align  || 'center';
        ctx.textBaseline = opts.base   || 'alphabetic';
        ctx.font         = opts.font;
        if (opts.track) ctx.letterSpacing = opts.track;
        ctx.fillText(str, x, y);
        ctx.restore();
    }

    // Fits a string to a width by stepping the size down, then returns the font
    // used — so a long hospital name shrinks instead of running off the edge.
    function fitFont(str, maxWidth, weight, startPx, minPx) {
        var px = startPx;
        while (px > minPx) {
            ctx.font = weight + ' ' + px + 'px Inter, Arial, sans-serif';
            if (ctx.measureText(str).width <= maxWidth) break;
            px -= 2;
        }
        return weight + ' ' + px + 'px Inter, Arial, sans-serif';
    }

    function wrap(str, maxWidth, font, maxLines) {
        ctx.font = font;
        var words = String(str || '').split(/\s+/).filter(Boolean);
        var lines = [], line = '';

        for (var i = 0; i < words.length; i++) {
            var test = line ? line + ' ' + words[i] : words[i];
            if (ctx.measureText(test).width > maxWidth && line) {
                lines.push(line);
                line = words[i];
            } else { line = test; }
        }
        if (line) lines.push(line);

        if (maxLines && lines.length > maxLines) {
            lines = lines.slice(0, maxLines);
            lines[maxLines - 1] = lines[maxLines - 1].replace(/.{3}$/, '…');
        }
        return lines;
    }

    function logoCircle(cx, cy, r) {
        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.fillStyle = '#ffffff';
        ctx.fill();

        if (logoReady) {
            ctx.clip();
            ctx.drawImage(logo, cx - r, cy - r, r * 2, r * 2);
        }
        ctx.restore();

        ctx.save();
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(255,255,255,0.65)';
        ctx.lineWidth = Math.max(2, r * 0.06);
        ctx.stroke();
        ctx.restore();
    }

    // ── The poster ──────────────────────────────────────────────────────────

    function draw() {
        var W = state.w, H = state.h, T = THEMES[state.theme];
        /* One scale for everything, taken from whichever dimension is tighter.
           Driving it off height alone made the story format render 1350-tall
           type on a canvas still only 1080 wide, and the footer ran off the
           edge. Taller canvases now keep the same type and spend the extra
           room on spacing instead. */
        var u  = Math.min(W / 1080, H / 1350);
        var cx = W / 2;
        var pad = 72;
        var inner = W - pad * 2;

        canvas.width = W; canvas.height = H;

        /* Work out how tall the variable part is before drawing anything, so
           the block can be placed rather than just flowed from the top. On a
           story canvas that is 570px taller than a portrait one, flowing left a
           dead gap in the middle. */
        var showPatient = document.getElementById('showPatient');
        var showNote    = document.getElementById('showNote');

        var rows = [];
        if (DATA.patient && showPatient && showPatient.checked) rows.push(['PATIENT', DATA.patient]);
        rows.push(['HOSPITAL', DATA.hospital]);
        if (DATA.place)    rows.push(['LOCATION', DATA.place]);
        if (DATA.neededOn) rows.push(['REQUIRED BY', DATA.neededOn]);
        if (DATA.reason && showNote && showNote.checked) rows.push(['WHY', DATA.reason]);

        // Background
        var bg = ctx.createLinearGradient(0, 0, W * 0.3, H);
        bg.addColorStop(0, T.a);
        bg.addColorStop(1, T.b);
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, W, H);

        // Watermark drops, kept faint so they read as texture not clutter
        ctx.save();
        ctx.globalAlpha = 0.06;
        ctx.fillStyle = '#ffffff';
        drop(W - 90, H * 0.30, 240 * u); ctx.fill();
        drop(70, H * 0.62, 180 * u);     ctx.fill();
        drop(W * 0.82, H * 0.88, 130 * u); ctx.fill();
        ctx.restore();

        // Heights of everything between the masthead and the call to action.
        var _labelFont = '700 ' + (20 * u) + 'px Inter, Arial, sans-serif';
        var _valueFont = '600 ' + (31 * u) + 'px Inter, Arial, sans-serif';
        var _rowGap    = 20 * u;
        var _labelW    = 215 * u;
        var _measured  = rows.map(function (r) {
            return wrap(r[1], inner - _labelW - 76 * u, _valueFont, 2);
        });
        var _panelH = 36 * u;
        _measured.forEach(function (lines) { _panelH += lines.length * (38 * u) + _rowGap; });

        var logoR   = 54 * u;
        var _ribbonH = 72 * u;
        var _cardH   = 352 * u;
        var _ctaH    = 192 * u;
        var _ctaY    = H - _ctaH - 128 * u;

        var _top    = 56 * u + logoR;
        var _flow   = logoR + 34 * u + 2 + 42 * u + _ribbonH + 46 * u + _cardH + 38 * u + _panelH;
        var _slack  = _ctaY - (_top + _flow) - 40 * u;

        // ── Masthead ────────────────────────────────────────────────────────
        var y = _top + (_slack > 0 ? _slack * 0.30 : 0);
        logoCircle(pad + logoR, y, logoR);

        var tx = pad + logoR * 2 + 26 * u;
        text('SERVICE TO MANKIND', tx, y - 6 * u, {
            align: 'left', color: '#ffffff',
            font: fitFont('SERVICE TO MANKIND', inner - logoR * 2 - 30 * u, '800', 44 * u, 26 * u)
        });
        text('LOVE ALL · SERVE ALL', tx, y + 28 * u, {
            align: 'left', color: 'rgba(255,255,255,0.72)',
            font: '600 ' + (21 * u) + 'px Inter, Arial, sans-serif', track: '2px'
        });

        y += logoR + 34 * u;
        ctx.fillStyle = 'rgba(255,255,255,0.18)';
        ctx.fillRect(pad, y, inner, Math.max(1, 2 * u));

        // ── Urgency ribbon ──────────────────────────────────────────────────
        y += 42 * u;
        var head = (document.getElementById('headline').value || DATA.banner).toUpperCase();
        var ribbonH = _ribbonH;
        var ribbonFont = fitFont(head, inner - 90 * u, '800', 42 * u, 24 * u);

        ctx.save();
        ctx.fillStyle = T.band;
        ctx.shadowColor = 'rgba(0,0,0,0.25)';
        ctx.shadowBlur = 24 * u;
        ctx.shadowOffsetY = 8 * u;
        roundRect(pad, y, inner, ribbonH, ribbonH / 2);
        ctx.fill();
        ctx.restore();

        text(head, cx, y + ribbonH * 0.65, { color: '#ffffff', font: ribbonFont, track: '3px' });

        // ── Blood group hero ────────────────────────────────────────────────
        y += ribbonH + 46 * u;
        var cardH = _cardH;

        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.shadowColor = 'rgba(0,0,0,0.22)';
        ctx.shadowBlur = 40 * u;
        ctx.shadowOffsetY = 14 * u;
        roundRect(pad, y, inner, cardH, 34 * u);
        ctx.fill();
        ctx.restore();

        // A soft halo behind the letters. A drop shape read as a stray blob
        // once the group sat on top of it; a circle stays symmetrical.
        ctx.save();
        ctx.globalAlpha = 0.55;
        ctx.fillStyle = T.soft;
        ctx.beginPath();
        ctx.arc(cx, y + cardH * 0.50, 128 * u, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        text('BLOOD GROUP NEEDED', cx, y + 52 * u, {
            color: '#9aa1b5', font: '700 ' + (22 * u) + 'px Inter, Arial, sans-serif', track: '4px'
        });

        text(DATA.group, cx, y + cardH * 0.70, {
            color: T.ink,
            font: fitFont(DATA.group, inner - 180 * u, '800', 168 * u, 96 * u)
        });

        var unitsLine = DATA.units + (DATA.units === '1' ? ' UNIT REQUIRED' : ' UNITS REQUIRED');
        text(unitsLine, cx, y + cardH - 36 * u, {
            color: T.ink, font: '700 ' + (32 * u) + 'px Inter, Arial, sans-serif', track: '1px'
        });

        // ── Details ─────────────────────────────────────────────────────────
        y += cardH + 38 * u;

        var labelFont = _labelFont, valueFont = _valueFont, rowGap = _rowGap, labelW = _labelW;

        var measured = _measured, panelH = _panelH;

        ctx.save();
        ctx.fillStyle = 'rgba(255,255,255,0.12)';
        roundRect(pad, y, inner, panelH, 26 * u);
        ctx.fill();
        ctx.restore();

        var ry = y + 46 * u;
        rows.forEach(function (r, i) {
            text(r[0], pad + 34 * u, ry, {
                align: 'left', color: 'rgba(255,255,255,0.72)', font: labelFont, track: '2px'
            });
            measured[i].forEach(function (ln, k) {
                text(ln, pad + labelW, ry + k * (38 * u), {
                    align: 'left', color: '#ffffff', font: valueFont
                });
            });
            ry += measured[i].length * (38 * u) + rowGap;
        });

        // ── A line for the space a taller canvas leaves ─────────────────────
        if (_slack > 150 * u) {
            // _ctaY, not ctaY — the latter is not assigned until below, and
            // reading it here silently produced NaN coordinates.
            var mid = (y + panelH + _ctaY) / 2;
            text('YOUR ONE DONATION', cx, mid - 6 * u, {
                color: 'rgba(255,255,255,0.92)',
                font: '800 ' + (46 * u) + 'px Inter, Arial, sans-serif', track: '1px'
            });
            text('CAN SAVE A LIFE TODAY', cx, mid + 48 * u, {
                color: 'rgba(255,255,255,0.92)',
                font: '800 ' + (46 * u) + 'px Inter, Arial, sans-serif', track: '1px'
            });
        }

        // ── Call to action ──────────────────────────────────────────────────
        var ctaH = _ctaH, ctaY = _ctaY;

        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.shadowColor = 'rgba(0,0,0,0.22)';
        ctx.shadowBlur = 36 * u;
        ctx.shadowOffsetY = 12 * u;
        roundRect(pad, ctaY, inner, ctaH, 30 * u);
        ctx.fill();
        ctx.restore();

        text('CALL TO DONATE', cx, ctaY + 48 * u, {
            color: '#9aa1b5', font: '700 ' + (21 * u) + 'px Inter, Arial, sans-serif', track: '4px'
        });

        var phone = DATA.mobile + (DATA.altMobile ? '  ·  ' + DATA.altMobile : '');
        text(phone, cx, ctaY + 118 * u, {
            color: T.ink, font: fitFont(phone, inner - 80 * u, '800', 60 * u, 34 * u)
        });

        text(DATA.contact, cx, ctaY + 162 * u, {
            color: '#5b6478', font: '600 ' + (28 * u) + 'px Inter, Arial, sans-serif'
        });

        // ── Footer ──────────────────────────────────────────────────────────
        var fy = H - 86 * u;
        text(document.getElementById('footnote').value || '', cx, fy, {
            color: 'rgba(255,255,255,0.95)',
            font: '700 ' + (27 * u) + 'px Inter, Arial, sans-serif'
        });

        text('One donation can save up to three lives  ·  stmorg.in  ·  ' + DATA.requestNo, cx, fy + 38 * u, {
            color: 'rgba(255,255,255,0.55)',
            font: '500 ' + (20 * u) + 'px Inter, Arial, sans-serif'
        });
    }

    // Web fonts arrive after this runs, so draw now and again once Inter and
    // the logo are both in.
    function render() {
        draw();
        if (document.fonts && document.fonts.ready) document.fonts.ready.then(draw);
    }

    // ── Controls ────────────────────────────────────────────────────────────

    function bindChips(id, apply) {
        var box = document.getElementById(id);
        if (!box) return;
        box.addEventListener('click', function (e) {
            var chip = e.target.closest('.filter-chip');
            if (!chip) return;
            e.preventDefault();
            box.querySelectorAll('.filter-chip').forEach(function (c) { c.classList.remove('is-active'); });
            chip.classList.add('is-active');
            apply(chip);
            render();
        });
    }

    bindChips('sizeChips', function (chip) {
        state.w = parseInt(chip.getAttribute('data-w'), 10);
        state.h = parseInt(chip.getAttribute('data-h'), 10);
    });
    bindChips('themeChips', function (chip) { state.theme = chip.getAttribute('data-theme'); });

    ['headline', 'footnote'].forEach(function (id) {
        document.getElementById(id).addEventListener('input', render);
    });
    ['showPatient', 'showNote'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', render);
    });

    // ── Download ────────────────────────────────────────────────────────────
    document.getElementById('downloadBtn').addEventListener('click', function () {
        draw();
        var name = 'STM-blood-' + DATA.group.replace('+', 'pos').replace('-', 'neg')
                 + '-' + DATA.requestNo.replace(/[^A-Za-z0-9]/g, '') + '.png';

        canvas.toBlob(function (blob) {
            if (!blob) { alert('The poster could not be saved. Try a different browser.'); return; }
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url; a.download = name;
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 2000);
        }, 'image/png');
    });

    // ── Copy the appeal ─────────────────────────────────────────────────────
    document.getElementById('copyBtn').addEventListener('click', function () {
        var box = document.getElementById('shareText');
        var btn = this;
        var done = function (ok) {
            btn.innerHTML = ok ? '<i class="mdi mdi-check me-1"></i>Copied'
                               : '<i class="mdi mdi-alert-outline me-1"></i>Select it and copy by hand';
            setTimeout(function () {
                btn.innerHTML = '<i class="mdi mdi-content-copy me-1"></i>Copy the Appeal Text';
            }, 2500);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(box.value).then(function () { done(true); }, function () { done(false); });
        } else {
            box.select();
            done(document.execCommand('copy'));
        }
    });

    render();
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
