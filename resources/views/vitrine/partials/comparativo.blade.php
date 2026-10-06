{{--
  Tabela comparativa: transmissão avulsa × Individual × Corporativo (5/10).
  Custo por curso e quantidade de cursos vêm do catálogo real ($resumo); preços do config ($planos).
--}}
@php
    use App\Services\AssinaturaVitrineService as V;
    $avulsa = config('assinatura_vitrine.avulsa');
    $p = collect($planos)->keyBy('chave');
    $ind = $p['individual'];
    $c5 = $p['corporativo5'];
    $c10 = $p['corporativo10'];
    $cursos = number_format($resumo['cursos'], 0, ',', '.');
@endphp
<section class="vt-secao" id="comparativo" aria-labelledby="comparativo-titulo">
  <div class="vt-wrap">
    <p class="vt-eyebrow">Comparativo</p>
    <h2 id="comparativo-titulo" class="vt-h2">Avulsa ou assinatura: o que o seu órgão leva</h2>

    <div class="vt-tabela-wrap" tabindex="0" aria-label="Tabela comparativa (role para o lado no celular)">
      <table class="vt-tabela">
        <thead>
          <tr>
            <th scope="col"><span class="vt-sr">Item</span></th>
            <th scope="col" class="vt-col-off">{{ $avulsa['nome'] }}</th>
            <th scope="col">Assinatura Individual</th>
            <th scope="col" class="vt-col-on">Corporativo 5 / 10</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th scope="row">Investimento</th>
            <td class="vt-col-off">{{ V::brl($avulsa['preco']) }}<small>{{ $avulsa['unidade'] }}</small></td>
            <td>{{ V::brl($ind->preco) }}<small>por ano</small></td>
            <td class="vt-col-on">{{ V::brl($c5->preco) }} / {{ V::brl($c10->preco) }}<small>por ano</small></td>
          </tr>
          <tr>
            <th scope="row">Custo por curso</th>
            <td class="vt-col-off">{{ V::brl($avulsa['preco']) }}</td>
            <td>{{ $ind->por_curso ? V::brl($ind->por_curso) : '—' }}<small>{{ V::brl($ind->preco, 0) }} ÷ {{ $cursos }} cursos</small></td>
            <td class="vt-col-on">{{ $c5->por_curso ? V::brl($c5->por_curso) : '—' }} / {{ $c10->por_curso ? V::brl($c10->por_curso) : '—' }}<small>por servidor</small></td>
          </tr>
          <tr>
            <th scope="row">Quantidade de cursos</th>
            <td class="vt-col-off">1</td>
            <td>{{ $cursos }}</td>
            <td class="vt-col-on">{{ $cursos }}</td>
          </tr>
          <tr>
            <th scope="row">Materiais/apostilas</th>
            <td class="vt-col-off">{{ $avulsa['materiais'] }}</td>
            <td>{{ number_format($resumo['apostilas'], 0, ',', '.') }}</td>
            <td class="vt-col-on">{{ number_format($resumo['apostilas'], 0, ',', '.') }}</td>
          </tr>
          <tr>
            <th scope="row">Certificados</th>
            <td class="vt-col-off">{{ $avulsa['certificados'] }}</td>
            <td><x-vitrine.marca :ok="true" />Ilimitados</td>
            <td class="vt-col-on"><x-vitrine.marca :ok="true" />Ilimitados</td>
          </tr>
          <tr>
            <th scope="row">Acesso</th>
            <td class="vt-col-off">{{ $avulsa['acesso'] }}</td>
            <td>12 meses</td>
            <td class="vt-col-on">12 meses</td>
          </tr>
          <tr>
            <th scope="row">Novos cursos durante a vigência</th>
            <td class="vt-col-off"><x-vitrine.marca :ok="false" /></td>
            <td><x-vitrine.marca :ok="true" /></td>
            <td class="vt-col-on"><x-vitrine.marca :ok="true" /></td>
          </tr>
          <tr>
            <th scope="row">Usuários</th>
            <td class="vt-col-off">1</td>
            <td>1</td>
            <td class="vt-col-on">5 ou 10</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
