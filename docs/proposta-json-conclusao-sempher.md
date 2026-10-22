# Proposta de JSON — conclusão de treinamento

**Rascunho para alinhamento com a Sempher.** O backend já armazena pessoa, atribuição, treinamento, conclusão, aptidão e CAs; ainda não existe endpoint de conclusão nem envio externo.

```json
{
  "atribuicao_id": 6002,
  "pessoa_id_stw": 2003,
  "treinamento": {
    "id_plataforma": 4001,
    "codigo_sempher": null,
    "titulo": "NR-35 - Trabalho em Altura"
  },
  "conclusao": {
    "status": "concluido",
    "data_conclusao": "2026-09-25T17:30:00-03:00",
    "apto": true
  },
  "cas": [
    { "produto_id_stw": 3001 }
  ]
}
```

Os IDs e dados são exemplos. `id_plataforma` é o ID local; `codigo_sempher` está nulo até a definição do identificador aceito pelo STW.

## Alinhamento pendente

**Qual identificador a Sempher espera para localizar o treinamento: o ID local, um código cadastrado no STW ou outro valor?** Se for um código externo, precisamos confirmar de onde obtê-lo.

Também falta confirmar se a Sempher precisa receber `atribuicao_id`, `apto` e a lista de CAs. Este JSON é uma proposta, não um contrato aprovado nem uma integração implementada.

