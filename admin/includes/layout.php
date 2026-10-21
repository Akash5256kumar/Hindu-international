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
  <link rel="icon" href="../assets/images/brand/logo-mark.png?v=brand-favicon-20261008" type="image/png">
  <link rel="stylesheet" href="assets/admin.css?v=list-order-20261021">
</head>
<body class="auth">
  <main class="auth__card">
    <div class="auth__brand">
      <img src="../assets/images/brand/logo.png" alt="Hindu.International" width="340" height="94">
      <div><span>Admin panel</span></div>
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
