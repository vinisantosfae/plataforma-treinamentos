# Documentação da API

API REST da plataforma de treinamentos da Sempher Solutions (Laravel 10 + Sanctum).

- **URL base (desenvolvimento):** `http://127.0.0.1:8000/api`
- **Formato:** JSON. Envie sempre `Accept: application/json` e, quando houver corpo, `Content-Type: application/json`.
- **Convenção de nomes:** o **corpo das requisições** usa `snake_case` (igual às colunas do banco); as **respostas** usam `camelCase`.

## Autenticação

O login devolve um token que deve ser enviado em todas as demais rotas:

```
Authorization: Bearer <token>
```

> **Atenção:** hoje o login é um **mock** para desenvolvimento. Só funciona com `APP_ENV=local` ou `testing`, a senha é sempre `password` e os usuários vêm de `resources/mock-auth-users.json`. A integração real com o STW (CPF e senha) ainda não existe.

| CPF | Nome | Empresa | Administrador |
|---|---|---|---|
| 111.111.111-11 | Ana Souza | ACME (1001) | sim |
| 222.222.222-22 | Bruno Lima | ACME (1001) | não |
| 333.333.333-33 | Carla Oliveira | Beta (1002) | sim |

### Escopo por empresa e perfis

- Toda consulta é filtrada pela **empresa da pessoa autenticada**. Um recurso de outra empresa responde `404`, como se não existisse.
- Rotas marcadas como **admin** respondem `403` (`Acesso restrito a administradores.`) para quem não é administrador.
- Colaborador (não admin) só enxerga treinamentos **ativos**. O administrador enxerga ativos e inativos.

## Erros

| Status | Quando | Corpo |
|---|---|---|
| 401 | Token ausente, inválido ou revogado | `{"message": "Unauthenticated."}` |
| 403 | Rota exclusiva de administrador | `{"message": "Acesso restrito a administradores."}` |
| 404 | Recurso inexistente ou de outra empresa | `{"message": "No query results for model ..."}` |
| 422 | Validação ou regra de negócio | `{"message": "...", "errors": {"campo": ["mensagem"]}}` |

Com `APP_DEBUG=true` o Laravel acrescenta `exception`, `file` e `trace` ao erro. Isso não deve existir em produção.

## Rotas

| Método | Rota | Acesso | Descrição |
|---|---|---|---|
| POST | `/login` | público | Autentica e devolve o token |
| GET | `/me` | logado | Identidade de quem está logado |
| POST | `/logout` | logado | Revoga o token atual |
| GET | `/dashboard/colaborador` | logado | Indicadores do colaborador |
| GET | `/dashboard/admin` | admin | Indicadores da empresa |
| GET | `/treinamentos` | logado | Lista os treinamentos da empresa |
| GET | `/treinamentos/{id}` | logado | Detalha um treinamento |
| POST | `/treinamentos` | admin | Cadastra um treinamento |
| PATCH | `/treinamentos/{id}` | admin | Altera um treinamento (parcial) |
| DELETE | `/treinamentos/{id}` | admin | **Inativa** um treinamento |

### POST `/login`

Corpo:

```json
{ "cpf": "111.111.111-11", "senha": "password", "perfil": "administrador" }
```

`perfil` aceita `administrador` ou `colaborador`. Pedir `administrador` com um CPF que não é admin responde `403`.

Resposta `200`:

```json
{
  "token": "1|LqW6YZJC2whJfpx9UK1LhtYK6drG23gYZswMnowGaf9576f7",
  "identity": {
    "pessoaIdStw": 2001,
    "nome": "Ana Souza",
    "cpf": "111.111.111-11",
    "isAdmin": true,
    "empresa": { "idStw": 1001, "razaoSocial": "ACME Equipamentos de Segurança Ltda." }
  }
}
```

CPF ou senha inválidos respondem `422` com `CPF ou senha inválidos.`

### GET `/me`

Resposta `200`: `{ "identity": { ... } }`, com o mesmo objeto `identity` do login.

### POST `/logout`

Resposta `204` sem corpo. O token deixa de valer.

### GET `/dashboard/colaborador`

Resposta `200` (exemplo do Bruno Lima, com o seed de demonstração):

```json
{
  "identity": { "pessoaIdStw": 2002, "nome": "Bruno Lima", "cpf": "222.222.222-22", "isAdmin": false,
                "empresa": { "idStw": 1001, "razaoSocial": "ACME Equipamentos de Segurança Ltda." } },
  "summary": { "aFazer": 0, "emAndamento": 1, "concluidos": 0, "atrasados": 0 },
  "percentualConcluido": 0,
  "attention": [
    { "id": 4002, "titulo": "Uso correto de EPIs", "categoria": "Obrigatório", "dias": 41, "tipo": "vencimento", "status": "em_andamento" }
  ],
  "emAndamento": [ { "id": 4002, "titulo": "Uso correto de EPIs", "categoria": "Obrigatório" } ],
  "bloqueados": []
}
```

