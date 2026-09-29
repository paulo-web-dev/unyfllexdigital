{!! $d['recadastro'] ? 'Recadastro' : 'Novo cadastro' !!} de {!! mb_strtolower($d['papel']) !!} — {!! $d['municipio'] !!}
@if($d['recadastro'])
(A pessoa já tinha vínculo AMAI removido; o acesso foi reativado.)
@endif

Nome: {!! $d['nome'] !!}
E-mail: {!! $d['email'] !!}
Telefone: {!! $d['telefone'] !!}
Município: {!! $d['municipio'] !!}
Perfil: {!! $d['papel'] !!}
Cadastrado por: {!! $d['cadastrado_por'] !!}
Data/hora: {!! $d['data_hora'] !!}

Mensagem automática da área de gestão AMAI. Não responda este e-mail.
