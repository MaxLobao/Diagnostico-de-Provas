# Diagnóstico de Provas 7.1 (Protótipo em PHP)

Protótipo responsivo (Bootstrap 5 + Google Material Icons) de um sistema de simulados que gera dicas e priorizações com base na análise dos simulados.

## Como abrir no seu PC (Windows/Mac/Linux)

1) Instale PHP 8+ (qualquer pacote como XAMPP/WAMP/Laragon também serve).  
2) No terminal, entre na pasta do projeto e rode:

```bash
cd Diagnostico-de-Provas
php -S localhost:8000 -t public
```

3) Abra no navegador: `http://localhost:8000`

## Login de demonstração

- Email: `aluno@teste.com`
- Senha: `123456`

Você pode criar outra conta na tela de cadastro.

## Estrutura do projeto

- `public/`  
  Webroot (onde o servidor aponta). Roteador simples (`index.php`) e assets.
- `app/`  
  Bootstrap do app, helpers, auth, DB e views.
- `storage/`  
  Banco SQLite (`database.sqlite`). É criado/atualizado automaticamente no primeiro acesso.

## Observações importantes

- Este é um protótipo funcional (sem framework).  
- Os gráficos usam **Chart.js via CDN**.
- O modal de criar simulado suporta **template (ENEM/FUVEST/UNICAMP)** e **personalizado**.
- A tela “Caderno do simulado” permite marcar:
  - **Erro** (vermelho)
  - **Acerto por chute** (azul)
  - **Acerto consciente** (verde – padrão)
  Ao marcar erro/chute, abre um formulário para disciplina + motivo, e isso alimenta **Radar de prioridades** e **Diagnóstico**.

Se você quiser, depois a gente evolui para:
- integração Asaas (pagamento), 
- upload de foto de perfil, 
- e regras mais avançadas de templates/assuntos.
