# CambioPulse — Painel de Inteligência Cambial

Aplicação web voltada para monitoramento financeiro, conversão instantânea de divisas e gestão de gatilhos de preço, construída com arquitetura leve em PHP, JavaScript moderno e modelagem relacional MySQL.

---

## Visão Geral do Sistema

O **CambioPulse** centraliza dados do mercado de câmbio em uma interface escura de alto contraste (*Dark Fintech*), permitindo acompanhar a oscilação das principais moedas frente ao Real brasileiro (BRL) sem necessidade de recarregar a página.

### Recursos Principais

1. **Painel de Cotações ao Vivo**  
   Consulta assíncrona via Fetch API para exibição imediata das taxas de compra de **Dólar Americano (USD)**, **Euro (EUR)** e **Bitcoin (BTC)**, com valores de contingência (*fallback*) caso a rede esteja indisponível.

2. **Calculadora de Câmbio Cruzado**  
   Motor de conversão em tempo real que utiliza o Real como base intermediária para converter livremente qualquer combinação entre `BRL`, `USD`, `EUR` e `BTC`.

3. **Monitor de Gatilhos de Preço**  
   Módulo backend em PHP preparado para validar e registrar metas de cotação por par monetário (`USD-BRL`, `EUR-BRL`, `BTC-BRL`).

4. **Série Histórica de 7 Dias**  
   Gráfico de linhas suavizadas renderizado com **Chart.js**, comparando a trajetória semanal do Dólar e do Euro.

---

## Stack & Arquitetura

| Camada | Tecnologias | Papel no Projeto |
| :--- | :--- | :--- |
| **Interface (UI)** | HTML5, CSS3 (Variáveis & Grid), Google Fonts | Layout responsivo em tema escuro com tipografia *Plus Jakarta Sans* |
| **Lógica Client-Side** | JavaScript (ES6+), Fetch API, Chart.js | Consumo da API externa, cálculos de conversão e plotagem gráfica |
| **Processamento Server-Side** | PHP 8+ | Tratamento de requisições `POST` e validação de alertas |
| **Persistência & BI** | MySQL / MariaDB | Armazenamento de histórico, logs de conversão e consultas analíticas |
| **Provedor de Dados** | AwesomeAPI | Fonte pública de cotações e fechamentos diários |

---

## Organização do Repositório

```text
projeto_CambioPulse/
├── CambioPulse.php   # Aplicação principal (estrutura HTML, controlador PHP e scripts JS)
├── style.css         # Folha de estilos completa com tema Dark Fintech
├── schemas.sql       # DDL das tabelas e queries de análise (agregações e cruzamento de status)
└── README.md         # Documentação técnica do projeto_CambioPulse