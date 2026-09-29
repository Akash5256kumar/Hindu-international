<?php
/* =====================================================================
   Shared page shell for the login / setup screens.
   ===================================================================== */

declare(strict_types=1);

function auth_page_start(string $title): void
{
    ?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · Hindu.International Admin</title>
  <link rel="icon" href="../assets/icons/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body class="auth">
  <main class="auth__card">
    <div class="auth__brand">
      <img src="../assets/icons/favicon.svg" alt="" width="40" height="40">
      <div><strong>Hindu.International</strong><span>Admin panel</span></div>
    </div>
<?php
}

function auth_page_end(): void
{
    ?>
  </main>
</body>
</html>
<?php
}

function auth_error(?string $message): void
{
    if ($message) {
        echo '<p class="alert alert--error" role="alert">' . e($message) . "</p>\n";
    }
}
