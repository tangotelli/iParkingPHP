<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\UserService;
use App\Service\VehicleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/vehicle")
 */
class VehicleController extends AbstractController
{
    private VehicleService $vehicleService;
    private UserService $userService;
    private Serializer $serializer;

    public function __construct(VehicleService $vehicleService, UserService $userService)
    {
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @Route(path="/register", methods={ Request::METHOD_POST })
     */
    public function register(Request $request)
    {
        $vehicle = new Vehicle();
        $body = $request->getContent();
        $postData = json_decode((string) $body, true);
        /** @var User $user */
        $user = $this->userService->findByEmail($postData['email']);
        if (null == $user) {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
        $vehicle->setNickname($postData['nickname']);
        $vehicle->setLicensePlate($postData['licensePlate']);
        $vehicle->setUser($user);
        $existingVehicle = $this->vehicleService->findByUserAndLicensePlate($user, $postData['licensePlate']);
        if (null != $existingVehicle) {
            return new JsonResponse(['Status' => 'KO - Vehicle already registered'], 401);
        } else {
            $this->vehicleService->register($vehicle);

            return new JsonResponse(
                $this->serializer->serialize(
                    $vehicle,
                    JsonEncoder::FORMAT,
                    [AbstractNormalizer::IGNORED_ATTRIBUTES => ['password']]));
        }
    }
}
