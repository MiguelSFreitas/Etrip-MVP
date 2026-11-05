<?php
/**
 * PostoModel.php
 */

require_once __DIR__ . '/Query.php';

function buscarPostosPorCidade($cidade, $postos = null) {
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('SELECT * FROM postos_recarga WHERE cidade = ?');
    $stmt->execute([$cidade]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Busca um posto específico pelo ID.
 */
function buscarPostoPorId($postoId, $postos = null) {
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('SELECT * FROM postos_recarga WHERE id = ?');
    $stmt->execute([$postoId]);
    $posto = $stmt->fetch(PDO::FETCH_ASSOC);
    return $posto ?: null;
}