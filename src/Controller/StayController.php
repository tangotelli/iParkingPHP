<?php

namespace App\Controller;

use App\Document\Booking;
use App\Document\Status;
use App\Document\Stay;
use App\Document\User;
use App\Document\Vehicle;
use App\Service\BookingService;
use App\Service\StayService;
use App\Service\UserService;
use App\Service\VehicleService;
use App\Util\ControllerUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stay")
 */
class StayController extends AbstractController
{
    private StayService $stayService;
    private VehicleService $vehicleService;
    private UserService $userService;
    private BookingService $bookingService;

    public function __construct(StayService $stayService, VehicleService $vehicleService,
                                UserService $userService, BookingService $bookingService)
    {
        $this->stayService = $stayService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->bookingService = $bookingService;
    }

    /**
     * @Route(path="/new", methods={ Request::METHOD_POST })
     */
    public function beginStay(Request $request)
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
        if ($this->stayService->existsCurrentStay($parkingId, $vehicle)) {
            return new JsonResponse(['Status' => 'KO - You already have an active stay in this parking'], 401);
        }
        $stay = $this->findSpotAndBeginStay($user, $vehicle, $parkingId);

        return new JsonResponse($stay->jsonSerialize());
    }

    private function findSpotAndBeginStay(User $user, Vehicle $vehicle, string $parkingId): Stay
    {
        /** @var Booking $booking */
        $booking = $this->bookingService->findActiveBooking($user, $parkingId);
        if (null != $booking) {
            $spot = $booking->getSpot();
            if ($spot->getStatus() == Status::BOOKED()) {
                return $this->stayService->beginStayFromBooking($spot, $vehicle);
            }
        }
        return $this->stayService->beginStay($parkingId, $vehicle);
    }
}
