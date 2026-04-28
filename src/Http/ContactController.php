<?php

declare(strict_types=1);

namespace App\Http;

use App\Repository\ContactRepository;
use App\Security\Csrf;

final class ContactController
{
    public function __construct(private ContactRepository $repository)
    {
    }

    public function submit(): void
    {
        if (!Csrf::validate(Request::post('_csrf'))) {
            http_response_code(419);
            $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
            Response::redirect('/');
        }

        $name = Request::post('name');
        $company = Request::post('company');
        $email = Request::post('email');
        $phone = Request::post('phone');
        $message = Request::post('message');

        $errors = [];
        if ($name === '') {
            $errors[] = 'Name ist erforderlich.';
        }
        if ($company === '') {
            $errors[] = 'Unternehmen ist erforderlich.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Eine gültige E-Mail ist erforderlich.';
        }
        if ($message === '') {
            $errors[] = 'Nachricht ist erforderlich.';
        }

        if ($errors !== []) {
            $_SESSION['flash_error'] = implode(' ', $errors);
            $_SESSION['old'] = [
                'name' => $name,
                'company' => $company,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
            ];
            Response::redirect('/');
        }

        $this->repository->create($name, $company, $email, $phone, $message);
        $_SESSION['flash_success'] = 'Vielen Dank, Ihre Nachricht wurde gespeichert.';
        unset($_SESSION['old']);
        Response::redirect('/');
    }
}
