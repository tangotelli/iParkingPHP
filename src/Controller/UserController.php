<?php

namespace App\Controller;

use App\Document\User;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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
        $existingUser = $this->userService->findByEmail($postData['email']);
        if (null != $existingUser) {
            return new JsonResponse(['Status' => 'KO - User already exists'], 401);
        } else {
            $this->userService->signin($user);

            return new JsonResponse(
                $this->serializer->serialize(
                    $user,
                    JsonEncoder::FORMAT,
                    [AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']]));
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
                return new JsonResponse(
                    $this->serializer->serialize(
                        $user,
                        JsonEncoder::FORMAT,
                        [AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']]));
            } else {
                return new JsonResponse(['Status' => 'KO - Wrong credentials'], 401);
            }
        } else {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
    }
}
