<?php
include 'includes/header.php';

$current_year  = date('Y');
$current_month = date('m');

// Fetch live statistics — donations (all time totals)
$donations_resp   = json_decode(get_api_data("$api_url/logs/donations?month=all&page=1"), true);
// Expenditures — current year only to be accurate
$spends_this_year = json_decode(get_api_data("$api_url/logs/spends?month=all&year=$current_year&page=1"), true);
$users_resp       = json_decode(get_api_data("$api_url/global/users?page=1"), true);

$total_donations = $donations_resp['total_amount']   ?? 0;
$donation_count  = $donations_resp['total_records']  ?? ($donations_resp['total_pages'] ?? 0) * 10;
$total_spends    = $spends_this_year['total_amount'] ?? 0;
$total_users     = ($users_resp['total_pages'] ?? 0) * 10;

// Recent donations
$recent_donations = $donations_resp['data'] ?? [];

// ── The new sections, so the dashboard shows what is actually outstanding ────
$ticket_board  = json_decode(get_api_data("$api_url/tickets/tickets?board=1"), true);
$ticket_counts = $ticket_board['counts'] ?? [];
$open_tickets  = ($ticket_counts['open'] ?? 0) + ($ticket_counts['in_review'] ?? 0)
               + ($ticket_counts['in_progress'] ?? 0) + ($ticket_counts['on_hold'] ?? 0)
               + ($ticket_counts['approved'] ?? 0);

$blood_stats = json_decode(get_api_data("$api_url/blood/donors?stats=1"), true);
$blood_head  = $blood_stats['headline'] ?? [];
$blood_reqs  = $blood_stats['requests'] ?? [];
$open_blood  = ($blood_reqs['open'] ?? 0) + ($blood_reqs['partially_arranged'] ?? 0);

// Whatever is on this person's plate: their live tickets, newest first.
$my_tickets = json_decode(get_api_data("$api_url/tickets/tickets?status=live&mine=1&limit=5&" . actor_qs()), true);
$my_tickets = is_array($my_tickets['data'] ?? null) ? $my_tickets['data'] : [];

// $pending_approvals comes from includes/header.php, for treasurers and admins.

/*
 * Has the database migration been run?
 *
 * The portal code can be deployed before the SQL is applied, in which case the
 * new sections sit empty. Rather than leave an admin wondering, say so once,
 * here, with what is missing.
 */
$schema_missing = [];
if (is_admin()) {
    $probe = json_decode(get_api_data("$api_url/tickets/categories"), true);
    if (($probe['reason'] ?? '') === 'schema_missing' || ($probe['status'] ?? '') !== 'success') {
        $probe2 = json_decode(get_api_data("$api_url/blood/donors?stats=1"), true);
        if (($probe2['reason'] ?? '') === 'schema_missing') {
            $schema_missing = ['tickets', 'blood bank', 'fund approvals'];
        }
    }
}
?>

