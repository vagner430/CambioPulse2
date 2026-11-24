<?php
$msgAlerta = '';
$paresPermitidos = ['USD-BRL', 'EUR-BRL', 'BTC-BRL'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['par_moedas'], $_POST['valor_limite'])) {
    $parBruto = trim((string) $_POST['par_moedas']);
    $limiteBruto = filter_var($_POST['valor_limite'], FILTER_VALIDATE_FLOAT);

    if (!in_array($parBruto, $paresPermitidos, true)) {
        $msgAlerta = "<p class='alerta-erro'>Par de moedas inválido.</p>";
    } elseif ($limiteBruto === false || !is_finite($limiteBruto) || $limiteBruto <= 0) {
        $msgAlerta = "<p class='alerta-erro'>Informe um valor numérico maior que zero.</p>";
    } else {
        $parSeguro = htmlspecialchars($parBruto, ENT_QUOTES, 'UTF-8');
        $limiteFormatado = htmlspecialchars(number_format($limiteBruto, 2, ',', '.'), ENT_QUOTES, 'UTF-8');
        $msgAlerta = "<p class='alerta-sucesso'>Alerta configurado para <strong>{$parSeguro}</strong> ao atingir <strong>R$ {$limiteFormatado}</strong></p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CambioPulse — Monitoramento & Câmbio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <div class="brand">
                <span class="brand-badge">CP</span>
                <div>
                    <h1>CambioPulse</h1>
                    <p class="subtitle">Monitoramento cambial & conversão em tempo real</p>
                </div>
            </div>
            <span class="status-pill"><span class="dot"></span> Mercado Online</span>
        </div>
    </header>

    <main class="container">
        <section class="cards-grid">
            <div class="card card-usd">
                <div class="card-top">
                    <span class="ticker">USD / BRL</span>
                    <span class="tag">Dólar Comercial</span>
                </div>
                <p class="valor" id="usd">Carregando...</p>
            </div>
            <div class="card card-eur">
                <div class="card-top">
                    <span class="ticker">EUR / BRL</span>
                    <span class="tag">Euro</span>
                </div>
                <p class="valor" id="eur">Carregando...</p>
            </div>
            <div class="card card-btc">
                <div class="card-top">
                    <span class="ticker">BTC / BRL</span>
                    <span class="tag">Bitcoin</span>
                </div>
                <p class="valor" id="btc">Carregando...</p>
            </div>
        </section>

        <div class="forms-grid">
            <section class="card-form">
                <h2>Conversor Rápido</h2>
                <form id="form-conversor">
                    <div class="field-group">
                        <label for="valor">Montante</label>
                        <input type="number" step="0.01" min="0.01" id="valor" required placeholder="Ex: 250.00">
                    </div>

                    <div class="row-selects">
                        <div class="field-group">
                            <label for="origem">Moeda de Origem</label>
                            <select id="origem">
                                <option value="USD">Dólar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                                <option value="BTC">Bitcoin (BTC)</option>
                                <option value="BRL">Real (BRL)</option>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="destino">Moeda de Destino</label>
                            <select id="destino">
                                <option value="BRL" selected>Real (BRL)</option>
                                <option value="USD">Dólar (USD)</option>
                                <option value="EUR">Euro (EUR)</option>
                                <option value="BTC">Bitcoin (BTC)</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit">Calcular Conversão</button>
                </form>
                <div id="resultado"></div>
            </section>

            <section class="card-form">
                <h2>Gatilho de Alerta</h2>
                <form method="POST" action="CambioPulse.php">
                    <div class="field-group">
                        <label for="par_moedas">Par Monitorado</label>
                        <select name="par_moedas" id="par_moedas">
                            <option value="USD-BRL">Dólar / Real (USD-BRL)</option>
                            <option value="EUR-BRL">Euro / Real (EUR-BRL)</option>
                            <option value="BTC-BRL">Bitcoin / Real (BTC-BRL)</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label for="valor_limite">Cotação-Alvo (R$)</label>
                        <input type="number" step="0.01" min="0.01" id="valor_limite" name="valor_limite" required placeholder="Ex: 5.50">
                    </div>

                    <button type="submit" class="btn-secondary">Registrar Alerta</button>
                </form>
                <div><?= $msgAlerta ?></div>
            </section>
        </div>

        <section class="grafico-container">
            <div class="grafico-header">
                <h2>Histórico Cambial (7 Dias)</h2>
                <span class="grafico-legenda">Comparativo USD vs EUR em Reais</span>
            </div>
            <div style="height: 320px;">
                <canvas id="grafico"></canvas>
            </div>
        </section>
    </main>

    <script>
        const moedasValidas = ['BRL', 'USD', 'EUR', 'BTC'];
        let taxas = {
            BRL: 1.0,
            USD: 5.05,
            EUR: 5.42,
            BTC: 429974.00
        };

        function formatarReais(valor) {
            return 'R$ ' + valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function renderizarCotacoes() {
            document.getElementById('usd').textContent = formatarReais(taxas.USD);
            document.getElementById('eur').textContent = formatarReais(taxas.EUR);
            document.getElementById('btc').textContent = formatarReais(taxas.BTC);
        }

        function buscarCotacoes() {
            fetch('https://economia.awesomeapi.com.br/last/USD-BRL,EUR-BRL,BTC-BRL')
                .then(res => {
                    if (!res.ok) throw new Error('Falha na requisição');
                    return res.json();
                })
                .then(dados => {
                    const usd = parseFloat(dados?.USDBRL?.bid);
                    const eur = parseFloat(dados?.EURBRL?.bid);
                    const btc = parseFloat(dados?.BTCBRL?.bid);

                    if (Number.isFinite(usd) && usd > 0) taxas.USD = usd;
                    if (Number.isFinite(eur) && eur > 0) taxas.EUR = eur;
                    if (Number.isFinite(btc) && btc > 0) taxas.BTC = btc;

                    renderizarCotacoes();
                })
                .catch(() => {
                    renderizarCotacoes();
                });
        }

        function exibirMensagemConversao(textoPrefixo, textoDestaque, tipoClasse) {
            const boxResultado = document.getElementById('resultado');
            boxResultado.replaceChildren();

            const p = document.createElement('p');
            p.className = tipoClasse;
            p.append(document.createTextNode(textoPrefixo));

            if (textoDestaque) {
                const strong = document.createElement('strong');
                strong.textContent = textoDestaque;
                p.append(strong);
            }

            boxResultado.appendChild(p);
        }

        document.getElementById('form-conversor').addEventListener('submit', function(e) {
            e.preventDefault();

            const valor = parseFloat(document.getElementById('valor').value);
            const de = document.getElementById('origem').value;
            const para = document.getElementById('destino').value;

            if (!Number.isFinite(valor) || valor <= 0) {
                exibirMensagemConversao('Digite um valor maior que zero para converter.', '', 'alerta-erro');
                return;
            }

            if (!moedasValidas.includes(de) || !moedasValidas.includes(para)) {
                exibirMensagemConversao('Moeda selecionada inválida.', '', 'alerta-erro');
                return;
            }

            const emReais = valor * taxas[de];
            const convertido = emReais / taxas[para];

            const v1 = valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
            const v2 = convertido.toLocaleString('pt-BR', { minimumFractionDigits: 4 });

            exibirMensagemConversao(`${v1} ${de} = `, `${v2} ${para}`, 'alerta-sucesso');
        });

        function montarGrafico() {
            let datas = ['6d', '5d', '4d', '3d', '2d', 'Ontem', 'Hoje'];
            let histUsd = [5.01, 5.03, 4.98, 5.02, 5.05, 5.00, 5.05];
            let histEur = [5.55, 5.58, 5.54, 5.59, 5.62, 5.61, 5.42];

            Promise.all([
                fetch('https://economia.awesomeapi.com.br/json/daily/USD-BRL/7').then(r => {
                    if (!r.ok) throw new Error('Falha USD');
                    return r.json();
                }),
                fetch('https://economia.awesomeapi.com.br/json/daily/EUR-BRL/7').then(r => {
                    if (!r.ok) throw new Error('Falha EUR');
                    return r.json();
                })
            ])
            .then(([dadosUsd, dadosEur]) => {
                if (Array.isArray(dadosUsd) && Array.isArray(dadosEur) && dadosUsd.length > 0 && dadosEur.length > 0) {
                    const usdValidos = dadosUsd.map(d => parseFloat(d?.bid)).filter(n => Number.isFinite(n)).reverse();
                    const eurValidos = dadosEur.map(d => parseFloat(d?.bid)).filter(n => Number.isFinite(n)).reverse();
                    const labelsValidas = dadosUsd.map(d => {
                        const ts = Number(d?.timestamp);
                        if (!Number.isFinite(ts) || ts <= 0) return '';
                        const dia = new Date(ts * 1000);
                        return dia.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
                    }).filter(Boolean).reverse();

                    if (usdValidos.length === labelsValidas.length && eurValidos.length === labelsValidas.length && labelsValidas.length > 0) {
                        datas = labelsValidas;
                        histUsd = usdValidos;
                        histEur = eurValidos;
                    }
                }
                desenhar(datas, histUsd, histEur);
            })
            .catch(() => {
                desenhar(datas, histUsd, histEur);
            });
        }

        function desenhar(labels, dadosUsd, dadosEur) {
            Chart.defaults.color = '#94a3b8';
            Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.12)';
            const ctx = document.getElementById('grafico').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Dólar (USD)',
                            data: dadosUsd,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.14)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#10b981',
                            tension: 0.35,
                            fill: true
                        },
                        {
                            label: 'Euro (EUR)',
                            data: dadosEur,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.10)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#f59e0b',
                            tension: 0.35,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#e2e8f0',
                                font: { family: 'Plus Jakarta Sans', weight: '600' }
                            }
                        }
                    }
                }
            });
        }

        buscarCotacoes();
        montarGrafico();
    </script>
</body>
</html>
