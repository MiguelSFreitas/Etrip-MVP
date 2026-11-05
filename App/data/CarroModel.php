<?php
/**
 * CarroModel.php
 */

require_once __DIR__ . '/Query.php';

function buscarCarroPorId($carroId, $carros = null) {
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('SELECT * FROM carros WHERE id = ?');
    $stmt->execute([$carroId]);
    $carro = $stmt->fetch(PDO::FETCH_ASSOC);
    return $carro ?: null;
}

/**
 * Retorna a lista de marcas únicas, em ordem alfabética.
 */
function listarMarcas($carros = null) {
    $pdo = conectarBanco();
    $stmt = $pdo->query('SELECT DISTINCT marca FROM carros ORDER BY marca');
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Retorna os carros de uma marca específica.
 */
function listarModelosPorMarca($marca, $carros = null) {
    $pdo = conectarBanco();
    $stmt = $pdo->prepare('SELECT * FROM carros WHERE marca = ?');
    $stmt->execute([$marca]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna TODOS os carros cadastrados.
 * Usada pelo index.php pra montar o select e pra exportar em JSON
 * pro selects.js filtrar modelo/versão no navegador.
 */
function listarTodosCarros() {
    $pdo = conectarBanco();
    $stmt = $pdo->query('SELECT * FROM carros ORDER BY marca, modelo, versao');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mantém a variável $carros disponível pra quem ainda usa esse nome
// direto (ex: index.php fazia `foreach ($carros as $carro)`), assim
// não precisa trocar require_once nem lógica nenhuma lá.
$carros = listarTodosCarros();