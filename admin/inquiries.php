<?php
/* =====================================================================
   ADMIN → INQUIRIES: every booking request, contact message and donation
   form sent from the website (newest first). Photo of a booking request:
   inquiries.php?photo=<id>. Actions are POST forms with the CSRF token.
   ===================================================================== */

declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/inquiries.php';

Auth::requirePage();

/* ---------- Photo of a booking request (logged-in admins only) ---------- */
if (isset($_GET['photo'])) {
    $path = Inquiries::photoPath((string) $_GET['photo']);
    if (!$path) {
        http_response_code(404);
        exit('Photo not found.');
    }
    header('Content-Type: ' . (str_ends_with($path, '.png') ? 'image/png' : 'image/jpeg'));
    header('Content-Length: ' . filesize($path));
    header('Content-Disposition: inline; filename="' . basename($path) . '"');
    readfile($path);
    exit;
}

/* ---------- Actions ---------- */
$filter = (string) ($_GET['show'] ?? 'all');
$filters = ['all' => 'All', 'new' => 'New'] + Inquiries::TYPES;
if (!isset($filters[$filter])) {
    $filter = 'all';
}
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        http_response_code(403);
        exit('Security check failed. Please go back and reload the page.');
    }
    $id = (string) ($_POST['id'] ?? '');
    switch ((string) ($_POST['action'] ?? '')) {
        case 'read':
            Inquiries::update($id, ['read' => true]);
            break;
        case 'unread':
            Inquiries::update($id, ['read' => false]);
            break;
        case 'delete':
            Inquiries::delete($id);
            break;
        case 'read-all':
            foreach (Inquiries::all() as $r) {
                if (empty($r['read'])) {
                    Inquiries::update((string) $r['id'], ['read' => true]);
                }
            }
            break;
    }
    redirect('inquiries.php' . ($filter !== 'all' ? '?show=' . rawurlencode($filter) : '') . ($id !== '' && ($_POST['action'] ?? '') !== 'delete' ? '#i-' . $id : ''));
}

$all = Inquiries::all();
$unread = count(array_filter($all, static fn ($r): bool => empty($r['read'])));
$counts = ['all' => count($all), 'new' => $unread];
foreach (Inquiries::TYPES as $t => $label) {
    $counts[$t] = count(array_filter($all, static fn ($r): bool => ($r['type'] ?? '') === $t));
}
$list = array_values(array_filter($all, static function ($r) use ($filter): bool {
    if ($filter === 'all') return true;
    if ($filter === 'new') return empty($r['read']);
    return ($r['type'] ?? '') === $filter;
}));

/** Date in Indian time, e.g. 17 Nov 2026, 4:05 pm */
function inquiry_time(string $iso): string
{
    try {
        $d = new DateTime($iso);
        $d->setTimezone(new DateTimeZone('Asia/Kolkata'));
        return $d->format('j M Y, g:i a') . ' IST';
    } catch (Throwable $e) {
        return $iso;
    }
}
$csrf = csrf_token();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Inquiries · Hindu.International Admin</title>
  <link rel="icon" href="../assets/images/brand/logo-mark.png" type="image/png">
  <link rel="stylesheet" href="assets/admin.css?v=inquiries-20261117">
