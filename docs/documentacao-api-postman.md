# API da Plataforma de Treinamentos — guia rápido

**Backend:** Laravel 10 · Sanctum · Base local: `http://127.0.0.1:8000/api`

## Rotas disponíveis

| Método | Rota | Acesso |
|---|---|---|
| `POST` | `/login` | Público |
| `GET` | `/me` · `POST /logout` | Autenticado |
| `GET` | `/dashboard/colaborador` | Autenticado |
| `GET` | `/dashboard/admin` | Administrador |
| `GET` | `/treinamentos` · `GET /treinamentos/{id}` | Autenticado |
| `POST` · `PATCH` · `DELETE` | `/treinamentos` · `/treinamentos/{id}` | Administrador |

O `DELETE` apenas inativa o treinamento. As consultas respeitam a empresa do usuário. O login atual é mock para desenvolvimento; não usa as credenciais reais do STW.

## Postman

Já existe uma coleção em `postman/Plataforma de Treinamentos.postman_collection.json`. Ela salva o token após o login e cobre autenticação, dashboards e operações de treinamento. Para o fluxo completo, execute na ordem: login, criar, listar, atualizar, detalhar, inativar e logout.

Use `Accept: application/json` e, nas rotas autenticadas, `Authorization: Bearer {{token}}`. A coleção e o guia de uso estão na pasta `postman/`, ainda não versionada pelo Git.

## Limite atual

O backend não tem endpoint para registrar conclusão nem integração de envio à Sempher. A conclusão disponível no frontend é demonstrativa e local. A documentação completa existente está em `docs/API.md`.

