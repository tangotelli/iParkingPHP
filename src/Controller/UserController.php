<?php

namespace App\Controller;

use App\Document\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private UserService $userService;
    private Serializer $serializer;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
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
        $this->userService->signin($user);
        if (null != $user->getId()) {
            return new JsonResponse(['Status' => 'OK']);
        } else {
            return new JsonResponse(['Status' => 'KO'], 401);
        }
    }

    /**
     * @Route(path="/login", methods={ Request::METHOD_GET })
     */
    public function login(Request $request)
    {
        $email = (string) $request->query->get('email');
        $password = (string) $request->query->get('password');
        if ($this->userService->login($email, $password)) {
            return new JsonResponse(['Status' => 'OK']);
        } else {
            return new JsonResponse(['Status' => 'KO'], 401);
        }
    }
}
