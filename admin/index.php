<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

Auth::requirePage();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
  <title>Content editor · Hindu.International Admin</title>
  <link rel="icon" href="../assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="app">
  <header class="topbar">
    <a class="topbar__brand" href="index.php">
      <img src="../assets/icons/favicon.svg" alt="" width="30" height="30">
      <span><strong>Hindu.International</strong> Admin</span>
    </a>
    <div class="topbar__actions">
      <span class="save-status" id="save-status" role="status" aria-live="polite"></span>
      <a class="btn btn--ghost" href="../index.html" target="_blank" rel="noopener">View website ↗</a>
      <button class="btn btn--primary" id="save-btn" type="button" disabled>Save changes</button>
      <details class="menu">
        <summary class="btn btn--ghost" aria-label="Account menu"><?= e(Auth::username()) ?> ▾</summary>
        <div class="menu__panel">
          <button class="menu__item" type="button" id="password-open">Change password</button>
          <form method="post" action="logout.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button class="menu__item" type="submit">Log out</button>
          </form>
        </div>
      </details>
    </div>
  </header>

  <div class="layout">
    <nav class="sidebar" id="sidebar" aria-label="Sections"></nav>
    <main class="editor" id="editor" tabindex="-1">
      <p class="editor__loading">Loading content…</p>
    </main>
  </div>

  <dialog class="dialog" id="password-dialog" aria-labelledby="password-title">
    <form class="stack" id="password-form" novalidate>
      <h2 id="password-title">Change password</h2>
      <label class="field"><span class="field__label">Current password</span>
        <input class="input" type="password" name="current" autocomplete="current-password" required></label>
      <label class="field"><span class="field__label">New password <small>(at least 10 characters)</small></span>
        <input class="input" type="password" name="new" autocomplete="new-password" minlength="10" required></label>
      <label class="field"><span class="field__label">Confirm new password</span>
        <input class="input" type="password" name="confirm" autocomplete="new-password" minlength="10" required></label>
      <p class="form-message" role="alert"></p>
      <div class="dialog__actions">
        <button class="btn btn--ghost" type="button" value="cancel" data-close>Cancel</button>
        <button class="btn btn--primary" type="submit">Change password</button>
      </div>
    </form>
  </dialog>

  <div class="toast" id="toast" role="status" aria-live="polite" hidden></div>

  <script src="assets/admin.js"></script>
</body>
</html>
