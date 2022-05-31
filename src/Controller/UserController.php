<?php

namespace App\Controller;

use App\Document\User;
use App\Service\UserService;
use App\Util\ControllerUtils;
use App\Util\MessageIndex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $requestData = ControllerUtils::getRequestData($request);
        $user = new User();
        $user->setEmail($requestData['email']);
        $user->setName($requestData['name']);
        $user->setPassword($requestData['password']);
        $existingUser = $this->userService->findByEmail($requestData['email']);
        if (null != $existingUser) {
            return ControllerUtils::errorResponse(MessageIndex::USER_ALREADY_REGISTERED,
                Response::HTTP_BAD_REQUEST);
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
                return ControllerUtils::errorResponse(MessageIndex::WRONG_CREDENTIALS,
                    Response::HTTP_UNAUTHORIZED);
            }
        } else {
            return ControllerUtils::errorResponse(MessageIndex::WRONG_CREDENTIALS,
                Response::HTTP_UNAUTHORIZED);
        }
    }
}
