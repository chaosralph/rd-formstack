<?php

declare(strict_types=1);

namespace App\Application\Contact;

use App\Repository\ContactRepository;

final class SubmitContactService
{
    public function __construct(private ContactRepository $repository)
    {
    }

    /**
     * @param array{name:string,company:string,email:string,phone:string,message:string} $payload
     */
    public function handle(array $payload): void
    {
        $this->repository->create(
            $payload['name'],
            $payload['company'],
            $payload['email'],
            $payload['phone'],
            $payload['message']
        );
    }
}
