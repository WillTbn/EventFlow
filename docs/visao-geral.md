# Visão Geral – Fact Sphere (EventFlow)

## 1. Visão geral do produto

O **Fact Sphere** é uma aplicação web para gestão de eventos, apoiando desde o primeiro briefing até a entrega ao público final.

O principal objetivo é **centralizar todo o fluxo de um evento** (planejamento, organização, comunicação e acompanhamento) em um único painel, reduzindo ruídos e aumentando a clareza entre equipes e organizadores.

A página inicial (Welcome.vue) apresenta:
- A proposta de valor do produto.
- Os principais benefícios.
- O público-alvo.
- Um mini-tutorial de uso.

## 2. Público-alvo

A aplicação é pensada para quatro perfis principais:

- **Produtores de eventos**  
  Focados em controle de cronogramas, fornecedores, entregas e prioridades.

- **Equipes operacionais**  
  Acompanham tarefas, atualizações, status e se comunicam rapidamente.

- **Público participante**  
  Necessita de informações claras, check-in ágil e comunicação consistente antes, durante e após o evento.

- **Organizadores / Stakeholders**  
  Buscam indicadores, resultados e facilidade no compartilhamento de relatórios de desempenho dos eventos.

## 3. Proposta de valor

Mensagens principais extraídas da landing page:

- "Transformar cada evento em uma experiência fluida e memorável".
- "Centralizar o fluxo de eventos, do briefing ao público final".

Benefícios destacados:

- **Agenda inteligente** – organização de datas, horários e etapas do evento.
- **Convites automáticos** – envio de convites e acompanhamento de confirmações.
- **Check-in rápido** – facilitação da entrada no evento e controle de presença.

## 4. Funcionalidades (conceituais)

Com base na Welcome.vue, o Fact Sphere se propõe a oferecer:

- **Calendário centralizado com alertas**  
  Visão de todos os eventos, prazos e marcos importantes em um só lugar.

- **Gestão de equipes e tarefas**  
  Distribuição de responsabilidades, acompanhamento de pendências e progresso em tempo real.

- **Automação de convites e mensagens**  
  Envio de convites ao público, lembretes, confirmações de presença e comunicações padronizadas.

- **Check-in rápido e relatórios**  
  Registro de presença no dia do evento.  
  Geração de relatórios e indicadores (ex.: participantes, aprovações, andamento do evento).

A ilustração do "Painel geral" na landing page mostra exemplos de dados acompanhados:

- Evento ativo (ex.: "Festival Criativo 2026") com barra de progresso.
- Quantidade de convidados.
- Número de aprovações.
- Checklist automático com percentual concluído (ex.: "97% pronto").

## 5. Fluxo básico de uso (Mini tutorial)

Fluxo sugerido na seção "Mini tutorial" da Welcome.vue:

1. **Criar evento**  
   Definir nome, data, local e público-alvo.  
   O sistema gera páginas e comunicados automaticamente (ex.: página do evento, textos padrão de convites).

2. **Organizar tarefas e equipes**  
   Montar o fluxo de tarefas do evento.  
   Atribuir responsáveis, acompanhar pendências e progresso em tempo real.

3. **Engajar o público**  
   Enviar convites e mensagens aos participantes.  
   Acompanhar confirmações de presença (RSVP).  
   Habilitar check-in rápido no dia do evento.

4. **Analisar resultados**  
   Revisar indicadores (presença, engajamento, execução de tarefas).  
   Salvar aprendizados para próximos eventos e compartilhar relatórios com organizadores.

## 6. Navegação a partir da página inicial

Principais ações disponíveis na Welcome.vue:

- **"Começar agora"**  
  Redireciona para /login.  
  Objetivo: permitir acesso ao painel completo, onde o usuário autenticado pode criar, gerenciar e acompanhar eventos.

- **"Ver como funciona"**  
  Leva à seção com id #tutorial na própria landing page.  
  Objetivo: explicar o fluxo de uso em 3 passos (Criar → Organizar → Engajar).

- **"Ver painel completo"**  
  Também redireciona para /login.  
  Ref refuerça o caminho para o ambiente autenticado, com acesso a todas as funcionalidades.

## 7. Benefícios esperados

- Redução de ruídos de comunicação entre equipes e organizadores.  
- Maior previsibilidade e controle sobre a execução do evento.  
- Experiência mais fluida para o público, desde o convite até o check-in.  
- Visibilidade de métricas para tomada de decisão e melhoria contínua entre eventos.

---

> Este documento foi gerado com base no conteúdo da página resources/js/pages/Welcome.vue e descreve a visão conceitual do produto apresentada na landing page.
