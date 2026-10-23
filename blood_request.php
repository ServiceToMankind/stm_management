<?php
include 'includes/header.php';

$rid = isset($_GET['rid']) && is_numeric($_GET['rid']) ? (int)$_GET['rid'] : 0;
if ($rid < 1) {
    set_flash('danger', 'No requirement was specified.');
    redirect('blood_requests');
}

// ── Log a donation against this requirement ──────────────────────────────────
if (isset($_POST['_donate'])) {
    $resp = json_decode(get_api_data_post("$api_url/blood/donations", with_actor([
        'donor_id'         => $_POST['donor_id']   ?? '',
        'blood_request_id' => $rid,
        'donated_on'       => $_POST['donated_on'] ?? '',
        'units'            => $_POST['units']      ?? '1',
        'hospital'         => $_POST['hospital']   ?? '',
        'recipient_name'   => $_POST['recipient_name'] ?? '',
        'remarks'          => $_POST['remarks']    ?? '',
        'confirm'          => !empty($_POST['confirm']) ? '1' : '',
    ])), true);

    if (($resp['status'] ?? '') === 'success') {
        set_flash('success', $resp['message'] ?? 'Donation logged.');
    } elseif (($resp['reason'] ?? '') === 'needs_confirm') {
        // Looks like a double entry — say so and let them confirm.
        set_flash('warning', ($resp['message'] ?? '') . ' Tick "log it anyway" to go ahead.');
    } else {
        set_flash('danger', $resp['message'] ?? 'Could not log the donation.');
    }

    redirect('blood_request?rid=' . $rid);
}

// ── Close / cancel / delete ──────────────────────────────────────────────────
if (isset($_GET['action']) && in_array($_GET['action'], ['close', 'cancel', 'delete'], true)) {
    $resp = json_decode(get_api_data(
        "$api_url/blood/manage_request?action=" . $_GET['action'] . "&rid=$rid&" . actor_qs()), true);
    $ok = ($resp['status'] ?? '') === 'success';

    set_flash($ok ? 'success' : 'danger', $resp['message'] ?? 'Could not complete that.');
    redirect('' . ($ok && $_GET['action'] === 'delete' ? 'blood_requests' : 'blood_request?rid=' . $rid));
}

$resp = json_decode(get_api_data("$api_url/blood/requests?rid=$rid"), true);
$r    = $resp['data'][0] ?? null;

if (!$r) {
    set_flash('danger', 'That requirement no longer exists.');
    redirect('blood_requests');
}

$donations  = $resp['donations']  ?? [];
$matches    = $resp['matches']    ?? [];
$compatible = $resp['compatible'] ?? [];
$gap_days   = $resp['gap_days']   ?? 90;

$is_live   = in_array($r['status'], ['open', 'partially_arranged'], true);
$is_raiser = $r['raised_by'] === portal_uid();
$can_close = $is_raiser || is_admin();
$short     = (float)$r['units_short'];
$pct       = (float)$r['units_needed'] > 0
               ? min(100, round((float)$r['units_arranged'] / (float)$r['units_needed'] * 100))
               : 0;

