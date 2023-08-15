<?php

use logger\Logger;

$logger = Logger::channel('address-plog');

$files = loadBackend('files');
$uuid = $files->fromGUIDv4($param);

$logger->debug('plogCamshot()', ['uuid' => $uuid]);

$file = $files->getFile($uuid);

if ($file) {
    $logger->debug('plogCamshot()', ['uuid' => $uuid, 'fileInfo' => $file['fileInfo']]);

    $metaData = $file['fileInfo']->metadata;

    header('Content-Type: ' . isset($metaData->contentType) ? $metaData->contentType : 'image/jpeg');

    echo stream_get_contents($file['stream']);

    exit;
}
