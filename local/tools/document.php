<?php
/**
 * Публичная раздача устава и политики ПДн (просмотр PDF в браузере).
 */
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('NO_KEEP_STATISTIC', true);

$docKey = strtolower(trim((string)($_GET['doc'] ?? '')));
$assetsDir = $_SERVER['DOCUMENT_ROOT'] . '/local/templates/my_template/assets';

$resolveFile = static function (string $baseName) use ($assetsDir): ?array {
    $candidates = [
        ['ext' => 'pdf', 'mime' => 'application/pdf', 'inline' => true],
        ['ext' => 'docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'inline' => false],
    ];
    foreach ($candidates as $candidate) {
        $path = $assetsDir . '/' . $baseName . '.' . $candidate['ext'];
        if (is_readable($path)) {
            return [
                'path' => $path,
                'mime' => $candidate['mime'],
                'inline' => $candidate['inline'],
                'filename' => $baseName . '.' . $candidate['ext'],
            ];
        }
    }

    return null;
};

$map = [
    'ustav' => $resolveFile('USTAV'),
    'politika' => $resolveFile('POLITIKA'),
];

if (!isset($map[$docKey]) || $map[$docKey] === null) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Документ не найден';
    exit;
}

$file = $map[$docKey];
$path = $file['path'];
$size = filesize($path);
if ($size === false) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Ошибка чтения файла';
    exit;
}

$disposition = $file['inline'] ? 'inline' : 'attachment';
$filename = str_replace(['"', '\\'], '', $file['filename']);

header('Content-Type: ' . $file['mime']);
header('Content-Length: ' . $size);
header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: public, max-age=86400');

readfile($path);
exit;
