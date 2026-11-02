-- ============================================================


USE etrip;

ALTER TABLE usuarios ADD COLUMN is_admin TINYINT(1) NOT NULL DEFAULT 0;

CREATE TABLE IF NOT EXISTS configuracoes (
    chave VARCHAR(50) PRIMARY KEY,
    valor VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

INSERT INTO configuracoes (chave, valor) VALUES ('modo_teste', '0');

-- Depois de criar sua conta normalmente pelo cadastro.php, rode este
-- UPDATE trocando o e-mail pelo da conta que vai virar admin:
--
-- UPDATE usuarios SET is_admin = 1 WHERE email = 'seuemail@exemplo.com';