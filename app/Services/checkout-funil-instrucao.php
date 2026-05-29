<?php

/*
|--------------------------------------------------------------------------
| INSTRUÇÃO — no CheckoutService.php
|
| 1. Adicione o use no topo:
|    use App\Services\FunnelService;
|
| 2. No método processar(), ANTES do DB::transaction, adicione:
|--------------------------------------------------------------------------
*/

// Registra etapa "pagamento" no funil
FunnelService::registrar('pagamento', $dto->classesId);

/*
|--------------------------------------------------------------------------
| 3. No método processar(), DENTRO do DB::transaction,
|    após o Enrollment::create(), adicione:
|--------------------------------------------------------------------------
*/

// Registra etapa "converteu" no funil quando cartão aprovado
if ($statusMatricula === 'checked') {
    FunnelService::registrar('converteu', $dto->classesId);
}

/*
|--------------------------------------------------------------------------
| 4. No CheckoutController.php, no método status() (polling PIX/Boleto),
|    após confirmar o pagamento, adicione:
|--------------------------------------------------------------------------
*/

// Dentro do if ($enrollment && $enrollment->status !== 'checked'):
FunnelService::registrar('converteu', $enrollment->classes_id);
