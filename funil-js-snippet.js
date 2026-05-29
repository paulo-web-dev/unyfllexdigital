/**
 * ══════════════════════════════════════════════════════════════════════════
 * INSTRUÇÃO — adicione esta função no seu arquivo JS global (layouts/site.blade.php)
 * ou em cada página relevante.
 *
 * Ela registra eventos de "carrinho" e "visualizou" via AJAX no funil.
 * ══════════════════════════════════════════════════════════════════════════
 */

/**
 * Registra um evento no funil de conversão.
 * @param {string} etapa   - 'carrinho' | 'visualizou' | 'pagamento'
 * @param {number|null} classesId - ID da minisérie (quando aplicável)
 */
function registrarFunil(etapa, classesId = null) {
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    fetch('/funil/registrar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ etapa, classes_id: classesId }),
    }).catch(() => {}); // silencioso — nunca quebra o fluxo
}

/**
 * ══════════════════════════════════════════════════════════════════════════
 * ONDE CHAMAR:
 * ══════════════════════════════════════════════════════════════════════════
 *
 * 1. AO ADICIONAR AO CARRINHO (home.blade.php e cursos.blade.php):
 *    No listener do btn-add-to-cart, após UnyCart.addItem():
 *
 *    const result = UnyCart.addItem(item);
 *    if (result.added) {
 *        registrarFunil('carrinho', parseInt(item.id));  // ← ADICIONAR
 *        setInCart(this);
 *        showCartToast(item.title);
 *    }
 *
 * 2. NA PÁGINA DE DETALHE DO CURSO (curso.show):
 *    No script da página, ao carregar:
 *
 *    document.addEventListener('DOMContentLoaded', function() {
 *        registrarFunil('visualizou', {{ $curso->id }});  // ← ADICIONAR
 *    });
 *
 * 3. AO INICIAR PAGAMENTO (checkout.blade.php):
 *    No início da função finalizarPedido(), após validar carrinho:
 *
 *    registrarFunil('pagamento', parseInt(classesId));  // ← ADICIONAR
 *
 * ══════════════════════════════════════════════════════════════════════════
 * ROTA — adicionar no web.php (FORA do grupo admin, sem autenticação):
 * ══════════════════════════════════════════════════════════════════════════
 *
 * Route::post('/funil/registrar', [FunnelController::class, 'registrar'])
 *     ->name('funil.registrar');
 *
 * ══════════════════════════════════════════════════════════════════════════
 * KERNEL — adicionar TrackFunnel no array $middleware (global):
 * ══════════════════════════════════════════════════════════════════════════
 *
 * \App\Http\Middleware\TrackFunnel::class,
 *
 * ══════════════════════════════════════════════════════════════════════════
 * ENCRYPT COOKIES — adicionar _unyflex_sid nas exceções:
 * ══════════════════════════════════════════════════════════════════════════
 *
 * protected $except = [
 *     'referral',
 *     '_unyflex_sid',   // ← ADICIONAR
 * ];
 */
