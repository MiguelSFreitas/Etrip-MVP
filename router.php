<?php
/**
 * router.php
 */

require_once __DIR__ . '/auth.php';

$caminho = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$arquivo = __DIR__ . $caminho;

// A raiz do site ("/") não serve mais o index.php direto: decide
// pra onde ir dependendo de ter login ou não.
if ($caminho === '/' || $caminho === '/index.php') {
    iniciarSessao();
    if (!usuarioLogado()) {
        header('Location: /login.php');
        exit;
    }
    require __DIR__ . '/index.php';
    return true;
}

// Se o arquivo pedido existir de verdade (CSS, JS, imagem, etc.),
// deixa o servidor entregar ele normalmente.
if (file_exists($arquivo) && !is_dir($arquivo)) {
    return false;
}

// Se a URL corresponde a um arquivo .php que existe, deixa rodar normal.
if (preg_match('/\.php$/', $caminho) && file_exists($arquivo)) {
    return false;
}

// Qualquer outra coisa que não exista -> mostra o 404 personalizado.
if (!file_exists($arquivo)) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    return true;
}

return false;