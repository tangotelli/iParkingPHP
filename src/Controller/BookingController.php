<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\BookingService;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Util\ControllerUtils;
use App\Util\MessageIndex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            return ControllerUtils::errorResponse(MessageIndex::USER_NOT_FOUND,
                Response::HTTP_NOT_FOUND);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleService->findByUserAndNickname($user, $requestData['vehicle']);
        if (null == $vehicle) {
            return ControllerUtils::errorResponse(MessageIndex::VEHICLE_NOT_FOUND_NICKNAME,
                Response::HTTP_NOT_FOUND);
        }
        if ($this->bookingService->existsActiveBooking($parkingId, $vehicle)) {
            return ControllerUtils::errorResponse(MessageIndex::BOOKING_ALREADY_ACTIVE,
                Response::HTTP_FORBIDDEN);
        }
        if ($this->bookingService->anySpotFree($parkingId)) {
            $booking = $this->bookingService->bookSpot($parkingId, $vehicle);

            return new JsonResponse($booking->jsonSerialize());
        } else {
            return ControllerUtils::errorResponse(MessageIndex::NO_FREE_SPOTS,
                Response::HTTP_PRECONDITION_FAILED);
        }
    }
}
