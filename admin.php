<?php
/**
 * admin.php
 */

require_once __DIR__ . '/auth.php';
exigirAdmin();

require_once __DIR__ . '/App/data/Query.php';
require_once __DIR__ . '/App/data/ConfigModel.php';

$pdo = conectarBanco();
$mensagem = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ---- Adicionar carro ----
    if (isset($_POST['acao']) && $_POST['acao'] === 'adicionar_carro') {
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

            $stmt = $pdo->prepare('INSERT INTO carros (marca, modelo, versao, bateria_kwh, consumo_kwh_km, autonomia_km, caminho_imagem) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$marca, $modelo, $versao, $bateria, $consumo, $autonomia, $imagem]);

            $mensagem = "Carro \"{$marca} {$modelo} {$versao}\" adicionado com sucesso.";
        } catch (Exception $e) {
            $erro = $e->getMessage();
        }
    }

    // ---- Ligar/desligar modo teste ----
    if (isset($_POST['acao']) && $_POST['acao'] === 'alternar_modo_teste') {
        definirModoTeste(!modoTesteAtivo());
    }

    // ---- Promover/remover admin de um usuário ----
    if (isset($_POST['acao']) && $_POST['acao'] === 'alternar_admin' && isset($_POST['usuario_id'])) {
        $stmt = $pdo->prepare('UPDATE usuarios SET is_admin = NOT is_admin WHERE id = ?');
        $stmt->execute([$_POST['usuario_id']]);
    }
}

$usuarios = $pdo->query('SELECT id, nome, email, is_admin, criado_em FROM usuarios ORDER BY criado_em DESC')->fetchAll(PDO::FETCH_ASSOC);
$carros = $pdo->query('SELECT * FROM carros ORDER BY marca, modelo, versao')->fetchAll(PDO::FETCH_ASSOC);
$modoTeste = modoTesteAtivo();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — ETRIP</title>
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

    <main class="camadas" style="margin-top: 110px; max-width: 960px;">

        <section class="camada">
            <header class="camada-topo">
                <span class="passo">🛠</span>
                <div>
                    <h2>Painel de administração</h2>
                    <p>Visível só pra contas marcadas como admin.</p>
                </div>
            </header>

            <?php if ($mensagem): ?>
                <p class="aviso" style="background:#e8f5e9; color:#2e7d32;"><?php echo htmlspecialchars($mensagem); ?></p>
            <?php endif; ?>
            <?php if ($erro): ?>
                <p class="aviso" role="alert"><?php echo htmlspecialchars($erro); ?></p>
            <?php endif; ?>
        </section>

        <!-- ===== MODO TESTE ===== -->
        <section class="camada">
            <header class="camada-topo">
                <span class="passo">🧪</span>
                <div>
                    <h2>Modo teste</h2>
                    <p>
                        Status atual:
                        <strong><?php echo $modoTeste ? 'LIGADO — simulações não estão sendo salvas' : 'DESLIGADO — tudo normal'; ?></strong>
                    </p>
                </div>
            </header>

            <form method="post" action="admin.php">
                <input type="hidden" name="acao" value="alternar_modo_teste">
                <button type="submit"><?php echo $modoTeste ? 'Desligar modo teste' : 'Ligar modo teste'; ?></button>
            </form>
        </section>

        <!-- ===== ADICIONAR CARRO ===== -->
        <section class="camada">
            <header class="camada-topo">
                <span class="passo">🚗</span>
                <div>
                    <h2>Adicionar carro</h2>
                    <p>Cadastra uma nova marca/modelo/versão no sistema.</p>
                </div>
            </header>

            <form method="post" action="admin.php">
                <input type="hidden" name="acao" value="adicionar_carro">

                <div class="campos">
                    <p class="campo">
                        <label for="marca">Marca</label>
                        <input type="text" name="marca" id="marca" required>
                    </p>
                    <p class="campo">
                        <label for="modelo">Modelo</label>
                        <input type="text" name="modelo" id="modelo" required>
                    </p>
                    <p class="campo">
                        <label for="versao">Versão</label>
                        <input type="text" name="versao" id="versao" required>
                    </p>
                    <p class="campo">
                        <label for="bateria_kwh">Bateria (kWh)</label>
                        <input type="number" step="0.01" name="bateria_kwh" id="bateria_kwh" required>
                    </p>
                    <p class="campo">
                        <label for="consumo_kwh_km">Consumo (kWh/km)</label>
                        <input type="number" step="0.001" name="consumo_kwh_km" id="consumo_kwh_km" required>
                    </p>
                    <p class="campo">
                        <label for="autonomia_km">Autonomia (km)</label>
                        <input type="number" name="autonomia_km" id="autonomia_km" required>
                    </p>
                    <p class="campo">
                        <label for="caminho_imagem">Caminho da imagem (opcional)</label>
                        <input type="text" name="caminho_imagem" id="caminho_imagem" placeholder="carrosimg/exemplo.png">
                    </p>
                </div>

                <div class="acao">
                    <button type="submit">Adicionar carro</button>
                </div>
            </form>
        </section>

        <!-- ===== CARROS CADASTRADOS ===== -->
        <section class="camada">
            <header class="camada-topo">
                <span class="passo">📋</span>
                <div>
                    <h2>Carros cadastrados (<?php echo count($carros); ?>)</h2>
                </div>
            </header>

            <div style="overflow-x:auto;">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>Marca</th>
                            <th>Modelo</th>
                            <th>Versão</th>
                            <th>Bateria</th>
                            <th>Autonomia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($carros as $c): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($c['marca']); ?></td>
                                <td><?php echo htmlspecialchars($c['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($c['versao']); ?></td>
                                <td><?php echo $c['bateria_kwh']; ?> kWh</td>
                                <td><?php echo $c['autonomia_km']; ?> km</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== USUÁRIOS ===== -->
        <section class="camada">
            <header class="camada-topo">
                <span class="passo">👥</span>
                <div>
                    <h2>Usuários cadastrados (<?php echo count($usuarios); ?>)</h2>
                </div>
            </header>

            <div style="overflow-x:auto;">
                <table class="tabela-admin">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Criado em</th>
                            <th>Admin?</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($u['nome']); ?></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($u['criado_em'])); ?></td>
                                <td><?php echo $u['is_admin'] ? '✅' : '—'; ?></td>
                                <td>
                                    <form method="post" action="admin.php" style="display:inline;">
                                        <input type="hidden" name="acao" value="alternar_admin">
                                        <input type="hidden" name="usuario_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" style="padding:6px 12px; font-size:12px; width:auto;">
                                            <?php echo $u['is_admin'] ? 'Remover admin' : 'Tornar admin'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>

    <footer class="rodape">
        <img src="/img/etrip-logo-completa.png" alt="ETRIP" class="logo-footer"> &copy;
    </footer>

    <script src="/js/tema.js"></script>

</body>

</html>