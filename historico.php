<?php
/**
 * historico.php
 *
 * Mostra as simulações salvas na tabela historico_simulacoes como
 * cards — só as do usuário que está logado (cada conta só vê e
 * mexe no próprio histórico).
 */

require_once __DIR__ . '/auth.php';
exigirLogin();

require_once __DIR__ . '/backend/data/Query.php';      // conectarBanco()

$pdo = conectarBanco();
$usuarioId = idUsuarioLogado();

// Processa exclusão, se o botão de lixeira de algum item foi clicado.
// O "AND usuario_id = ?" garante que ninguém apaga histórico de outra conta.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $stmt = $pdo->prepare('DELETE FROM historico_simulacoes WHERE id = ? AND usuario_id = ?');
    $stmt->execute([$_POST['excluir_id'], $usuarioId]);

    header('Location: historico.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM historico_simulacoes WHERE usuario_id = ? ORDER BY data_hora DESC LIMIT 20');
$stmt->execute([$usuarioId]);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Histórico — ETRIP</title>
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
            <li><a href="index.php#viagem">Calcular viagem</a></li>
            <li><a href="historico.php" class="ativo">Histórico</a></li>
            <?php if (souAdmin()): ?>
                <li><a href="admin.php">Admin</a></li>
            <?php endif; ?>
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
                <span class="passo">🕒</span>
                <div>
                    <h2>Histórico de simulações</h2>
                    <p>Suas últimas viagens calculadas no ETRIP.</p>
                </div>
            </header>
        </section>

        <?php if (empty($historico)): ?>

            <section class="camada" style="text-align:center;">
                <p>Nenhuma simulação feita ainda.</p>
                <div class="acao">
                    <a href="index.php#viagem" class="hero-botao" style="display:inline-block;">Fazer a primeira simulação</a>
                </div>
            </section>

        <?php else: ?>

            <div class="grade-historico">
                <?php foreach ($historico as $item): ?>
                    <div class="historico-card historico-card-clicavel">

                        <?php if (!empty($item['imagem'])): ?>
                            <img src="/<?php echo htmlspecialchars($item['imagem']); ?>" alt="<?php echo htmlspecialchars($item['carro']); ?>" class="historico-img">
                        <?php else: ?>
                            <div class="historico-img historico-img-vazia" aria-hidden="true">🚗</div>
                        <?php endif; ?>

                        <div class="historico-info">
                            <span class="historico-carro"><?php echo htmlspecialchars($item['carro']); ?></span>
                            <span class="historico-rota">
                                <?php echo htmlspecialchars($item['origem']); ?> → <?php echo htmlspecialchars($item['destino']); ?>
                            </span>
                            <span class="historico-detalhe">
                                <?php echo $item['distancia_km']; ?> km · <?php echo date('d/m/Y H:i', strtotime($item['data_hora'])); ?>
                            </span>
                        </div>

                        <div class="historico-acoes">
                            <!-- Form escondido: o clique na div inteira do card dispara este submit -->
                            <form method="post" action="Resultado.php" class="historico-abrir-form">
                                <input type="hidden" name="carro_id" value="<?php echo htmlspecialchars($item['carro_id']); ?>">
                                <input type="hidden" name="origem" value="<?php echo htmlspecialchars($item['origem']); ?>">
                                <input type="hidden" name="destino" value="<?php echo htmlspecialchars($item['destino']); ?>">
                                <input type="hidden" name="percentual_saida" value="<?php echo htmlspecialchars($item['percentual_saida']); ?>">
                                <input type="hidden" name="percentual_chegada" value="<?php echo htmlspecialchars($item['percentual_chegada']); ?>">
                            </form>

                            <form method="post" action="historico.php" class="historico-excluir-form" onclick="event.stopPropagation();">
                                <input type="hidden" name="excluir_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <button type="submit" class="botao-excluir" aria-label="Excluir esta simulação" title="Excluir">
                                    🗑
                                </button>
                            </form>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <footer class="rodape">
        <img src="/img/etrip-logo-completa.png" alt="ETRIP" class="logo-footer"> &copy;
    </footer>

    <script src="/js/tema.js"></script>

    <script>
        document.querySelectorAll('.historico-card-clicavel').forEach(function (card) {
            card.addEventListener('click', function () {
                const form = card.querySelector('.historico-abrir-form');
                if (form) {
                    form.submit();
                }
            });
        });
    </script>

</body>

</html>