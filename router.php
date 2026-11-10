<?php
/**
 * router.php
 */

require_once __DIR__ . '/auth.php';

$caminho = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$arquivo = __DIR__ . $caminho;


if ($caminho === '/' || $caminho === '/index.php') {
    iniciarSessao();
    if (!usuarioLogado()) {
        header('Location: /login.php');
        exit;
    }
    require __DIR__ . '/index.php';
    return true;
}


if (file_exists($arquivo) && !is_dir($arquivo)) {
    return false;
}

if (preg_match('/\.php$/', $caminho) && file_exists($arquivo)) {
    return false;
}


if (!file_exists($arquivo)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return true;
}

return false;