<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Auth\AdminSetupService;
use App\Application\Auth\LoginService;
use App\Http\Request;
use App\Http\Response;
use App\Repository\UserRepository;
use App\Security\AuthSession;
use App\Security\Csrf;

final class AuthController
{
    public function __construct(
        private LoginService $loginService,
        private AdminSetupService $setupService,
        private UserRepository $userRepository,
    ) {
    }

    public function login(): void
    {
        if (!Csrf::validate(Request::post('_csrf'))) {
            $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
            Response::redirect('/login');
        }

        $result = $this->loginService->authenticate(
            Request::post('email'),
            Request::post('password')
        );

        if ($result['ok'] !== true) {
            $_SESSION['flash_error'] = $result['error'] ?? 'E-Mail oder Passwort ist nicht korrekt.';
            $_SESSION['old'] = [
                'auth_email' => Request::post('email'),
            ];
            Response::redirect('/login');
        }

        $user = $result['user'];
        $this->userRepository->touchLastLogin((int) $user['id']);
        AuthSession::login($user);
        $_SESSION['flash_success'] = 'Willkommen zurück.';
        unset($_SESSION['old']);

        Response::redirect(AuthSession::consumeIntendedPath('/dashboard'));
    }

    public function setupInitialAdmin(): void
    {
        if (!Csrf::validate(Request::post('_csrf'))) {
            $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
            Response::redirect('/login');
        }

        $result = $this->setupService->createInitialAdmin(
            Request::post('display_name'),
            Request::post('email'),
            Request::post('password'),
            Request::post('password_confirmation')
        );

        if ($result['ok'] !== true) {
            $_SESSION['flash_error'] = $result['error'] ?? 'Der Zugang konnte nicht eingerichtet werden.';
            $_SESSION['flash_errors'] = $result['errors'] ?? [];
            $_SESSION['old'] = [
                'setup_display_name' => Request::post('display_name'),
                'setup_email' => Request::post('email'),
            ];
            Response::redirect('/login');
        }

        $user = $result['user'];
        AuthSession::login($user);
        $_SESSION['flash_success'] = 'Der erste Zugang wurde eingerichtet.';
        unset($_SESSION['old']);

        Response::redirect('/dashboard');
    }

    public function logout(): void
    {
        if (!Csrf::validate(Request::post('_csrf'))) {
            $_SESSION['flash_error'] = 'Ungültige Anfrage. Bitte erneut versuchen.';
            Response::redirect('/dashboard');
        }

        AuthSession::logout();
        $_SESSION['flash_success'] = 'Sie wurden abgemeldet.';
        Response::redirect('/login');
    }
}
