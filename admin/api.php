<?php
/* =====================================================================
   ADMIN API (JSON). All actions need a logged-in session; every POST
   also needs the CSRF token in the X-CSRF-Token header.
     GET  api.php?action=load       schema + content
     POST api.php?action=save       body: {"content": {...}}
     POST api.php?action=upload     multipart: file, kind=image|video
     POST api.php?action=password   body: {"current","new","confirm"}
   ===================================================================== */

declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

Auth::requireApi();

$action = (string) ($_GET['action'] ?? '');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'POST' && !csrf_valid($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    json_response(['ok' => false, 'message' => 'Security check failed. Please reload the page.'], 403);
}

/** Decode a JSON request body */
function json_body(): array
{
    $data = json_decode((string) file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

try {
    switch ("$method $action") {
        case 'GET load':
            json_response(['ok' => true, 'schema' => ContentStore::schema(), 'content' => ContentStore::load()]);

        case 'POST save':
            $body = json_body();
            $result = ContentStore::save(is_array($body['content'] ?? null) ? $body['content'] : []);
            if (!$result['ok']) {
                json_response(['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $result['errors']], 422);
            }
            json_response(['ok' => true, 'content' => $result['content'], 'savedAt' => date('c')]);

        case 'POST upload':
            $kind = ($_POST['kind'] ?? '') === 'video' ? 'video' : 'image';
            if (empty($_FILES['file'])) {
                // PHP drops the whole body when it exceeds post_max_size
                json_response(['ok' => false, 'message' => 'File is too large for the server (limit ' . ini_get('post_max_size') . ').'], 413);
            }
            $stored = Uploads::store($_FILES['file'], $kind);
            json_response(['ok' => true, 'path' => $stored['path']]);

        case 'POST password':
            $body = json_body();
            $error = Auth::changePassword((string) ($body['current'] ?? ''), (string) ($body['new'] ?? ''), (string) ($body['confirm'] ?? ''));
            if ($error) {
                json_response(['ok' => false, 'message' => $error], 422);
            }
            json_response(['ok' => true, 'message' => 'Password changed.']);

        default:
            json_response(['ok' => false, 'message' => 'Unknown action.'], 404);
    }
} catch (UploadException $ex) {
    json_response(['ok' => false, 'message' => $ex->getMessage()], 422);
} catch (Throwable $ex) {
    error_log('[admin] ' . $ex->getMessage());
    json_response(['ok' => false, 'message' => 'Something went wrong on the server: ' . $ex->getMessage()], 500);
}
