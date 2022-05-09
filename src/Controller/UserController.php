<?php

namespace App\Controller;

use App\Document\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route(path="/signin", methods={ Request::METHOD_POST })
     */
    public function signin(Request $request): JsonResponse
    {
        $user = new User();
        $body = $request->getContent();
        $postData = json_decode((string) $body, true);
        $user->setEmail($postData['email']);
        $user->setName($postData['name']);
        $user->setPassword($postData['password']);
        $existingUser = $this->userService->findByEmail($postData['email']);
        if (null != $existingUser) {
            return new JsonResponse(['Status' => 'KO - User already exists'], 401);
        } else {
            $this->userService->signin($user);

            return new JsonResponse($user->jsonSerialize());
        }
    }

    /**
     * @Route(path="/login", methods={ Request::METHOD_GET })
     */
    public function login(Request $request): JsonResponse
    {
        $email = (string) $request->query->get('email');
        $password = (string) $request->query->get('password');
        /** @var User $user */
        $user = $this->userService->findByEmail($email);
        if (null != $user) {
            if ($this->userService->login($user, $password)) {
                return new JsonResponse($user->jsonSerialize());
            } else {
                return new JsonResponse(['Status' => 'KO - Wrong credentials'], 401);
            }
        } else {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
    }
}
