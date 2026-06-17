{{-- Campos reutilizados nos formularios do topo e do final --}}

{{-- Honeypot anti-spam (invisivel para humanos) --}}
<input type="text" name="website" class="lp-hp" tabindex="-1" autocomplete="off" aria-hidden="true">

{{-- UTMs capturadas da URL do anuncio --}}
<input type="hidden" name="utm_source"   value="{{ $utm['utm_source'] ?? '' }}">
<input type="hidden" name="utm_medium"   value="{{ $utm['utm_medium'] ?? '' }}">
<input type="hidden" name="utm_campaign" value="{{ $utm['utm_campaign'] ?? '' }}">
<input type="hidden" name="utm_content"  value="{{ $utm['utm_content'] ?? '' }}">
<input type="hidden" name="utm_term"     value="{{ $utm['utm_term'] ?? '' }}">

<div class="lp-field">
  <label>Nome completo</label>
  <input type="text" name="nome" value="{{ old('nome') }}" placeholder="Seu nome" required>
  @error('nome') <span class="lp-err">{{ $message }}</span> @enderror
</div>

<div class="lp-field">
  <label>E-mail</label>
  <input type="email" name="email" value="{{ old('email') }}" placeholder="seu@email.com.br" required>
  @error('email') <span class="lp-err">{{ $message }}</span> @enderror
</div>

<div class="lp-field">
  <label>WhatsApp</label>
  <input type="tel" name="whatsapp" class="js-whats" value="{{ old('whatsapp') }}" placeholder="(11) 99999-9999" required>
  @error('whatsapp') <span class="lp-err">{{ $message }}</span> @enderror
</div>

<div class="row g-2">
  <div class="col-6">
    <div class="lp-field">
      <label>Cidade</label>
      <input type="text" name="cidade" value="{{ old('cidade') }}" placeholder="Sua cidade" required>
      @error('cidade') <span class="lp-err">{{ $message }}</span> @enderror
    </div>
  </div>
  <div class="col-6">
    <div class="lp-field">
      <label>Cargo</label>
      <input type="text" name="cargo" value="{{ old('cargo') }}" placeholder="Ex.: Pregoeiro" required>
      @error('cargo') <span class="lp-err">{{ $message }}</span> @enderror
    </div>
  </div>
</div>
