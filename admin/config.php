<?php
/* =====================================================================
   ADMIN PANEL SETTINGS
   ===================================================================== */

return [
    // Needed ONCE, on /admin/setup.php, to create the admin login.
    // Keep it private. Setup is disabled automatically after the first login is created.
    'setup_key' => 'Rupesh@123',

    // Log out after this many seconds without activity.
    'session_timeout' => 2 * 60 * 60,

    // Block logins from an IP after this many wrong passwords within the window.
    'login_max_attempts' => 5,
    'login_window'       => 15 * 60,

    // Upload limits (also limited by PHP's upload_max_filesize / post_max_size).
    'max_image_mb' => 8,
    'max_video_mb' => 60,

    // How many previous versions of the content to keep in admin/data/backups.
    'backups_to_keep' => 20,

    // Redirect the admin panel to HTTPS (ignored on localhost).
    'force_https' => true,
];
