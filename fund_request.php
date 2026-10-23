<?php
include 'includes/header.php';

$rid = isset($_GET['rid']) && is_numeric($_GET['rid']) ? (int)$_GET['rid'] : 0;
if ($rid < 1) {
    set_flash('danger', 'No fund request was specified.');
    redirect('fund_requests');
}

// ── The treasurer's decision ─────────────────────────────────────────────────
if (isset($_POST['_decide'])) {
    $resp = get_api_data_post("$api_url/funds/review_fund_request", with_actor([
        'rid'            => $rid,
        'decision'       => $_POST['decision']        ?? '',
        'approved_amount'=> $_POST['approved_amount'] ?? '',
        'treasurer_note' => $_POST['treasurer_note']  ?? '',
    ]));
    $resp = json_decode($resp, true);

    set_flash(($resp['status'] ?? '') === 'success' ? 'success' : 'danger',
              $resp['message'] ?? 'Could not record the decision.');
    redirect('fund_request?rid=' . $rid);
}

// ── Withdraw / delete ────────────────────────────────────────────────────────
if (isset($_GET['action']) && in_array($_GET['action'], ['cancel', 'delete'], true)) {
    $resp = json_decode(get_api_data(
        "$api_url/funds/manage_fund_request?action=" . $_GET['action'] . "&rid=$rid&" . actor_qs()), true);
    $ok = ($resp['status'] ?? '') === 'success';

    set_flash($ok ? 'success' : 'danger', $resp['message'] ?? 'Could not complete that.');
    redirect('' . ($ok && $_GET['action'] === 'delete' ? 'fund_requests' : 'fund_request?rid=' . $rid));
}

$resp = json_decode(get_api_data("$api_url/funds/fund_requests?rid=$rid"), true);
$r    = $resp['data'][0] ?? null;

if (!$r) {
    set_flash('danger', 'That fund request no longer exists.');
    redirect('fund_requests');
}

$spends = $resp['spends'] ?? [];
$files  = $resp['files']  ?? [];

$is_raiser  = $r['raised_by'] === portal_uid();
$reviewer   = can_approve_funds();
// Nobody rules on their own request, treasurer included.
$can_decide = $reviewer && $r['status'] === 'pending' && !$is_raiser;
$can_edit   = ($is_raiser || is_admin()) && $r['status'] === 'pending';
$can_cancel = ($is_raiser && $r['status'] === 'pending') || (is_admin() && $r['status'] !== 'settled');

$sanctioned = $r['approved_amount'] !== null ? (float)$r['approved_amount'] : 0;
$drawn      = (float)$r['settled_amount'];
$remaining  = round($sanctioned - $drawn, 2);
$pct        = $sanctioned > 0 ? min(100, round($drawn / $sanctioned * 100)) : 0;
?>

