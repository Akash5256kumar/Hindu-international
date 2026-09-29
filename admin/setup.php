<?php
/* First-time setup: create the admin login. Needs the setup key from admin/config.php.
   Disabled automatically once a login exists. */
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
require __DIR__ . '/includes/layout.php';

if (Auth::isConfigured()) {
    redirect('login.php');
}

$error = null;
$username = 'admin';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $error = Auth::setup(
            trim((string) ($_POST['setup_key'] ?? '')),
            $username,
            (string) ($_POST['password'] ?? ''),
            (string) ($_POST['confirm'] ?? '')
        );
        if ($error === null) {
            redirect('login.php?setup=1');
        }
    }
}

auth_page_start('Create admin login');
?>
    <h1>Create admin login</h1>
    <p class="muted">This is done once. You need the <strong>setup key</strong> from <code>admin/config.php</code>.</p>
    <?php auth_error($error); ?>
    <form method="post" class="stack" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <label class="field">
        <span class="field__label">Setup key</span>
        <input class="input" type="password" name="setup_key" autocomplete="off" required autofocus>
      </label>
      <label class="field">
        <span class="field__label">Username</span>
        <input class="input" type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required>
      </label>
      <label class="field">
        <span class="field__label">Password <small>(at least 10 characters)</small></span>
        <input class="input" type="password" name="password" autocomplete="new-password" minlength="10" required>
      </label>
      <label class="field">
        <span class="field__label">Confirm password</span>
        <input class="input" type="password" name="confirm" autocomplete="new-password" minlength="10" required>
      </label>
      <button class="btn btn--primary btn--block" type="submit">Create login</button>
    </form>
<?php
auth_page_end();
