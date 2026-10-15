<p>Oi, {{ $nome }}, tudo bem?</p>

<p>
    O treinamento <strong>{{ $treinamento }}</strong> encerra no dia {{ $dataLimite }}.
    @if ($diasRestantes === 0)
        Hoje é o último dia para finalizá-lo.
    @else
        Você tem até esse dia para finalizá-lo ({{ $diasRestantes }} {{ $diasRestantes === 1 ? 'dia restante' : 'dias restantes' }}).
    @endif
</p>

<p>Acesse a plataforma de treinamentos para concluir.</p>
