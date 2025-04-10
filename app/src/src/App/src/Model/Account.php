<?php

declare(strict_types = 1);

namespace App\Model;

use App\Abstraction\Model;

class Account extends Model
{
    public ?int $id = null;
    public ?int $role_id = null;
    public ?string $full_name = null;
    public ?string $document = null;
    public ?string $documentType = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $created = null;

    public function __construct(array|object $data = [])
    {
        $this->hydrate($data);
    }
}
