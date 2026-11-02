<?php
/**
 * auth.php
 *
 * Funções de autenticação/sessão do ETRIP. Incluir esse arquivo no
 * topo de qualquer página que precise saber quem está logado ou que
 * precise EXIGIR login (chamando exigirLogin()).
 */

function iniciarSessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Retorna true se tem alguém logado nessa sessão.
 */
function usuarioLogado() {
    iniciarSessao();
    return isset($_SESSION['usuario_id']);
}

/**
 * Se não tiver ninguém logado, redireciona pro login e para a
 * execução da página ali mesmo (exit). Chame isso no topo de toda
 * página que só pode ser vista por quem está logado.
 */
function exigirLogin() {
    iniciarSessao();
    if (!usuarioLogado()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Atalho pra pegar o id do usuário logado (só chame depois de
 * exigirLogin(), senão pode vir null).
 */
function idUsuarioLogado() {
    iniciarSessao();
    return $_SESSION['usuario_id'] ?? null;
}

function nomeUsuarioLogado() {
    iniciarSessao();
    return $_SESSION['usuario_nome'] ?? '';
}

/**
 * Retorna true se o usuário logado é administrador.
 */
function souAdmin() {
    iniciarSessao();
    return !empty($_SESSION['is_admin']);
}

/**
 * Exige login E admin. Se não estiver logado, manda pro login; se
 * estiver logado mas não for admin, manda pra home (não mostra nem
 * que a página de admin existe, pra não dar pista).
 */
function exigirAdmin() {
    exigirLogin();
    if (!souAdmin()) {
        header('Location: index.php');
        exit;
    }
}