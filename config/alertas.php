<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Alertas de vencimento de treinamento
    |--------------------------------------------------------------------------
    |
    | O comando treinamentos:alertar-vencimento roda uma vez por dia no horário
    | abaixo. Cada colaborador recebe um e-mail por treinamento não concluído
    | cujo prazo vence nos próximos "dias_antecedencia" dias (todo dia, até o
    | vencimento). O supervisor recebe um relatório com os mesmos itens.
    |
    */

    'dias_antecedencia' => (int) env('ALERTAS_DIAS_ANTECEDENCIA', 3),

    'horario' => env('ALERTAS_HORARIO', '08:00'),

    'timezone' => env('ALERTAS_TIMEZONE', 'America/Sao_Paulo'),

    'notificar_supervisor' => (bool) env('ALERTAS_NOTIFICAR_SUPERVISOR', true),

];