$urgency_tone  = ['critical' => 'danger', 'urgent' => 'warning', 'routine' => 'info'];
$urgency_label = ['critical' => 'Critical', 'urgent' => 'Urgent', 'routine' => 'Routine'];
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-water"></i></span>
      <?= h($r['blood_group']) ?> · <?= h($r['request_no'] ?: 'Requirement #' . $r['id']) ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="blood_requests" class="btn btn-sm btn-light border"><i class="mdi mdi-arrow-left me-1"></i>Back</a>
      <a href="blood_poster?rid=<?= $rid ?>" class="btn btn-sm btn-gradient-danger">
        <i class="mdi mdi-image-outline me-1"></i>Poster
      </a>
      <?php if ($is_live && ($is_raiser || is_admin())): ?>
        <a href="manage_blood_request?rid=<?= $rid ?>" class="btn btn-sm btn-gradient-info">
          <i class="mdi mdi-pencil me-1"></i>Edit
        </a>
      <?php endif; ?>
      <?php if ($is_live && $can_close): ?>
        <a href="blood_request?rid=<?= $rid ?>&action=close" class="btn btn-sm btn-gradient-success"
           onclick="return confirm('Mark this requirement as done?')">
          <i class="mdi mdi-check me-1"></i>Mark Done
        </a>
        <a href="blood_request?rid=<?= $rid ?>&action=cancel" class="btn btn-sm btn-outline-secondary"
           onclick="return confirm('Cancel this requirement? Use this when the blood is no longer needed.')">Cancel</a>
      <?php endif; ?>
      <?php if (is_admin() && empty($donations)): ?>
        <a href="blood_request?rid=<?= $rid ?>&action=delete" class="btn btn-sm btn-gradient-danger"
           onclick="return confirm('Delete this requirement for good?')"><i class="mdi mdi-delete"></i></a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">

    <!-- ── The need ─────────────────────────────────────────────────────── -->
    <div class="col-lg-4 mb-4">
      <div class="card border-0 mb-4">
        <div class="card-body px-4 py-4">

          <div class="text-center mb-4">
            <div style="font-size:3.4rem;font-weight:800;color:var(--stm-blood);line-height:1;">
              <?= h($r['blood_group']) ?>
            </div>
            <div style="font-size:0.9rem;font-weight:600;color:#2c3250;margin-top:6px;">
              <?php if ($is_live && $short > 0): ?>
                <?= units_text($short) ?> more unit<?= $short == 1 ? '' : 's' ?> needed
              <?php else: ?>
                <?= units_text($r['units_arranged']) ?> of <?= units_text($r['units_needed']) ?> unit(s) arranged
              <?php endif; ?>
            </div>
            <div class="fund-bar mt-2"><span style="width:<?= $pct ?>%;"></span></div>
            <div class="mt-3 d-flex gap-2 justify-content-center">
              <?= blood_status_badge($r['status']) ?>
              <span class="badge-gradient-<?= $urgency_tone[$r['urgency']] ?? 'info' ?>">
                <?= h($urgency_label[$r['urgency']] ?? $r['urgency']) ?>
              </span>
            </div>
          </div>

          <dl class="detail-list">
            <dt>Patient</dt>
            <dd>
              <?= $r['patient_name'] ? h($r['patient_name']) : '—' ?>
              <?php if ($r['patient_age']): ?> · <?= h($r['patient_age']) ?><?php endif; ?>
            </dd>

            <dt>Hospital</dt>
            <dd><?= h($r['hospital']) ?></dd>

            <dt>Location</dt>
            <dd><?= h(trim(($r['area'] ?: '') . ($r['area'] && $r['city'] ? ', ' : '') . ($r['city'] ?: ''))) ?: '—' ?></dd>

            <dt>Needed By</dt>
            <dd><?= nice_date($r['needed_on']) ?></dd>

            <dt>Contact</dt>
            <dd>
              <?= h($r['contact_name']) ?><br>
              <a href="tel:<?= h($r['contact_mobile']) ?>"><?= h($r['contact_mobile']) ?></a>
              <?php if ($r['alt_mobile']): ?>
                <br><a href="tel:<?= h($r['alt_mobile']) ?>"><?= h($r['alt_mobile']) ?></a>
              <?php endif; ?>
            </dd>

            <?php if (!empty($r['reason'])): ?>
              <dt>Why It Is Needed</dt>
              <dd style="white-space:pre-line;"><?= h($r['reason']) ?></dd>
            <?php endif; ?>

            <?php if ($r['notes']): ?>
              <dt>Internal Notes</dt>
              <dd style="white-space:pre-line;"><?= h($r['notes']) ?></dd>
            <?php endif; ?>

            <?php if ($r['ticket_id']): ?>
              <dt>Ticket</dt>
              <dd><a href="ticket?rid=<?= (int)$r['ticket_id'] ?>"><?= h($r['ticket_no'] ?: '#' . $r['ticket_id']) ?></a></dd>
            <?php endif; ?>
          </dl>

          <div style="font-size:0.72rem;color:var(--stm-muted);margin-top:16px;">
            Posted by <?= h($r['raised_by_name'] ?: '—') ?> on <?= nice_datetime($r['created_at']) ?>
          </div>

        </div>
      </div>

      <!-- Who has turned up -->
      <div class="card border-0">
        <div class="card-body px-4 py-4">
          <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
            <i class="mdi mdi-heart-outline me-2" style="color:var(--stm-green-dark);"></i>Donated So Far
          </h6>

          <?php if (empty($donations)): ?>
            <p class="text-muted mb-0" style="font-size:0.85rem;">
              Nobody yet. Log each donation as it happens — it keeps the units count honest and
              marks the donor as recently given.
            </p>
          <?php else: ?>
            <div class="stm-timeline">
              <?php foreach ($donations as $d): ?>
                <div class="stm-timeline-item is-status">
                  <div class="d-flex justify-content-between align-items-baseline gap-2">
                    <span class="stm-timeline-who"><?= h($d['name']) ?></span>
                    <span class="stm-timeline-when"><?= nice_date($d['donated_on']) ?></span>
                  </div>
                  <p class="stm-timeline-note">
                    <strong style="color:var(--stm-blood);"><?= h($d['blood_group']) ?></strong>
                    · <?= units_text($d['units']) ?> unit(s)
                    <?php if ($d['mobile']): ?>
                      <br><a href="tel:<?= h($d['mobile']) ?>" style="font-size:0.8rem;"><?= h($d['mobile']) ?></a>
                    <?php endif; ?>
                    <?php if ($d['remarks']): ?><br><?= h($d['remarks']) ?><?php endif; ?>
                  </p>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- ── Who to call ──────────────────────────────────────────────────── -->
    <div class="col-lg-8 mb-4">

      <?php if ($is_live): ?>
        <!-- Log a donation -->
        <div class="card border-0 mb-4">
          <div class="card-body px-4 py-4">
            <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-water-outline me-2" style="color:var(--stm-blood);"></i>Log a Donation
            </h6>

            <form method="POST" action="blood_request?rid=<?= $rid ?>" class="row g-2 align-items-end">
              <input type="hidden" name="_donate" value="1">

              <!-- Typed, not picked from a list: the registry is far too long for
                   a dropdown once it holds every member. -->
              <div class="col-md-4 position-relative" id="donorPickBlock">
                <label for="donorPick" class="form-label fw-bold" style="font-size:0.8rem;">Donor <span class="text-danger">*</span></label>
                <input type="text" id="donorPick" class="form-control form-control-sm"
                       placeholder="Type a name, number or area…" autocomplete="off">
                <input type="hidden" name="donor_id" id="donorId">
                <div id="donorSug" class="position-absolute bg-white border rounded shadow-sm"
                     style="z-index:1060;display:none;max-height:260px;overflow-y:auto;left:12px;right:12px;top:100%;"></div>
                <small class="text-muted" style="font-size:0.7rem;">
                  Eligible, compatible donors first.
                </small>
              </div>
              <div class="col-md-2">
                <label for="donated_on" class="form-label fw-bold" style="font-size:0.8rem;">Date</label>
                <input type="date" name="donated_on" id="donated_on" class="form-control form-control-sm"
                       value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
              </div>
              <div class="col-md-2">
                <label for="units" class="form-label fw-bold" style="font-size:0.8rem;">Units</label>
                <input type="number" step="0.5" min="0.5" max="5" name="units" id="units"
                       class="form-control form-control-sm" value="1">
              </div>
              <div class="col-md-4">
                <label for="hospital" class="form-label fw-bold" style="font-size:0.8rem;">Where</label>
                <input type="text" name="hospital" id="hospital" class="form-control form-control-sm"
                       value="<?= h($r['hospital']) ?>">
              </div>

              <div class="col-md-8">
                <label for="remarks" class="form-label fw-bold" style="font-size:0.8rem;">Remarks</label>
                <input type="text" name="remarks" id="remarks" class="form-control form-control-sm"
                       maxlength="255" placeholder="Optional">
              </div>
              <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-sm btn-gradient-success w-100">
                  <i class="mdi mdi-check me-1"></i>Log It
                </button>
              </div>

              <div class="col-12">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirm">
                  <label class="form-check-label text-muted" for="confirm" style="font-size:0.74rem;">
                    Log it anyway — overrides the <?= $gap_days ?>-day gap and blood-group checks
                  </label>
                </div>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

      <!-- The call list -->
      <div class="card border-0">
        <div class="card-body px-0 pb-0">
          <div class="d-flex justify-content-between align-items-center px-4 pb-3 flex-wrap gap-2">
            <h6 class="mb-0" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-phone-outline me-2" style="color:var(--stm-navy);"></i>Donors to Call
            </h6>
            <span class="text-muted" style="font-size:0.76rem;">
              Compatible groups: <?= h(implode(', ', $compatible)) ?> · same area first
            </span>
          </div>

          <?php if (empty($matches)): ?>
            <div class="stm-empty">
              <i class="mdi mdi-account-off-outline"></i>
              <p>Nobody on the registry is eligible for <?= h($r['blood_group']) ?> right now.</p>
              <small>
                Share the poster so volunteers can find a donor outside STM, and add whoever
                comes forward to the registry.
              </small>
              <div class="mt-4">
                <a href="blood_poster?rid=<?= $rid ?>" class="btn btn-gradient-danger">
                  <i class="mdi mdi-image-outline me-1"></i>Download the Poster
                </a>
                <a href="manage_blood_donor?group=<?= urlencode($r['blood_group']) ?>" class="btn btn-light border ms-2">
                  Add a Donor
                </a>
              </div>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th style="padding-left:20px;">Donor</th>
                    <th>Group</th>
                    <th>Area</th>
                    <th>Last Donated</th>
                    <th style="padding-right:20px;text-align:center;">Call</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($matches as $m): ?>
                    <tr>
                      <td style="padding-left:20px;">
                        <div style="font-weight:600;color:#2c3250;"><?= h($m['name']) ?></div>
                        <div style="font-size:0.74rem;color:var(--stm-text-muted);">
                          <?= h($m['mobile']) ?>
                          <?php if ((int)$m['total_donations'] > 0): ?>
                            · <?= (int)$m['total_donations'] ?> donation<?= (int)$m['total_donations'] == 1 ? '' : 's' ?>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td>
                        <span style="font-weight:800;color:var(--stm-blood);"><?= h($m['blood_group']) ?></span>
                        <?php if ((int)$m['exact_match'] !== 1): ?>
                          <div><span class="badge-stm-muted">compatible</span></div>
                        <?php endif; ?>
                      </td>
                      <td style="font-size:0.82rem;">
                        <?= $m['area'] ? h($m['area']) : '<span class="text-muted">—</span>' ?>
                        <?php if ((int)$m['same_area'] === 1): ?>
                          <div><span class="badge-gradient-success">same area</span></div>
                        <?php endif; ?>
                      </td>
                      <td style="font-size:0.82rem;white-space:nowrap;">
                        <?php if ($m['last_donated_on']): ?>
                          <?= nice_date($m['last_donated_on']) ?>
                          <div style="font-size:0.72rem;color:var(--stm-text-muted);"><?= (int)$m['days_since'] ?> days ago</div>
                        <?php else: ?>
                          <span class="text-muted">Not yet</span>
                        <?php endif; ?>
                      </td>
                      <td style="padding-right:20px;text-align:center;white-space:nowrap;">
                        <a href="tel:<?= h($m['mobile']) ?>" class="btn btn-gradient-success btn-xs me-1" title="Call">
                          <i class="mdi mdi-phone"></i>
                        </a>
                        <?php if ($m['alt_mobile']): ?>
                          <a href="tel:<?= h($m['alt_mobile']) ?>" class="btn btn-light border btn-xs" title="Alternate number">
                            <i class="mdi mdi-phone-plus"></i>
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