<div class="main-panel">
<div class="content-wrapper">

  <div class="page-header">
    <h3 class="page-title">
      <span class="page-title-icon me-2"><i class="mdi mdi-cash-usd"></i></span>
      <?= h($r['request_no'] ?: 'Request #' . $r['id']) ?>
    </h3>
    <div class="d-flex align-items-center gap-2 flex-wrap">
      <a href="fund_requests" class="btn btn-sm btn-light border"><i class="mdi mdi-arrow-left me-1"></i>Back</a>
      <?php if ($can_edit): ?>
        <a href="manage_fund_request?rid=<?= $rid ?>" class="btn btn-sm btn-gradient-info"><i class="mdi mdi-pencil me-1"></i>Edit</a>
      <?php endif; ?>
      <?php if ($r['status'] === 'approved' && $remaining > 0 && ($is_raiser || is_admin())): ?>
        <a href="manage_spends?fund_request_id=<?= $rid ?>" class="btn btn-sm btn-gradient-success">
          <i class="mdi mdi-receipt me-1"></i>Log Spend
        </a>
      <?php endif; ?>
      <?php if ($can_cancel): ?>
        <a href="fund_request?rid=<?= $rid ?>&action=cancel" class="btn btn-sm btn-outline-secondary"
           onclick="return confirm('Withdraw this fund request?')">Withdraw</a>
      <?php endif; ?>
      <?php if (is_admin() && $drawn == 0): ?>
        <a href="fund_request?rid=<?= $rid ?>&action=delete" class="btn btn-sm btn-gradient-danger"
           onclick="return confirm('Delete this request for good?')"><i class="mdi mdi-delete"></i></a>
      <?php endif; ?>
    </div>
  </div>

  <div class="row">

    <!-- ── The request ──────────────────────────────────────────────────── -->
    <div class="col-lg-7 mb-4">
      <div class="card border-0 mb-4">
        <div class="card-body px-4 py-4">

          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
              <h5 class="mb-1" style="font-weight:700;color:#2c3250;"><?= h($r['title']) ?></h5>
              <div style="font-size:0.78rem;color:var(--stm-text-muted);">
                Raised by <?= h($r['raised_by_name'] ?: '—') ?> on <?= nice_date($r['created_at']) ?>
              </div>
            </div>
            <?= fund_status_badge($r['status']) ?>
          </div>

          <div class="row mb-4">
            <div class="col-sm-4">
              <p class="mini-stat-label mb-1">Amount Requested</p>
              <h4 class="mb-0 fw-bold" style="color:var(--stm-navy);"><?= money($r['amount']) ?></h4>
            </div>
            <div class="col-sm-4">
              <p class="mini-stat-label mb-1">Sanctioned</p>
              <h4 class="mb-0 fw-bold" style="color:<?= $sanctioned > 0 ? 'var(--stm-green-dark)' : 'var(--stm-muted)' ?>;">
                <?= $r['approved_amount'] !== null ? money($r['approved_amount']) : '—' ?>
              </h4>
            </div>
            <div class="col-sm-4">
              <p class="mini-stat-label mb-1"><?= $r['status'] === 'settled' ? 'Drawn' : 'Still Available' ?></p>
              <h4 class="mb-0 fw-bold" style="color:var(--stm-teal);">
                <?= $sanctioned > 0 ? money($r['status'] === 'settled' ? $drawn : $remaining) : '—' ?>
              </h4>
              <?php if ($sanctioned > 0): ?>
                <div class="fund-bar"><span style="width:<?= $pct ?>%;"></span></div>
              <?php endif; ?>
            </div>
          </div>

          <h6 class="fw-bold text-uppercase text-muted mb-2" style="font-size:0.72rem;letter-spacing:0.7px;">Reason Given</h6>
          <p style="font-size:0.9rem;color:#4a5168;white-space:pre-line;"><?= h($r['reason']) ?></p>

          <div class="row mt-3">
            <div class="col-sm-6">
              <dl class="detail-list">
                <dt>Against Ticket</dt>
                <dd>
                  <?php if ($r['ticket_id']): ?>
                    <a href="ticket?rid=<?= (int)$r['ticket_id'] ?>"><?= h($r['ticket_no'] ?: '#' . $r['ticket_id']) ?></a>
                    <div style="font-size:0.78rem;color:var(--stm-text-muted);"><?= h($r['ticket_title']) ?></div>
                  <?php else: ?>
                    <span class="badge-stm-muted">Standalone request</span>
                  <?php endif; ?>
                </dd>

                <dt>Needed By</dt>
                <dd><?= nice_date($r['needed_by']) ?></dd>
              </dl>
            </div>
            <div class="col-sm-6">
              <dl class="detail-list">
                <dt>Pay To</dt>
                <dd><?= $r['payee_name'] ? h($r['payee_name']) : '—' ?></dd>

                <dt>Payment Details</dt>
                <dd style="word-break:break-word;"><?= $r['payee_details'] ? h($r['payee_details']) : '—' ?></dd>
              </dl>
            </div>
          </div>

          <h6 class="fw-bold text-uppercase text-muted mt-4 mb-2" style="font-size:0.72rem;letter-spacing:0.7px;">Proof</h6>
          <?php if (empty($files) && !$r['proof_link']): ?>
            <div class="gate-note">
              <i class="mdi mdi-alert-outline"></i>
              <div>No proof was attached to this request. Ask the coordinator for a quote,
                   bill or estimate before sanctioning it.</div>
            </div>
          <?php else: ?>
            <?php if (!empty($files)): ?>
              <div class="proof-grid mb-2">
                <?php foreach ($files as $f): ?>
                  <a class="proof-item" target="_blank" href="<?= h(proof_url($api_url, $f['id'])) ?>">
                    <?php if ((int)$f['is_image'] === 1): ?>
                      <img src="<?= h(proof_url($api_url, $f['id'])) ?>" alt="<?= h($f['file_name']) ?>">
                    <?php else: ?>
                      <span class="proof-doc"><i class="mdi mdi-file-pdf-box"></i></span>
                    <?php endif; ?>
                    <span class="proof-name"><?= h($f['file_name']) ?></span>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php if ($r['proof_link']): ?>
              <p class="mb-0" style="font-size:0.84rem;">
                <i class="mdi mdi-link-variant me-1"></i>
                <a href="<?= h($r['proof_link']) ?>" target="_blank" rel="noopener noreferrer nofollow" style="word-break:break-all;">
                  <?= h($r['proof_link']) ?>
                </a>
              </p>
            <?php endif; ?>
          <?php endif; ?>

        </div>
      </div>

      <!-- What has been spent against it -->
      <?php if (!empty($spends)): ?>
        <div class="card border-0">
          <div class="card-body px-4 py-4">
            <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
              <i class="mdi mdi-receipt me-2" style="color:var(--stm-navy);"></i>Spent Against This
            </h6>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead><tr><th>Date</th><th>Title</th><th>Amount</th><th>Logged By</th></tr></thead>
                <tbody>
                  <?php foreach ($spends as $s): ?>
                    <tr>
                      <td style="white-space:nowrap;font-size:0.8rem;"><?= nice_date($s['date']) ?></td>
                      <td style="font-weight:600;font-size:0.85rem;">
                        <?= h($s['title']) ?>
                        <?php if ($s['des']): ?>
                          <div style="font-size:0.74rem;color:var(--stm-text-muted);font-weight:400;"><?= h($s['des']) ?></div>
                        <?php endif; ?>
                      </td>
                      <td style="font-weight:700;color:#c0392b;white-space:nowrap;"><?= money($s['amount']) ?></td>
                      <td style="font-size:0.8rem;"><?= h($s['logged_by_name'] ?: '—') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- ── The decision ─────────────────────────────────────────────────── -->
    <div class="col-lg-5 mb-4">
      <div class="card border-0">
        <div class="card-body px-4 py-4">

          <h6 class="mb-3" style="font-weight:700;color:#2c3250;">
            <i class="mdi mdi-gavel me-2" style="color:var(--stm-navy);"></i>Treasurer's Decision
          </h6>

          <?php if ($r['status'] !== 'pending'): ?>
            <!-- Already decided -->
            <div class="mb-3"><?= fund_status_badge($r['status']) ?></div>
            <dl class="detail-list mb-3">
              <dt>Reviewed By</dt>
              <dd><?= h($r['reviewed_by_name'] ?: '—') ?></dd>
              <dt>Reviewed On</dt>
              <dd><?= nice_datetime($r['reviewed_on']) ?></dd>
            </dl>
            <?php if (trim((string)$r['treasurer_note']) !== ''): ?>
              <h6 class="fw-bold text-uppercase text-muted mb-1" style="font-size:0.72rem;letter-spacing:0.7px;">Note</h6>
              <p style="font-size:0.88rem;color:#4a5168;white-space:pre-line;"><?= h($r['treasurer_note']) ?></p>
            <?php endif; ?>

            <?php if (is_admin() && $drawn == 0 && in_array($r['status'], ['approved', 'rejected'], true)): ?>
              <form method="POST" action="fund_request?rid=<?= $rid ?>" class="mt-3"
                    onsubmit="return confirm('Send this back to the treasurer\'s queue?')">
                <input type="hidden" name="_decide" value="1">
                <input type="hidden" name="decision" value="reopen">
                <input type="text" name="treasurer_note" class="form-control form-control-sm mb-2"
                       placeholder="Why it is going back (optional)">
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                  <i class="mdi mdi-undo me-1"></i>Reopen for Review
                </button>
              </form>
            <?php endif; ?>

          <?php elseif ($can_decide): ?>
            <!-- The queue item -->
            <p class="text-muted" style="font-size:0.84rem;">
              <?= h($r['raised_by_name']) ?> is asking for <strong><?= money($r['amount']) ?></strong>.
              Sanction the full amount, or enter a smaller figure.
            </p>

            <form method="POST" action="fund_request?rid=<?= $rid ?>" id="approveForm">
              <input type="hidden" name="_decide" value="1">
              <input type="hidden" name="decision" value="approve" id="decisionField">

              <div class="form-group mb-3">
                <label for="approved_amount" class="form-label fw-bold">Amount to Sanction</label>
                <div class="input-group">
                  <span class="input-group-text">₹</span>
                  <input type="number" step="0.01" min="0" max="<?= h($r['amount']) ?>"
                         name="approved_amount" id="approved_amount" class="form-control"
                         value="<?= h($r['amount']) ?>">
                </div>
                <small class="text-muted" style="font-size:0.74rem;">Cannot exceed the <?= money($r['amount']) ?> requested.</small>
              </div>

              <div class="form-group mb-3">
                <label for="treasurer_note" class="form-label fw-bold">Note</label>
                <textarea name="treasurer_note" id="treasurer_note" class="form-control" rows="3"
                          placeholder="Conditions, instructions, or the reason for rejecting."></textarea>
                <small class="text-muted" style="font-size:0.74rem;">Required when rejecting.</small>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-gradient-success fw-bold" onclick="document.getElementById('decisionField').value='approve';">
                  <i class="mdi mdi-check-decagram me-1"></i>Approve
                </button>
                <button type="submit" class="btn btn-gradient-danger fw-bold" id="rejectBtn">
                  <i class="mdi mdi-close-circle-outline me-1"></i>Reject
                </button>
              </div>
            </form>

          <?php elseif ($is_raiser): ?>
            <div class="gate-note">
              <i class="mdi mdi-clock-outline"></i>
              <div>Waiting on the treasurer. You will see the decision here, and can log a
                   spend against it once it is approved.</div>
            </div>

          <?php else: ?>
            <div class="gate-note">
              <i class="mdi mdi-lock-outline"></i>
              <div>Only the treasurer can sanction funds. This request is still in their queue.</div>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>

</div>

<?php if ($can_decide): ?>
<script>
(function () {
    "use strict";
    // Rejecting takes a reason — stop the submit rather than letting the API
    // bounce it back and lose what was typed.
    var form   = document.getElementById('approveForm');
    var reject = document.getElementById('rejectBtn');
    var note   = document.getElementById('treasurer_note');
    var field  = document.getElementById('decisionField');

    reject.addEventListener('click', function (e) {
        field.value = 'reject';
        if (note.value.trim().length < 5) {
            e.preventDefault();
            note.focus();
            note.classList.add('is-invalid');
            alert('Add a short note explaining why this is being rejected.');
            return;
        }
        if (!confirm('Reject this request for ₹<?= number_format((float)$r['amount'], 2) ?>?')) {
            e.preventDefault();
        }
    });

    form.addEventListener('submit', function (e) {
        if (field.value === 'approve') {
            var amt = parseFloat(document.getElementById('approved_amount').value || '0');
            if (!(amt > 0)) {
                e.preventDefault();
                alert('Enter the amount you are sanctioning.');
            }
        }
    });
})();
</script>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
</div>
