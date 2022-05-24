<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\BookingService;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Util\ControllerUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/booking")
 */
class BookingController extends AbstractController
{
    private BookingService $bookingService;
    private VehicleService $vehicleService;
    private UserService $userService;

    public function __construct(BookingService $bookingService, VehicleService $vehicleService,
                                UserService $userService)
    {
        $this->bookingService = $bookingService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
    }

    /**
     * @Route(path="/new", methods={ Request::METHOD_POST })
     */
    public function bookSpot(Request $request): JsonResponse
    {
        $requestData = ControllerUtils::getRequestData($request);
        $parkingId = $requestData['parkingId'];
        /** @var User $user */
        $user = $this->userService->findByEmail($requestData['email']);
        if (null == $user) {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleService->findByUserAndNickname($user, $requestData['vehicle']);
        if (null == $vehicle) {
            return new JsonResponse(['Status' => 'KO - No vehicle found with that nickname'], 404);
        }
        if ($this->bookingService->anySpotFree($parkingId)) {
            $booking = $this->bookingService->bookSpot($parkingId, $vehicle);

            return new JsonResponse($booking->jsonSerialize());
        } else {
            return new JsonResponse(['Status' => 'KO - No free spots in the given parking'], 401);
        }
    }
}
