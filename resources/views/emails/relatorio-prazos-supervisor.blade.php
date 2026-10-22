<p>Oi{{ $supervisorNome ? ', '.$supervisorNome : '' }}, tudo bem?</p>

<p>Os colaboradores abaixo têm treinamentos não concluídos perto do prazo:</p>

<table cellpadding="6" border="1" style="border-collapse: collapse;">
    <thead>
        <tr>
            <th>Colaborador</th>
            <th>Treinamento</th>
            <th>Prazo</th>
            <th>Dias restantes</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($itens as $item)
            <tr>
                <td>{{ $item['colaborador'] }}</td>
                <td>{{ $item['treinamento'] }}</td>
                <td>{{ $item['dataLimite'] }}</td>
                <td>{{ $item['diasRestantes'] }}</td>
                <td>{{ $item['status'] === 'em_andamento' ? 'Em andamento' : 'Não iniciado' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
