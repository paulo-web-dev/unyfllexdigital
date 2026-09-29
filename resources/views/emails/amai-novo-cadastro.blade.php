<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#eef2f7;font-family:Arial,Helvetica,sans-serif;color:#0A2540;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef2f7;padding:24px 12px;">
    <tr><td align="center">
      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;">

        <tr><td style="background:#0E2F4F;padding:20px 32px;">
          <span style="font-size:18px;font-weight:bold;color:#ffffff;">Unyflex Digital</span>
          <span style="display:block;font-size:11px;color:#9fc0e6;letter-spacing:1px;text-transform:uppercase;margin-top:2px;">Gestão AMAI — aviso de cadastro</span>
        </td></tr>

        <tr><td style="padding:28px 32px 8px;">
          <h1 style="margin:0 0 8px;font-size:20px;color:#0A2540;">
            {{ $d['recadastro'] ? 'Recadastro' : 'Novo cadastro' }} de {{ mb_strtolower($d['papel']) }} — {{ $d['municipio'] }}
          </h1>
          @if($d['recadastro'])
            <p style="margin:0 0 8px;font-size:13px;color:#7a8a9a;">A pessoa já tinha vínculo AMAI removido; o acesso foi reativado.</p>
          @endif
        </td></tr>

        <tr><td style="padding:8px 32px 28px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;line-height:1.5;">
            @foreach([
              'Nome'           => $d['nome'],
              'E-mail'         => $d['email'],
              'Telefone'       => $d['telefone'],
              'Município'      => $d['municipio'],
              'Perfil'         => $d['papel'],
              'Cadastrado por' => $d['cadastrado_por'],
              'Data/hora'      => $d['data_hora'],
            ] as $rotulo => $valor)
              <tr>
                <td style="padding:8px 12px 8px 0;border-bottom:1px solid #e2e8f0;color:#7a8a9a;white-space:nowrap;vertical-align:top;width:140px;">{{ $rotulo }}</td>
                <td style="padding:8px 0;border-bottom:1px solid #e2e8f0;color:#0A2540;font-weight:bold;">{{ $valor }}</td>
              </tr>
            @endforeach
          </table>
        </td></tr>

        <tr><td style="padding:0 32px 24px;font-size:12px;color:#7a8a9a;">
          Mensagem automática da área de gestão AMAI. Não responda este e-mail.
        </td></tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