<div class="main-panel">
    <div class="content-wrapper">

        <!-- Hero Section -->
        <div class="welcome-hero mb-4">
            <div style="position:relative; z-index:2;">
                <p class="mb-1" style="font-size:0.85rem; font-weight:600; opacity:0.7; text-transform:uppercase; letter-spacing:1px;">
                    <?= date('l, d F Y') ?>
                </p>
                <h2 style="font-size:1.8rem; font-weight:800; margin-bottom:0.25rem;">
                    <?= getGreeting() ?>, <?= h($user_name) ?>!
                </h2>
                <p style="opacity:0.7; font-size:0.9rem; margin:0;">
                    Signed in as <?= h(role_label()) ?>.
                    <?php if (can_approve_funds() && $pending_approvals > 0): ?>
                        <strong><?= $pending_approvals ?> fund request<?= $pending_approvals == 1 ? '' : 's' ?></strong>
                        waiting on your approval.
                    <?php elseif ($open_tickets > 0): ?>
                        <?= $open_tickets ?> ticket<?= $open_tickets == 1 ? '' : 's' ?> still open across the team.
                    <?php else: ?>
                        Here's a summary of your organization's activities.
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-card navy p-4 h-100">
                    <i class="mdi mdi-heart-outline stat-icon"></i>
                    <div class="stat-label">Total Donations Received</div>
                    <div class="stat-value">₹<?= number_format($total_donations) ?></div>
                    <div class="stat-note">Across all recorded donations</div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-card teal p-4 h-100">
                    <i class="mdi mdi-cash-register stat-icon"></i>
                    <div class="stat-label">Expenditures (<?= $current_year ?>)</div>
                    <div class="stat-value">₹<?= number_format($total_spends) ?></div>
                    <div class="stat-note">Logged activities this year</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card green p-4 h-100">
                    <i class="mdi mdi-account-group stat-icon"></i>
                    <div class="stat-label">Registered Members</div>
                    <div class="stat-value"><?= $total_users ?>+</div>
                    <div class="stat-note">Volunteers, donors & coordinators</div>
                </div>
            </div>
        </div>

        <?php if (!empty($schema_missing)): ?>
        <!-- Shown to admins only, until the migration has been run. -->
        <div class="gate-note mb-4" style="background:#fff8e6;border-color:#f3e0b0;">
            <i class="mdi mdi-database-alert"></i>
            <div>
                <strong>Finish the setup.</strong>
                Tickets, the blood bank and fund approvals are deployed but their database
                tables do not exist yet, so those sections will sit empty. Run
                <code>db/migrations/2026-10-23_portal_access_tickets_funds_blood.sql</code>
                against the production database — the steps are in <code>db/README.md</code>
                in the API repository. Everything else on this portal works as normal in the meantime.
            </div>
        </div>
        <?php endif; ?>

        <!-- Operations Row -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <a href="tickets" class="mini-stat text-decoration-none">
                    <div class="mini-stat-icon" style="background:var(--stm-navy-muted);color:var(--stm-navy);">
                        <i class="mdi mdi-ticket-confirmation"></i>
                    </div>
                    <div>
                        <p class="mini-stat-label">Open Tickets</p>
                        <p class="mini-stat-value"><?= number_format($open_tickets) ?></p>
                        <p class="mini-stat-note"><?= number_format($ticket_counts['resolved'] ?? 0) ?> resolved to date</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <a href="fund_requests" class="mini-stat text-decoration-none">
                    <div class="mini-stat-icon" style="background:#fef3c7;color:#92400e;">
                        <i class="mdi mdi-cash-usd"></i>
                    </div>
                    <div>
                        <p class="mini-stat-label"><?= can_approve_funds() ? 'Awaiting Your Approval' : 'Fund Requests Pending' ?></p>
                        <p class="mini-stat-value"><?= number_format($pending_approvals) ?></p>
                        <p class="mini-stat-note"><?= can_approve_funds() ? 'Sitting in your queue' : 'With the treasurer' ?></p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="blood" class="mini-stat text-decoration-none">
                    <div class="mini-stat-icon" style="background:#fdf3f2;color:var(--stm-blood);">
                        <i class="mdi mdi-water"></i>
                    </div>
                    <div>
                        <p class="mini-stat-label">Blood Donors Ready</p>
                        <p class="mini-stat-value"><?= number_format((int)($blood_head['ready'] ?? 0)) ?></p>
                        <p class="mini-stat-note"><?= $open_blood ?> requirement<?= $open_blood == 1 ? '' : 's' ?> open</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- What is on this person's desk -->
        <?php if (!empty($my_tickets)): ?>
        <div class="card border-0 mb-4">
            <div class="card-body px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-account-check-outline me-2" style="color:var(--stm-navy);"></i>
                        On Your Desk
                    </h5>
                    <a href="tickets?mine=1" class="btn btn-sm btn-gradient-primary">
                        <i class="mdi mdi-arrow-right me-1"></i>All Mine
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr><th>Ticket</th><th>Activity</th><th>Priority</th><th>Status</th><th></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($my_tickets as $mt): ?>
                            <tr>
                                <td style="max-width:280px;">
                                    <a href="ticket?rid=<?= (int)$mt['id'] ?>" style="font-weight:600;color:#2c3250;text-decoration:none;">
                                        <?= h($mt['title']) ?>
                                    </a>
                                    <div style="font-size:0.72rem;color:var(--stm-text-muted);">
                                        <?= h($mt['ticket_no']) ?> · <?= time_ago($mt['created_at']) ?>
                                    </div>
                                </td>
                                <td style="font-size:0.82rem;"><?= h($mt['category_name']) ?></td>
                                <td><?= priority_badge($mt['priority']) ?></td>
                                <td><?= ticket_status_badge($mt['status']) ?></td>
                                <td style="text-align:right;">
                                    <a href="ticket?rid=<?= (int)$mt['id'] ?>" class="btn btn-gradient-info btn-xs">
                                        <i class="mdi mdi-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Donations Table -->
        <div class="card border-0">
            <div class="card-body px-4 py-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-history me-2" style="color:var(--stm-navy);"></i>
                        Recent Donations
                    </h5>
                    <a href="donations" class="btn btn-sm btn-gradient-primary">
                        <i class="mdi mdi-arrow-right me-1"></i>View All
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Donor</th>
                                <th>Amount</th>
                                <th>Transaction ID</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_donations)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="mdi mdi-inbox-arrow-down" style="font-size:2rem; display:block; margin-bottom:8px; opacity:0.3;"></i>
                                        No donation records found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($recent_donations, 0, 8) as $don): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div style="width:32px; height:32px; border-radius:50%; background:var(--stm-navy-muted); display:flex; align-items:center; justify-content:center; margin-right:10px;">
                                                <i class="mdi mdi-account" style="color:var(--stm-navy); font-size:1rem;"></i>
                                            </div>
                                            <span class="fw-500"><?= h($don['name']) ?></span>
                                        </div>
                                    </td>
                                    <td style="color:#2a7d4f; font-weight:700;">₹<?= number_format($don['amount']) ?></td>
                                    <td style="font-size:0.8rem; color:var(--stm-text-muted);"><?= h($don['txid'] ?: '—') ?></td>
                                    <td style="font-size:0.82rem;"><?= date('d M, Y · h:i A', strtotime($don['added_on'])) ?></td>
                                    <td><span class="badge-gradient-success">Received</span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- .content-wrapper -->

    <?php include 'includes/footer.php'; ?>
</div><!-- .main-panel -->