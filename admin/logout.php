<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

// POST only, with the CSRF token, so other sites cannot log the admin out
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && csrf_valid($_POST['csrf'] ?? null)) {
    Auth::logout();
}
redirect('login.php');
