# ETRIP

Sistema de planejamento de viagem para carros elétricos no Espírito Santo.

Projeto desenvolvido para a feira de tecnologia da escola — Ensino Médio Técnico em Informática para Internet (1º IM01-EMI-IPI). Marca/assinatura do grupo: **SGX**.

**Prazo de entrega:** 13/11

---

## O que é o ETRIP

O ETRIP ajuda donos de carro elétrico a planejar uma viagem, respondendo três perguntas principais:

1. A bateria do carro aguenta chegar até o destino?
2. Se não aguentar, onde parar para recarregar no caminho — sem nunca deixar o carro descarregar completamente antes de chegar a um posto?
3. Quanto tempo a viagem leva e quanto custa?

Diferente de carro a combustão, carro elétrico precisa de planejamento: a autonomia é menor e os pontos de recarga são mais espalhados.

---

## Como funciona

**Tela inicial (`index.php`)** — só acessível com login:

O usuário escolhe:
- Marca, modelo e versão do carro elétrico
- Cidade de origem e cidade de destino
- Percentual de bateria ao sair
- Percentual de bateria desejado ao chegar

**Cálculo (`Resultado.php`)**

O sistema calcula a viagem considerando 3 situações possíveis, nunca assumindo que o carro pode chegar ao destino descarregado:

1. **Aguenta tranquilo** — chega com a reserva de bateria desejada
2. **Chega, mas abaixo da reserva** — o carro consegue chegar fisicamente ao destino, só que com menos bateria do que a pessoa queria; sugere recarregar lá
3. **Precisa parar antes** — a viagem gastaria mais energia do que a bateria tem inteira. O sistema busca a cidade mais distante possível, dentro do alcance real do carro, que tenha posto de recarga cadastrado. Se nem isso for possível, sugere recarregar mais antes de sair, na própria cidade de origem

**Resultado**

Mostra: imagem do carro, distância, tempo estimado, situação da bateria, posto sugerido (nome, endereço com botão de copiar, conector, potência, tempo de recarga) e custo estimado da viagem.

---

## Stack técnica

| Camada | Tecnologia |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Banco de dados | MySQL |
| Servidor (desenvolvimento) | Servidor embutido do PHP (`php -S`), com roteador customizado |

### Por que MySQL

O projeto migrou de arrays PHP locais para MySQL, guardando carros, postos de recarga, usuários, histórico de simulações e configurações do sistema. Cidades continuam em array (`CidadeModel.php`), por serem um conjunto pequeno e fixo (7 cidades).

---

## Contas e login

O ETRIP exige conta pra usar — cada simulação fica vinculada ao usuário que a fez.

- **Cadastro** (`cadastro.php`) — nome, e-mail, senha (mínimo 6 caracteres, armazenada com hash via `password_hash`)
- **Login** (`login.php`) — autentica e inicia sessão
- **Logout** (`logout.php`) — encerra a sessão
- Sem login, qualquer página protegida redireciona automaticamente pro login

---

## Painel de administração

Acessível só por contas marcadas como `is_admin = 1` no banco (link "Admin" só aparece no menu pra essas contas).

**Recursos:**
- **Modo teste** — liga/desliga globalmente; com o modo teste ativo, simulações feitas por qualquer usuário não são salvas no histórico (útil pra testar sem poluir dados reais)
- **Adicionar carro** — formulário que insere direto na tabela `carros`, sem precisar mexer no banco manualmente
- **Lista de carros cadastrados**
- **Lista de usuários**, com botão pra promover/remover admin de qualquer conta

A primeira conta admin precisa ser promovida manualmente via SQL:
```sql
UPDATE usuarios SET is_admin = 1 WHERE email = 'seuemail@exemplo.com';
```

---

## Estrutura de arquivos

```
etrip/
│
├── index.php              Tela principal — formulário (exige login)
├── Resultado.php           Cálculo + HTML do resultado
├── historico.php           Histórico de simulações (exige login)
├── admin.php               Painel de administração (exige admin)
├── login.php               Login
├── cadastro.php             Criar conta
├── logout.php               Encerrar sessão
├── auth.php                 Funções de sessão/autenticação
├── 404.php                  Página de erro personalizada
├── router.php                Necessário pro php -S mostrar o 404 e proteger a raiz
│
├── css/style.css
├── js/ (selects.js, sliders.js, tema.js)
|             
├── img/carrosimg/                Fotos dos carros e Logos
│
└── App/
    ├── classes/ (Carro.php, PostoRecarga.php — opcionais, não usadas ainda)
    └── data/
        ├── Query.php        Conexão PDO com o MySQL
        ├── CarroModel.php      Busca carros no banco
        ├── PostoModel.php      Busca postos no banco
        ├── CidadeModel.php     Cidades + distâncias (continua em array)
        └── ConfigModel.php     Modo teste e outras configurações
```

---

## Banco de dados — tabelas

| Tabela | Conteúdo |
|---|---|
| `carros` | Marca, modelo, versão, bateria, consumo, autonomia, imagem |
| `postos_recarga` | Nome, cidade, endereço, conector, potência |
| `usuarios` | Nome, e-mail, senha (hash), se é admin |
| `historico_simulacoes` | Cada simulação feita, vinculada ao `usuario_id` |
| `configuracoes` | Pares chave/valor (hoje só o `modo_teste`) |

---

## Dados cadastrados

**9+ versões de carro** (dados verificados em fichas técnicas e Inmetro) — mais os que forem adicionados pelo painel admin.

**7 cidades do ES**, seguindo a rota real da BR-101: Pinheiros → São Mateus → Linhares → Ibiraçu → Serra → Vitória → Vila Velha, com as 21 combinações de rota cadastradas.

**21+ postos de recarga reais**, pesquisados no Open Charge Map, cobrindo as 7 cidades.

---

## Recursos da interface

- Seleção de carro em cascata (marca → modelo → versão), gerada via PHP
- Imagem do carro na tela de resultado e no histórico
- Modo escuro, com preferência salva no navegador
- Histórico de simulações: cada card mostra carro, rota, distância e data; **clicar em qualquer lugar do card reabre o resultado** (recalcula com os mesmos dados); botão separado de excluir
- Botão de copiar endereço do posto sugerido
- Indicador de "Calculando..." no botão ao enviar o formulário
- Página 404 personalizada
- Tratamento de erro consistente via try/catch

---

## Escopo do MVP

**Incluído:** tudo listado acima.

**Fora do escopo (versões futuras):** múltiplas paradas otimizadas numa mesma simulação, comparação entre carros na mesma tela, mapa visual interativo, ajuste do cálculo por elevação do terreno, integração com APIs externas em tempo real, recuperação de senha, confirmação de e-mail.

---

## Equipe

| Responsável | Frente |
|---|---|
| **Miguel** | Backend PHP — lógica de cálculo, integração com MySQL |
| **Lorenzo** | Estrutura de dados — Models e banco |
| **Kaio** | Frontend — HTML, CSS, JavaScript |
| **Paulo** | Pesquisa de dados técnicos e testes de cenários |

---

## Material de divulgação

Comercial em estilo "carro 0km" (dinâmico, trilha forte), roteirizado com cenas geradas por IA, com prompts detalhados prontos para cada uma.
