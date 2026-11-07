<?php
/**
 * admin_editar_carro.php
 */

require_once __DIR__ . '/auth.php';
exigirAdmin();

require_once __DIR__ . '/App/data/Query.php';

$pdo = conectarBanco();
$erro = null;
$mensagem = null;

$id = isset($_GET['id']) ? (int) $_GET['id'] : (isset($_POST['id']) ? (int) $_POST['id'] : 0);

if (!$id) {
    header('Location: admin.php');
    exit;
}

// Salva as alterações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $versao = trim($_POST['versao'] ?? '');
        $bateria = (float) ($_POST['bateria_kwh'] ?? 0);
        $consumo = (float) ($_POST['consumo_kwh_km'] ?? 0);
        $autonomia = (int) ($_POST['autonomia_km'] ?? 0);
        $imagem = trim($_POST['caminho_imagem'] ?? '');

        if ($marca === '' || $modelo === '' || $versao === '' || $bateria <= 0 || $consumo <= 0 || $autonomia <= 0) {
            throw new Exception('Preencha marca, modelo, versão, bateria, consumo e autonomia corretamente.');
        }

        $stmt = $pdo->prepare(
            'UPDATE carros
             SET marca = ?, modelo = ?, versao = ?, bateria_kwh = ?, consumo_kwh_km = ?, autonomia_km = ?, caminho_imagem = ?
             WHERE id = ?'
        );
        $stmt->execute([$marca, $modelo, $versao, $bateria, $consumo, $autonomia, $imagem, $id]);

        $mensagem = 'Carro atualizado com sucesso.';
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}

// Busca os dados atuais do carro (já atualizados, se acabou de salvar)
$stmt = $pdo->prepare('SELECT * FROM carros WHERE id = ?');
$stmt->execute([$id]);
$carro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$carro) {
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar carro — ETRIP</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>
        if (localStorage.getItem('etrip-tema') === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    </script>
</head>

<body>

    <nav class="menu">
        <a href="index.php" class="marca">
            <img src="/img/etrip-logo-completa.png" alt="ETRIP" class="logo-nav">
        </a>
        <ul>
            <li><a href="index.php">Início</a></li>
            <li><a href="admin.php" class="ativo">Admin</a></li>
        </ul>
        <div class="usuario-info">
            <span>Olá, <?php echo htmlspecialchars(nomeUsuarioLogado()); ?></span>
            <a href="logout.php">Sair</a>
        </div>
        <button type="button" id="toggle-tema" class="botao-tema" aria-label="Alternar modo escuro" title="Alternar modo escuro">🌙</button>
    </nav>

    <main class="camadas" style="margin-top: 110px;">

        <section class="camada">
            <header class="camada-topo">
                <span class="passo">✏️</span>
                <div>
                    <h2>Editar carro</h2>
                    <p><?php echo htmlspecialchars($carro['marca'] . ' ' . $carro['modelo'] . ' ' . $carro['versao']); ?></p>
                </div>
            </header>

            <?php if ($mensagem): ?>
                <p class="aviso" style="background:#e8f5e9; color:#2e7d32;"><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
            <?php if ($erro): ?>
                <p class="aviso" role="alert"><?php echo htmlspecialchars($erro); ?></p>
            <?php endif; ?>

            <form method="post" action="admin_editar_carro.php?id=<?php echo $id; ?>">
                <input type="hidden" name="id" value="<?php echo $id; ?>">

                <div class="campos">
                    <p class="campo">
                        <label for="marca">Marca</label>
                        <input type="text" name="marca" id="marca" required value="<?php echo htmlspecialchars($carro['marca']); ?>">
                    </p>
                    <p class="campo">
                        <label for="modelo">Modelo</label>
                        <input type="text" name="modelo" id="modelo" required value="<?php echo htmlspecialchars($carro['modelo']); ?>">
                    </p>
                    <p class="campo">
                        <label for="versao">Versão</label>
                        <input type="text" name="versao" id="versao" required value="<?php echo htmlspecialchars($carro['versao']); ?>">
                    </p>
                    <p class="campo">
                        <label for="bateria_kwh">Bateria (kWh)</label>
                        <input type="number" step="0.01" name="bateria_kwh" id="bateria_kwh" required value="<?php echo htmlspecialchars($carro['bateria_kwh']); ?>">
                    </p>
                    <p class="campo">
                        <label for="consumo_kwh_km">Consumo (kWh/km)</label>
                        <input type="number" step="0.001" name="consumo_kwh_km" id="consumo_kwh_km" required value="<?php echo htmlspecialchars($carro['consumo_kwh_km']); ?>">
                    </p>
                    <p class="campo">
                        <label for="autonomia_km">Autonomia (km)</label>
                        <input type="number" name="autonomia_km" id="autonomia_km" required value="<?php echo htmlspecialchars($carro['autonomia_km']); ?>">
                    </p>
                    <p class="campo">
                        <label for="caminho_imagem">Caminho da imagem</label>
                        <input type="text" name="caminho_imagem" id="caminho_imagem" value="<?php echo htmlspecialchars($carro['caminho_imagem']); ?>">
                    </p>
                </div>

                <div class="acao" style="display:flex; gap:10px;">
                    <button type="submit">Salvar alterações</button>
                    <a href="admin.php" class="hero-botao" style="background:var(--gelo); color:var(--azul-escuro); display:flex; align-items:center; justify-content:center;">Cancelar</a>
                </div>
            </form>
        </section>

    </main>

    <footer class="rodape">
        <img src="/img/etrip-logo-completa.png" alt="ETRIP" class="logo-footer">
    </footer>

    <script src="/js/tema.js"></script>

</body>

</html>