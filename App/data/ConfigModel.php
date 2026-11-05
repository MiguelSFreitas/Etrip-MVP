<?php

require_once __DIR__ . '/Query.php'; // conectarBanco()

function modoTesteAtivo() {
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('SELECT valor FROM configuracoes WHERE chave = ?');
    $stmt->execute(['modo_teste']);
    $linha = $stmt->fetch(PDO::FETCH_ASSOC);
    return $linha && $linha['valor'] === '1';
}

function definirModoTeste($ativo) {
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('UPDATE configuracoes SET valor = ? WHERE chave = ?');
    $stmt->execute([$ativo ? '1' : '0', 'modo_teste']);
}