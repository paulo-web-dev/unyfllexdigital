<?php
// app/Events/PagamentoAprovado.php

namespace App\Events;

use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagamentoAprovado
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Enrollment $enrollment,
        public readonly ?Student   $student,
        public readonly array      $paymentData,
    ) {}
}
