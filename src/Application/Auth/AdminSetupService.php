<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Repository\UserRepositoryInterface;

final class AdminSetupService
{
    public function __construct(private UserRepositoryInterface $users)
    {
    }

    /** @return array{ok:bool,user?:array<string,mixed>,error?:string,errors?:array<string,string>} */
    public function createInitialAdmin(string $displayName, string $email, string $password, string $passwordConfirmation): array
    {
        if ($this->users->countUsers() > 0) {
            return [
                'ok' => false,
                'error' => 'Der erste Zugang wurde bereits eingerichtet.',
            ];
        }

        $errors = [];
        $normalizedName = trim($displayName);
        $normalizedEmail = strtolower(trim($email));

        if ($normalizedName === '') {
            $errors['display_name'] = 'Bitte einen Namen angeben.';
        }

        if ($normalizedEmail === '' || filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Bitte eine gültige E-Mail-Adresse angeben.';
        }

        if (strlen($password) < 8) {
            $errors['password'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        }

        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'Die Passwort-Bestätigung stimmt nicht überein.';
        }

        if ($errors !== []) {
            return [
                'ok' => false,
                'error' => 'Bitte prüfen Sie die markierten Eingaben.',
                'errors' => $errors,
            ];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($passwordHash) || $passwordHash === '') {
            return [
                'ok' => false,
                'error' => 'Der Zugang konnte nicht eingerichtet werden.',
            ];
        }

        $id = $this->users->createAdminUser($normalizedName, $normalizedEmail, $passwordHash);
        $user = $this->users->findById($id);
        if (!is_array($user)) {
            return [
                'ok' => false,
                'error' => 'Der Zugang konnte nicht eingerichtet werden.',
            ];
        }

        return [
            'ok' => true,
            'user' => $user,
        ];
    }
}
