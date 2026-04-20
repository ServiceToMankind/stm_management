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
                    <?= getGreeting() ?>, <?= htmlspecialchars($user_name) ?>!
                </h2>
                <p style="opacity:0.7; font-size:0.9rem; margin:0;">
                    Welcome back to the STM Management Portal. Here's a summary of your organization's activities.
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
                                            <span class="fw-500"><?= htmlspecialchars($don['name']) ?></span>
                                        </div>
                                    </td>
                                    <td style="color:#2a7d4f; font-weight:700;">₹<?= number_format($don['amount']) ?></td>
                                    <td style="font-size:0.8rem; color:var(--stm-text-muted);"><?= htmlspecialchars($don['txid'] ?: '—') ?></td>
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