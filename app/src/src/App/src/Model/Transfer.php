<?php

declare(strict_types = 1);

namespace App\Model;

use App\Abstraction\Model;

class Transfer extends Model
{
    public ?int $id = null;
    public ?int $payer = null;
    public ?int $payee = null;
    public ?string $identifier = null;
    public ?float $value = null;

    public function __construct(array|object $data = [])
    {
        $this->hydrate($data);
    }
}