</head>
<body class="app">
  <header class="topbar">
    <a class="topbar__brand" href="index.php">
      <img src="../assets/images/brand/logo.png" alt="Hindu.International" width="220" height="61">
      <span>Admin</span>
    </a>
    <div class="topbar__actions">
      <a class="btn btn--ghost" href="index.php">← Content editor</a>
      <a class="btn btn--ghost" href="../index.html" target="_blank" rel="noopener">View website ↗</a>
    </div>
  </header>

  <main class="inq">
    <div class="inq__head">
      <div>
        <h1>Inquiries</h1>
        <p class="inq__sub">Every booking request, contact message and donation form sent from the website. A copy is also emailed to you.</p>
      </div>
      <?php if ($unread): ?>
      <form method="post" action="inquiries.php<?= $filter !== 'all' ? '?show=' . e($filter) : '' ?>">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <button class="btn btn--ghost btn--sm" name="action" value="read-all">Mark all as read</button>
      </form>
      <?php endif; ?>
    </div>

    <nav class="inq__tabs" aria-label="Filter inquiries">
      <?php foreach ($filters as $key => $label): ?>
        <a class="inq__tab<?= $key === $filter ? ' is-active' : '' ?>" href="inquiries.php<?= $key !== 'all' ? '?show=' . e($key) : '' ?>"><?= e($label) ?> <span><?= (int) $counts[$key] ?></span></a>
      <?php endforeach; ?>
    </nav>

    <?php if (!$list): ?>
      <div class="inq__empty">
        <?= $all ? 'Nothing here for this filter.' : 'No inquiries yet. New forms sent from the website will appear here.' ?>
      </div>
    <?php endif; ?>

    <?php foreach ($list as $r):
        $id = (string) $r['id'];
        $type = (string) ($r['type'] ?? 'contact');
        $phoneDigits = preg_replace('/\D/', '', (string) ($r['phone'] ?? '')) ?? '';
    ?>
    <article class="inq-card<?= empty($r['read']) ? ' is-new' : '' ?>" id="i-<?= e($id) ?>">
      <header class="inq-card__top">
        <div class="inq-card__who">
          <?php if (empty($r['read'])): ?><span class="inq-badge inq-badge--new">New</span><?php endif; ?>
          <span class="inq-badge inq-badge--<?= e($type) ?>"><?= e(Inquiries::TYPES[$type] ?? $type) ?></span>
          <strong><?= e((string) ($r['name'] ?? '')) ?></strong>
        </div>
        <time datetime="<?= e((string) ($r['received'] ?? '')) ?>"><?= e(inquiry_time((string) ($r['received'] ?? ''))) ?></time>
      </header>

      <dl class="inq-card__facts">
        <div><dt>Email</dt><dd><a href="mailto:<?= e((string) $r['email']) ?>"><?= e((string) $r['email']) ?></a></dd></div>
        <?php if (!empty($r['phone'])): ?>
        <div><dt>Phone</dt><dd><a href="tel:<?= e($phoneDigits) ?>"><?= e((string) $r['phone']) ?></a>
          <?php if (strlen($phoneDigits) >= 10): ?> · <a href="https://wa.me/<?= e($phoneDigits) ?>" target="_blank" rel="noopener">WhatsApp</a><?php endif; ?></dd></div>
        <?php endif; ?>
        <?php if (!empty($r['event'])): ?><div><dt>Service / event</dt><dd><?= e((string) $r['event']) ?></dd></div><?php endif; ?>
        <?php if (!empty($r['amount'])): ?><div><dt>Amount</dt><dd><?= e((string) $r['amount']) ?><?= !empty($r['method']) ? ' · ' . e((string) $r['method']) : '' ?></dd></div><?php endif; ?>
        <?php if (!empty($r['page'])): ?><div><dt>Sent from</dt><dd><?= e((string) $r['page']) ?></dd></div><?php endif; ?>
        <div><dt>Email to you</dt><dd><?= $r['mailed'] === true ? '<span class="inq-ok">Sent</span>' : ($r['mailed'] === false ? '<span class="inq-fail">Failed — reply from here</span>' : '—') ?></dd></div>
      </dl>

      <?php if (!empty($r['message'])): ?>
        <p class="inq-card__msg"><?= nl2br(e((string) $r['message'])) ?></p>
      <?php endif; ?>

      <?php if (!empty($r['photo'])): ?>
        <a class="inq-card__photo" href="inquiries.php?photo=<?= e($id) ?>" target="_blank" rel="noopener">
          <img src="inquiries.php?photo=<?= e($id) ?>" alt="Photo sent by <?= e((string) $r['name']) ?>" loading="lazy">
        </a>
      <?php endif; ?>

      <footer class="inq-card__actions">
        <a class="btn btn--primary btn--sm" href="mailto:<?= e((string) $r['email']) ?>?subject=<?= rawurlencode('Re: your inquiry on Hindu.International') ?>">Reply by email</a>
        <form method="post" action="inquiries.php<?= $filter !== 'all' ? '?show=' . e($filter) : '' ?>">
          <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
          <input type="hidden" name="id" value="<?= e($id) ?>">
          <?php if (empty($r['read'])): ?>
            <button class="btn btn--ghost btn--sm" name="action" value="read">Mark as read</button>
          <?php else: ?>
            <button class="btn btn--ghost btn--sm" name="action" value="unread">Mark as new</button>
          <?php endif; ?>
        </form>
        <details class="inq-card__delete">
          <summary class="btn btn--link">Delete</summary>
          <form method="post" action="inquiries.php<?= $filter !== 'all' ? '?show=' . e($filter) : '' ?>">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="id" value="<?= e($id) ?>">
            <span>Delete this inquiry for good?</span>
            <button class="btn btn--sm inq-danger" name="action" value="delete">Yes, delete</button>
          </form>
        </details>
      </footer>
    </article>
    <?php endforeach; ?>
  </main>
</body>
</html>
