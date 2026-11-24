CREATE DATABASE IF NOT EXISTS cambiopulse_db;
USE cambiopulse_db;

DROP TABLE IF EXISTS log_conversoes;
DROP TABLE IF EXISTS alertas;
DROP TABLE IF EXISTS cotacoes;

CREATE TABLE cotacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moeda_origem VARCHAR(10) NOT NULL,
    moeda_destino VARCHAR(10) NOT NULL,
    valor_compra DECIMAL(15, 6) NOT NULL,
    valor_venda DECIMAL(15, 6) NOT NULL,
    variacao DECIMAL(10, 4) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alertas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    par_moedas VARCHAR(10) NOT NULL,
    valor_limite DECIMAL(15, 2) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE log_conversoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    moeda_origem VARCHAR(10) NOT NULL,
    moeda_destino VARCHAR(10) NOT NULL,
    valor_digitado DECIMAL(15, 2) NOT NULL,
    valor_resultado DECIMAL(15, 6) NOT NULL,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

SELECT 
    par_moedas, 
    COUNT(*) AS qtd_alertas, 
    ROUND(AVG(valor_limite), 2) AS media_limite, 
    MIN(valor_limite) AS menor_valor, 
    MAX(valor_limite) AS maior_valor
FROM alertas
GROUP BY par_moedas;

SELECT 
    moeda_origem, 
    COUNT(*) AS qtd_conversoes, 
    SUM(valor_digitado) AS total_convertido, 
    ROUND(AVG(valor_digitado), 2) AS media_valor
FROM log_conversoes
GROUP BY moeda_origem;

SELECT 
    a.id,
    a.par_moedas,
    a.valor_limite,
    c.valor_compra AS cotacao_atual,
    c.data_hora,
    IF(c.valor_compra >= a.valor_limite, 'Atingido', 'Aguardando') AS status
FROM alertas a
JOIN cotacoes c ON a.par_moedas = CONCAT(c.moeda_origem, '-', c.moeda_destino)
WHERE c.id IN (
    SELECT MAX(id) 
    FROM cotacoes 
    GROUP BY moeda_origem, moeda_destino
)
ORDER BY a.criado_em DESC;
