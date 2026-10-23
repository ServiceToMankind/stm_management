<?php
/*
 * Portal access helpers.
 *
 * Who may be in here at all is settled at login (coordinator role, active
 * account). This file answers the next question — what they are allowed to do
 * once inside — and holds the small label helpers the new screens share.
 *
 * Included by includes/header.php, so every page already has it.
 */

// ── Who is signed in ────────────────────────────────────────────────────────

function portal_role()
{
    return $_SESSION['PORTAL_ROLE'] ?? 'coordinator';
}

function portal_uid()
{
    return $_SESSION['ID'] ?? '';
}

function is_admin()
{
    return portal_role() === 'admin';
}

function is_treasurer()
{
    return portal_role() === 'treasurer';
}

// The treasurer signs money off; an admin can step in when they are away.
function can_approve_funds()
{
    return in_array(portal_role(), ['treasurer', 'admin'], true);
}

function role_label($role = null)
{
    $role = $role ?: portal_role();
    $labels = [
        'admin'       => 'Administrator',
        'treasurer'   => 'Treasurer',
        'coordinator' => 'Coordinator',
    ];
    return $labels[$role] ?? ucfirst($role);
}

/*
 * Blocks a page for anyone without the right standing. Called at the very top,
 * before any output, so the redirect still works.
 */
function require_portal_role(array $roles)
{
    if (in_array(portal_role(), $roles, true)) return;

    $_SESSION['FLASH'] = [
        'type' => 'danger',
        'msg'  => 'That section is limited to ' . implode(' and ', array_map('role_label', $roles)) . ' accounts.',
    ];
    redirect('index');
}

// ── Talking to the API ──────────────────────────────────────────────────────

// Every write endpoint wants to know who is asking, and checks the role it
// finds in the database rather than trusting what we send. The shared key is
// attached by the transport helpers in includes/functions.php, so it does not
// need repeating here.
function actor_qs()
{
    return 'actor=' . urlencode(portal_uid());
}

function with_actor(array $data)
{
    $data['actor'] = portal_uid();
    return $data;
}

/*
 * URL for a stored proof.
 *
 * The browser fetches these itself, so it never sees the shared key — each
 * file gets a token derived from it instead. Must match portal_file_token()
 * in the API's lib/portal.php.
 */
function proof_url($api_url, $id, $download = false)
{
    $url = rtrim($api_url, '/') . '/files/portal_file?id=' . (int)$id;
    $key = portal_key();
    if ($key !== null) {
        $url .= '&t=' . substr(hash_hmac('sha256', 'portal-file:' . (int)$id, $key), 0, 20);
    }
    if ($download) $url .= '&download=1';
    return $url;
}

// ── Redirecting ─────────────────────────────────────────────────────────────

/*
 * Send the browser somewhere else.
 *
 * Pages handle their POSTs after includes/header.php has already printed the
 * navbar, so the half-built page is thrown away first — without that, PHP
 * refuses the Location header and the visitor is left on a truncated page.
 */
function redirect($to)
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Location: ' . $to);
    exit;
}

// ── One-shot messages across a redirect ─────────────────────────────────────

function set_flash($type, $msg)
{
    $_SESSION['FLASH'] = ['type' => $type, 'msg' => $msg];
}

function take_flash()
{
    if (empty($_SESSION['FLASH'])) return null;
    $flash = $_SESSION['FLASH'];
    unset($_SESSION['FLASH']);
    return $flash;
}

// ── Escaping ────────────────────────────────────────────────────────────────

function h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ── Labels and badges ───────────────────────────────────────────────────────

$TICKET_STATUS = [
    'open'        => ['Open',        'info'],
    'in_review'   => ['In Review',   'warning'],
    'approved'    => ['Approved',    'success'],
    'in_progress' => ['In Progress', 'info'],
    'on_hold'     => ['On Hold',     'warning'],
    'resolved'    => ['Resolved',    'success'],
    'closed'      => ['Closed',      'muted'],
    'rejected'    => ['Rejected',    'danger'],
];

$FUND_STATUS = [
    'pending'   => ['Awaiting Treasurer', 'warning'],
    'approved'  => ['Approved',           'success'],
    'rejected'  => ['Rejected',           'danger'],
    'settled'   => ['Settled',            'info'],
    'cancelled' => ['Withdrawn',          'muted'],
];

$BLOOD_STATUS = [
    'open'               => ['Open',       'danger'],
    'partially_arranged' => ['Part Filled','warning'],
    'fulfilled'          => ['Fulfilled',  'success'],
    'closed'             => ['Closed',     'muted'],
    'cancelled'          => ['Cancelled',  'muted'],
];

$PRIORITY_TONE = [
    'critical' => 'danger',
    'high'     => 'warning',
    'normal'   => 'info',
    'low'      => 'muted',
];

function status_badge($status, array $map)
{
    [$label, $tone] = $map[$status] ?? [ucwords(str_replace('_', ' ', (string)$status)), 'muted'];
    $class = $tone === 'muted' ? 'badge-stm-muted' : 'badge-gradient-' . $tone;
    return '<span class="' . $class . '">' . h($label) . '</span>';
}

function ticket_status_badge($status)
{
    global $TICKET_STATUS;
    return status_badge($status, $TICKET_STATUS);
}

function fund_status_badge($status)
{
    global $FUND_STATUS;
    return status_badge($status, $FUND_STATUS);
}

function blood_status_badge($status)
{
    global $BLOOD_STATUS;
    return status_badge($status, $BLOOD_STATUS);
}

function priority_badge($priority)
{
    global $PRIORITY_TONE;
    $tone  = $PRIORITY_TONE[$priority] ?? 'muted';
    $class = $tone === 'muted' ? 'badge-stm-muted' : 'badge-gradient-' . $tone;
    return '<span class="' . $class . '">' . h(ucfirst((string)$priority)) . '</span>';
}

// ── Small formatters ────────────────────────────────────────────────────────

function money($amount)
{
    return '₹' . number_format((float)$amount, ((float)$amount == floor((float)$amount)) ? 0 : 2);
}

function units_text($units)
{
    $u = (float)$units;
    return rtrim(rtrim(number_format($u, 1), '0'), '.');
}

function nice_date($date, $fallback = '—')
{
    if (!$date || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') return $fallback;
    $ts = strtotime($date);
    return $ts ? date('d M Y', $ts) : $fallback;
}

function nice_datetime($date, $fallback = '—')
{
    if (!$date || strpos((string)$date, '0000-00-00') === 0) return $fallback;
    $ts = strtotime($date);
    return $ts ? date('d M Y · h:i A', $ts) : $fallback;
}

// "3 days ago" reads faster than a date on a busy timeline.
function time_ago($date)
{
    $ts = strtotime((string)$date);
    if (!$ts) return '';

    $diff = time() - $ts;
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('d M Y', $ts);
}
