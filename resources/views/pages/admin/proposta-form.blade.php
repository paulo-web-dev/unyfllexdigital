@extends('layouts.admin')
@section('title', 'Gerar Proposta')
@section('section', 'Comercial')

@section('content')
<div class="page">

  <div class="page-header">
    <div>
      <h1 class="page-title">Gerar Proposta</h1>
      <p class="page-subtitle">Monte uma proposta comercial e gere o PDF para enviar ao cliente</p>
    </div>
  </div>

  <form action="{{ route('admin.proposta.gerar') }}" method="POST" target="_blank" id="formProposta">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start;">

      {{-- Coluna esquerda --}}
      <div style="display:flex;flex-direction:column;gap:18px;">

        {{-- Dados do cliente --}}
        <div class="card" style="padding:24px;">
          <h3 style="margin:0 0 16px;font-size:15px;">Dados do cliente</h3>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Nome do responsável</label>
              <input type="text" name="cliente_nome" class="filter-select" style="width:100%;height:40px;" placeholder="Ex: João da Silva">
            </div>
            <div>
              <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Órgão / Prefeitura</label>
              <input type="text" name="cliente_orgao" class="filter-select" style="width:100%;height:40px;" placeholder="Ex: Prefeitura de São Paulo">
            </div>
          </div>
        </div>

        {{-- Seleção de cursos --}}
        <div class="card" style="padding:24px;">
          <h3 style="margin:0 0 6px;font-size:15px;">Minisséries inclusas</h3>
          <p style="font-size:12px;color:var(--fg-4);margin-bottom:16px;">Selecione as minisséries que farão parte da proposta</p>

          <div style="display:flex;flex-direction:column;gap:8px;max-height:340px;overflow-y:auto;">
            @foreach($cursos as $curso)
            <label style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-1);border:1px solid var(--line-2);border-radius:10px;cursor:pointer;transition:border-color 0.2s;"
                   onmouseover="this.style.borderColor='rgba(0,163,255,0.4)'" onmouseout="this.style.borderColor='var(--line-2)'">
              <input type="checkbox" name="cursos[]" value="{{ $curso->id }}" style="width:18px;height:18px;accent-color:var(--brand-400);flex-shrink:0;">
              <div style="flex:1;min-width:0;">
                <div style="font-size:14px;color:var(--fg-1);font-weight:500;">{{ $curso->title }}</div>
                <div style="font-size:11px;color:var(--fg-4);">{{ $curso->workload }}h de conteúdo</div>
              </div>
            </label>
            @endforeach
          </div>
        </div>

        {{-- Observações --}}
        <div class="card" style="padding:24px;">
          <h3 style="margin:0 0 16px;font-size:15px;">Observações (opcional)</h3>
          <textarea name="observacoes" rows="3" class="filter-select" style="width:100%;resize:vertical;padding:12px;"
                    placeholder="Ex: Inclui emissão de nota fiscal, suporte dedicado e acesso por 12 meses."></textarea>
        </div>
      </div>

      {{-- Coluna direita — valores --}}
      <div style="display:flex;flex-direction:column;gap:18px;position:sticky;top:80px;">
        <div class="card" style="padding:24px;">
          <h3 style="margin:0 0 16px;font-size:15px;">Configuração de valores</h3>

          <div style="margin-bottom:14px;">
            <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Número de alunos *</label>
            <input type="number" name="num_alunos" value="1" min="1" required class="filter-select" style="width:100%;height:40px;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Preço cheio por aluno (De:) *</label>
            <input type="number" name="preco_cheio" value="1990" step="0.01" min="0" required class="filter-select" style="width:100%;height:40px;">
          </div>

          <div style="margin-bottom:14px;">
            <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Preço final por aluno (Por:) *</label>
            <input type="number" name="preco_final" value="998" step="0.01" min="0" required class="filter-select" style="width:100%;height:40px;">
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
            <div>
              <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Parcelas</label>
              <input type="number" name="parcelas" value="10" min="1" max="12" class="filter-select" style="width:100%;height:40px;">
            </div>
            <div>
              <label style="font-size:12px;color:var(--fg-3);display:block;margin-bottom:6px;">Validade (dias)</label>
              <input type="number" name="validade_dias" value="7" min="1" max="365" class="filter-select" style="width:100%;height:40px;">
            </div>
          </div>

          {{-- Preview em tempo real --}}
          <div style="background:var(--bg-1);border:1px solid var(--line-2);border-radius:10px;padding:16px;margin-top:8px;">
            <div style="font-size:11px;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--fg-4);margin-bottom:10px;">Resumo</div>
            <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--fg-3);margin-bottom:6px;">
              <span>Total cheio</span>
              <span id="prev-cheio" style="text-decoration:line-through;color:#ff6b6b;">R$ 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;color:#fff;margin-bottom:6px;">
              <span>Total final</span>
              <span id="prev-final" style="color:#2BD9A1;">R$ 0</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--fg-4);">
              <span>Economia</span>
              <span id="prev-economia">R$ 0 (0%)</span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:16px;">
            <i data-lucide="file-text" style="width:16px;height:16px;stroke:currentColor;fill:none;stroke-width:1.75;"></i>
            Gerar proposta em PDF
          </button>
        </div>
      </div>

    </div>
  </form>
</div>

@endsection

@push('scripts')
<script>
function fmt(v){ return 'R$ ' + (v||0).toLocaleString('pt-BR', {minimumFractionDigits:0, maximumFractionDigits:0}); }

function atualizarPreview(){
  const alunos = parseInt(document.querySelector('[name="num_alunos"]').value)  || 0;
  const cheio  = parseFloat(document.querySelector('[name="preco_cheio"]').value) || 0;
  const final  = parseFloat(document.querySelector('[name="preco_final"]').value) || 0;

  const totalCheio = cheio * alunos;
  const totalFinal = final * alunos;
  const economia   = totalCheio - totalFinal;
  const pct        = totalCheio > 0 ? Math.round((economia/totalCheio)*100) : 0;

  document.getElementById('prev-cheio').textContent    = fmt(totalCheio);
  document.getElementById('prev-final').textContent    = fmt(totalFinal);
  document.getElementById('prev-economia').textContent = `${fmt(economia)} (${pct}%)`;
}

document.querySelectorAll('[name="num_alunos"],[name="preco_cheio"],[name="preco_final"]').forEach(el => {
  el.addEventListener('input', atualizarPreview);
});

atualizarPreview();

// Validação: ao menos 1 curso
document.getElementById('formProposta').addEventListener('submit', function(e){
  const checked = document.querySelectorAll('[name="cursos[]"]:checked').length;
  if (checked === 0) {
    e.preventDefault();
    alert('Selecione ao menos uma minisérie para a proposta.');
  }
});
</script>
@endpush
