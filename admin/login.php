<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (!Auth::isConfigured()) {
    redirect('setup.php');
}
if (Auth::check()) {
    redirect('index.php');
}

$error = null;
$username = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $error = Auth::attempt($username, (string) ($_POST['password'] ?? ''));
        if ($error === null) {
            redirect('index.php');
        }
    }
}

auth_page_start('Log in');
?>
    <h1>Log in</h1>
    <?php auth_error($error); ?>
    <?php if (isset($_GET['setup'])): ?>
      <p class="alert alert--success">Your admin login was created. Please log in.</p>
    <?php endif; ?>
    <form method="post" class="stack" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label class="field">
        <span class="field__label">Username</span>
        <input class="input" type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>
      </label>
      <label class="field">
        <span class="field__label">Password</span>
        <input class="input" type="password" name="password" autocomplete="current-password" required>
      </label>
      <button class="btn btn--primary btn--block" type="submit">Log in</button>
    </form>
    <p class="auth__foot"><a href="../index.html">← Back to website</a></p>
<?php
auth_page_end();
