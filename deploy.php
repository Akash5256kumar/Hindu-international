<?php
// GitHub webhook auto-deploy
// Set DEPLOY_SECRET in /etc/hindu.international/deploy.php as: define('DEPLOY_SECRET', 'your-secret');
$cfgPath = '/etc/hindu.international/deploy.php';
if (is_file($cfgPath)) require $cfgPath;

$secret   = defined('DEPLOY_SECRET') ? DEPLOY_SECRET : '';
$payload  = file_get_contents('php://input');
$sig      = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';

if ($secret !== '' && !hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $sig)) {
    http_response_code(403);
    exit('Forbidden');
}

$data   = json_decode($payload, true);
$branch = $data['ref'] ?? '';
if ($branch !== 'refs/heads/main') {
    http_response_code(200);
    exit('Not main branch');
}

$output = shell_exec('cd /var/www/html/hindu.international/public && git pull origin main 2>&1');
file_put_contents('/var/log/deploy-hindu.log', date('c') . "\n" . $output . "\n---\n", FILE_APPEND);
http_response_code(200);
echo 'Deployed';