</div>
<style>
#donorSug .sug { padding: 9px 12px; cursor: pointer; border-bottom: 1px solid #f2f4f8; }
#donorSug .sug:last-child { border-bottom: none; }
#donorSug .sug:hover, #donorSug .sug.on { background: #f5f7ff; }
#donorSug .sug-n { font-weight: 600; font-size: 0.85rem; color: #2c3250; }
#donorSug .sug-m { font-size: 0.74rem; color: #8a93b0; }
#donorSug .sug-g { font-weight: 800; color: var(--stm-blood); }
#donorSug .sug-add {
  padding: 9px 12px; background: #fffbea; color: #856404;
  font-size: 0.8rem; font-weight: 600; border-top: 1px solid #f0e68c; display: block;
}
#donorSug .sug-add:hover { background: #fff6d6; text-decoration: none; }
</style>

<script>
(function () {
    "use strict";
    var pick = document.getElementById('donorPick');
    if (!pick) return;                              // requirement already closed

    var API_BASE = "<?= rtrim($api_url, '/') ?>";
    var GROUP    = <?= json_encode($r['blood_group']) ?>;
    var sug      = document.getElementById('donorSug');
    var hidden   = document.getElementById('donorId');

    // The compatible, eligible donors the page already worked out — offered
    // before anyone types, since they are the ones worth ringing.
    var SHORTLIST = <?= json_encode(array_map(function ($m) {
        return ['id' => (int)$m['id'], 'name' => $m['name'], 'mobile' => $m['mobile'],
                'blood_group' => $m['blood_group'], 'area' => $m['area'], 'match' => 1];
    }, array_slice($matches, 0, 8)), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    var timer = null, chosen = false;

    function esc(t) {
        return String(t == null ? '' : t)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function close() { sug.style.display = 'none'; }

    function render(list, q) {
        sug.innerHTML = '';

        list.forEach(function (d) {
            var el = document.createElement('div');
            el.className = 'sug';
            el.innerHTML =
                '<div class="sug-n">' + esc(d.name) +
                    ' <span class="sug-g">' + esc(d.blood_group) + '</span>' +
                    (d.match ? ' <span class="badge-gradient-success">ready</span>' : '') +
                '</div>' +
                '<div class="sug-m">' + esc(d.mobile || '') +
                    (d.area ? ' · ' + esc(d.area) : '') + '</div>';
            el.addEventListener('mousedown', function () { choose(d); });
            sug.appendChild(el);
        });

        if (!list.length) {
            var none = document.createElement('div');
            none.className = 'sug-m';
            none.style.padding = '10px 12px';
            none.textContent = 'Nobody on the registry matches that.';
            sug.appendChild(none);
        }

        var add = document.createElement('a');
        add.className = 'sug-add';
        add.target = '_blank';
        add.href = 'manage_blood_donor?group=' + encodeURIComponent(GROUP);
        add.innerHTML = '<i class="mdi mdi-account-plus"></i> Not on the registry? Add ' +
                        (q ? '"<strong>' + esc(q) + '</strong>"' : 'a donor') + ' as a new donor';
        sug.appendChild(add);

        sug.style.display = 'block';
    }

    function choose(d) {
        chosen = true;
        hidden.value = d.id;
        pick.value = d.name + ' — ' + d.blood_group + (d.area ? ', ' + d.area : '');
        pick.classList.remove('is-invalid');
        close();
    }

    function search(q) {
        fetch(API_BASE + '/blood/donors?q=' + encodeURIComponent(q) + '&limit=8')
            .then(function (r) { return r.json(); })
            .then(function (j) { render(Array.isArray(j.data) ? j.data : [], q); })
            .catch(close);
    }

    pick.addEventListener('focus', function () {
        if (!this.value.trim() && SHORTLIST.length) render(SHORTLIST, '');
    });

    pick.addEventListener('input', function () {
        chosen = false;
        hidden.value = '';
        clearTimeout(timer);

        var q = this.value.trim();
        if (q.length < 2) {
            if (SHORTLIST.length) render(SHORTLIST, ''); else close();
            return;
        }
        timer = setTimeout(function () { search(q); }, 280);
    });

    pick.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#donorPickBlock')) close();
    });

    // Don't post a donation with nobody attached to it.
    pick.form.addEventListener('submit', function (e) {
        if (!hidden.value) {
            e.preventDefault();
            pick.classList.add('is-invalid');
            pick.focus();
            if (!chosen && pick.value.trim().length >= 2) search(pick.value.trim());
            else if (SHORTLIST.length) render(SHORTLIST, '');
        }
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
</div>