`bloqueados` lista treinamentos atribuídos cujo pré-requisito o colaborador ainda não concluiu (`{ id, titulo, preRequisito }`).

### GET `/dashboard/admin` (admin)

Resposta `200`:

```json
{
  "identity": { "...": "..." },
  "summary": { "colaboradores": 2, "treinamentosAtivos": 2, "colaboradoresPendentes": 1, "colaboradoresAtrasados": 0 },
  "percentualConcluido": 0,
  "highlights": [],
  "recentActivity": []
}
```

`highlights` traz até três destaques (`Atenção`, `Atrasado`, `Acompanhar`) e `recentActivity` as últimas conclusões (`{ descricao, dataConclusao }`).

## Treinamentos

### Objeto `treinamento` (respostas)

```json
{
  "id": 4002,
  "titulo": "Uso correto de EPIs",
  "descricao": "Reconhecimento, uso, higienização e guarda dos equipamentos de proteção.",
  "tipo": "presencial",
  "videoUrlYoutube": null,
  "categoria": "obrigatório",
  "prazoDias": 60,
  "presencaMinimaPercentual": 75,
  "duracaoVideoSegundos": null,
  "ativo": true,
  "criadoPorPessoaIdStw": 2001,
  "createdAt": "2026-10-06T02:36:32.000000Z",
  "updatedAt": "2026-10-06T02:36:32.000000Z",
  "prerequisitos": [ { "id": 4001, "titulo": "Integração de Segurança", "...": "..." } ]
}
```

Nas rotas `GET /treinamentos` e `GET /treinamentos/{id}` o campo `prerequisitos` vem preenchido (lista com no máximo um item, por enquanto). A lista é ordenada por `titulo`.

`categoria` guarda `obrigatório` ou `não obrigatório`, como descrito no DRP. O banco aceita qualquer texto, mas o front só envia esses dois valores.

### Campos aceitos em POST e PATCH

| Campo | Regra |
|---|---|
| `titulo` | obrigatório no POST; texto de até 255 caracteres |
| `tipo` | obrigatório no POST; texto (`online` ou `presencial`) |
| `categoria` | obrigatório no POST; texto |
| `descricao` | opcional; texto |
| `video_url_youtube` | opcional; URL válida |
| `prazo_dias` | opcional; inteiro maior ou igual a 0 |
| `presenca_minima_percentual` | opcional; inteiro de 0 a 100 |
| `duracao_video_segundos` | opcional; inteiro maior ou igual a 0 |
| `ativo` | opcional; booleano |
| `prerequisito_id` | opcional; id de outro treinamento, ou `null` para remover |

No **PATCH** nenhum campo é obrigatório: só o que for enviado é alterado. A empresa e o criador são definidos pelo servidor a partir do token e **não podem** ser informados no corpo.

### POST `/treinamentos` (admin)

```json
{
  "titulo": "Ergonomia no ambiente de trabalho",
  "tipo": "online",
  "categoria": "não obrigatório",
  "prazo_dias": 60,
  "prerequisito_id": 4002
}
```

Resposta `201`: `{ "message": "Treinamento cadastrado com sucesso." }`. O corpo não devolve o treinamento criado; consulte `GET /treinamentos` para obtê-lo.

### PATCH `/treinamentos/{id}` (admin)

```json
{ "titulo": "Ergonomia no trabalho", "prerequisito_id": null }
```

Resposta `200`: `{ "message": "Treinamento atualizado com sucesso." }`. Enviar `prerequisito_id: null` remove o vínculo; omitir o campo mantém o que já existe.

### DELETE `/treinamentos/{id}` (admin)

Não apaga o registro: marca `ativo = false`. Resposta `200`: `{ "message": "Treinamento inativado com sucesso." }`.

Se o treinamento for pré-requisito de outro treinamento **ativo**, responde `422` com `Este treinamento é pré-requisito de outro treinamento ativo.`

### Regras do pré-requisito

Aplicadas em `POST` e `PATCH` quando `prerequisito_id` é informado. Em caso de falha respondem `422` com o erro no campo `prerequisito_id`, e nada é gravado (a operação inteira é desfeita).

| Regra | Mensagem |
|---|---|
| O pré-requisito deve existir, estar **ativo** e ser da **mesma empresa** | `Selecione um treinamento ativo da sua empresa.` |
| Não pode formar ciclo (nem depender de si mesmo, nem indiretamente: A depende de B, B depende de C, C depende de A) | `O pré-requisito não pode depender deste treinamento.` |

Cada treinamento tem no máximo **um** pré-requisito pela API; um novo `prerequisito_id` substitui o anterior.

## Exemplo rápido com curl

```bash
# 1. login (guarde o token da resposta)
curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"cpf":"111.111.111-11","senha":"password","perfil":"administrador"}'

# 2. listar treinamentos
curl -s http://127.0.0.1:8000/api/treinamentos \
  -H "Accept: application/json" -H "Authorization: Bearer <token>"
```
