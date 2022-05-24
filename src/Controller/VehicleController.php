<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Util\ControllerUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/vehicle")
 */
class VehicleController extends AbstractController
{
    private VehicleService $vehicleService;
    private UserService $userService;

    public function __construct(VehicleService $vehicleService, UserService $userService)
    {
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
    }

    /**
     * @Route(path="/register", methods={ Request::METHOD_POST })
     */
    public function register(Request $request)
    {
        $requestData = ControllerUtils::getRequestData($request);
        /** @var User $user */
        $user = $this->userService->findByEmail($requestData['email']);
        if (null == $user) {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
        $vehicle = new Vehicle();
        $vehicle->setNickname($requestData['nickname']);
        $vehicle->setLicensePlate($requestData['licensePlate']);
        $vehicle->setUser($user);
        $existingVehicle = $this->vehicleService->findByUserAndLicensePlate($user, $requestData['licensePlate']);
        if (null != $existingVehicle) {
            return new JsonResponse(['Status' => 'KO - Vehicle already registered'], 401);
        } else {
            $this->vehicleService->register($vehicle);

            return new JsonResponse($vehicle->jsonSerialize());
        }
    }

    /**
     * @Route(path="/get", methods={ Request::METHOD_GET })
     */
    public function findByUserAndNickname(Request $request): JsonResponse
    {
        $email = (string) $request->query->get('email');
        $nickname = (string) $request->query->get('nickname');
        /** @var User $user */
        $user = $this->userService->findByEmail($email);
        if (null == $user) {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleService->findByUserAndNickname($user, $nickname);
        if (null == $vehicle) {
            return new JsonResponse(['Status' => 'KO - No vehicle found with that nickname'], 404);
        } else {
            return new JsonResponse($vehicle->jsonSerialize());
        }
    }

    /**
     * @Route(path="/get/{email}", methods={ Request::METHOD_GET })
     */
    public function findByUser(string $email): JsonResponse
    {
        /** @var User $user */
        $user = $this->userService->findByEmail($email);
        if (null == $user) {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
        $vehicles = $this->vehicleService->findByUser($user);
        /** @var Vehicle $vehicle */
        $dataArray = array_map(fn ($vehicle) => [$vehicle->jsonSerialize()], $vehicles);

        return new JsonResponse($dataArray);
    }
}
