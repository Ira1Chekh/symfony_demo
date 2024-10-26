<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\SecurityBundle\Security;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login-doc', name: 'api_login_doc', methods: ['POST'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                    'message' => 'missing credentials',
                ], Response::HTTP_UNAUTHORIZED);
        }
            
        $token = ''; // somehow create an API token for $user

        return $this->json([
            'user'  => $user->getUserIdentifier(),
            'token' => $token,
        ]);

    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, Security $security): JsonResponse
    {
        // Symfony will automatically handle the authentication based on `security.yaml` config.

        // If login is successful, we can get the user from the security context
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse([
                'error' => 'Invalid login credentials'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Optionally, return the user details, or a token if using JWT
        return new JsonResponse([
            'message' => 'Login successful',
            'user' => $user->getUsername(),
            // If you're using JWT, you would return the token here
        ]);
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): void
    {
        // Symfony will handle the logout, you can configure it in `security.yaml`.
        throw new \Exception('Should not be reached');
    }

}