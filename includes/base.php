<?php
// Works whether the project is at http://localhost/ or http://localhost/agritrade/ or any sub-folder.
if (!isset($BASE_URL)) {
    $__root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
    $__doc  = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';
    $BASE_URL = ($__doc !== '' && strpos($__root, $__doc) === 0) ? substr($__root, strlen($__doc)) : '';
    $BASE_URL = rtrim($BASE_URL, '/');
}
