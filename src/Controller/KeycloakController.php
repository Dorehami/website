<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class KeycloakController extends AbstractController
{
    #[Route('/connect/auth', name: 'connect_auth')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('keycloak')
            ->redirect(['openid', 'profile', 'email']);
    }

    #[Route('/connect/auth/check', name: 'connect_auth_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry): RedirectResponse
    {
        return $this->redirectToRoute('app_home');
    }
}
