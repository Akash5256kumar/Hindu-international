<?php
/* =====================================================================
   VIRTUAL PUNYA SNAN — form handler (service request + contact)
   Sends the submission by email (photo attached). Nothing is stored on
   the server. Responds with JSON: { ok: bool, message?: string }
   ===================================================================== */

declare(strict_types=1);
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function respond(bool $ok, string $message = '', int $code = 200): void
{
    http_response_code($code);
    echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'message' => $message]);
    exit;
}

function form_utf8_substr(string $value, int $start, int $length): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    $matched = preg_match_all('/./us', $value, $characters);
    return $matched === false
        ? substr($value, $start, $length)
        : implode('', array_slice($characters[0], $start, $length));
}

/* Single-line text: strip control characters (prevents header injection). */
function clean_line(string $key, int $max = 200): string
{
    $v = isset($_POST[$key]) ? (string) $_POST[$key] : '';
    $v = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $v) ?? '';
    return form_utf8_substr(trim($v), 0, $max);
}

/* Multi-line text: keep line breaks, drop other control characters. */
function clean_text(string $key, int $max = 3000): string
{
    $v = isset($_POST[$key]) ? (string) $_POST[$key] : '';
    $v = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/u', '', $v) ?? '';
    return form_utf8_substr(trim($v), 0, $max);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Method not allowed.', 405);
}

// Honeypot: real visitors never fill this hidden field.
if (!empty($_POST['website'])) {
    respond(true);
}

// Simple per-visitor throttle.
session_start();
$now = time();
if (isset($_SESSION['vps_last']) && ($now - (int) $_SESSION['vps_last']) < VPS_THROTTLE_SECONDS) {
    respond(false, 'Please wait a few seconds before sending again.', 429);
}

$type    = clean_line('form_type', 20) === 'request' ? 'request' : 'contact';
$name    = clean_line('name', 120);
$email   = clean_line('email', 160);
$phone   = clean_line('phone', 40);
$event   = clean_line('event', 200);
$message = clean_text('message');
$donationAmount = clean_line('other_amount', 20) ?: clean_line('donation_amount', 20);
$paymentMethod = clean_line('payment_method', 30);
$isDonation = clean_line('donation_interest', 10) === 'yes';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter your name and a valid email address.', 422);
}
if ($type === 'contact' && $message === '') {
    respond(false, 'Please enter your message.', 422);
}

$attachment = null;
if ($type === 'request') {
    $digits = preg_replace('/\D/', '', $phone) ?? '';
    if (strlen($digits) < 7 || strlen($digits) > 15) {
        respond(false, 'Please enter a valid phone / WhatsApp number.', 422);
    }

    $file = $_FILES['photo'] ?? null;
    if (!$file || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        respond(false, 'Please upload your photograph.', 422);
    }
    if ($file['size'] > VPS_MAX_BYTES) {
        respond(false, 'The photograph must be smaller than 5 MB.', 422);
    }
    $image = @getimagesize($file['tmp_name']);
    $mime = is_array($image) ? ([IMAGETYPE_JPEG => 'image/jpeg', IMAGETYPE_PNG => 'image/png'][$image[2]] ?? '') : '';
    if (!isset(VPS_ALLOWED_TYPES[$mime])) {
        respond(false, 'Please upload a JPG or PNG image.', 422);
    }
    $safeName = preg_replace('/[^A-Za-z0-9]+/', '-', $name) ?: 'devotee';
    $attachment = [
        'name' => 'photo-' . strtolower(trim($safeName, '-')) . '.' . VPS_ALLOWED_TYPES[$mime],
        'mime' => $mime,
        'data' => (string) file_get_contents($file['tmp_name']),
    ];
}

/* ---------- Compose email ---------- */
$subject = $type === 'request'
    ? 'New Virtual Punya Snan request — ' . $name
    : ($isDonation ? 'New donation inquiry — ' . $name : 'New contact message — ' . $name);

$lines = [
    $type === 'request' ? 'A new service request was submitted on the website.' : 'A new message was sent from the contact form.',
    '',
    'Name:    ' . $name,
    'Email:   ' . $email,
];
if ($type === 'contact' && $phone !== '') {
    $lines[] = 'Phone:   ' . $phone;
}
if ($isDonation) {
    $lines[] = 'Donation amount: ' . ($donationAmount !== '' ? $donationAmount : '(not selected)');
    $lines[] = 'Preferred payment method: ' . ($paymentMethod !== '' ? $paymentMethod : '(not selected)');
}
if ($type === 'request') {
    $lines[] = 'Phone / WhatsApp: ' . $phone;
    $lines[] = 'Event:   ' . ($event !== '' ? $event : '(not selected)');
}
$lines[] = '';
$lines[] = 'Message:';
$lines[] = $message !== '' ? $message : '(none)';
$lines[] = '';
$lines[] = 'Submitted: ' . gmdate('Y-m-d H:i') . ' UTC';
$body = implode("\r\n", $lines);

$headers = [
    'From: ' . VPS_FROM_NAME . ' <' . VPS_FROM . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
];

if ($attachment) {
    $boundary = 'vps_' . bin2hex(random_bytes(12));
    $headers[] = 'Content-Type: multipart/mixed; boundary="' . $boundary . '"';
    $body = "--$boundary\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $body . "\r\n\r\n"
        . "--$boundary\r\n"
        . 'Content-Type: ' . $attachment['mime'] . '; name="' . $attachment['name'] . "\"\r\n"
        . "Content-Transfer-Encoding: base64\r\n"
        . 'Content-Disposition: attachment; filename="' . $attachment['name'] . "\"\r\n\r\n"
        . chunk_split(base64_encode($attachment['data'])) . "\r\n"
        . "--$boundary--";
} else {
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    $headers[] = 'Content-Transfer-Encoding: 8bit';
}

$encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
$sent = mail(VPS_RECIPIENT, $encodedSubject, $body, implode("\r\n", $headers), '-f' . VPS_FROM);

if (!$sent) {
    respond(false, 'Sorry, we could not send this right now. Please try again, or contact us by phone / WhatsApp.', 500);
}

$_SESSION['vps_last'] = $now;
respond(true);
