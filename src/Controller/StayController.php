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
use App\Util\MessageIndex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            return ControllerUtils::errorResponse(MessageIndex::USER_NOT_FOUND,
                Response::HTTP_NOT_FOUND);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleService->findByUserAndNickname($user, $requestData['vehicle']);
        if (null == $vehicle) {
            return ControllerUtils::errorResponse(MessageIndex::VEHICLE_NOT_FOUND_NICKNAME,
                Response::HTTP_NOT_FOUND);
        }
        if ($this->stayService->existsActiveStay($parkingId, $vehicle)) {
            return ControllerUtils::errorResponse(MessageIndex::STAY_ALREADY_ACTIVE,
                Response::HTTP_FORBIDDEN);
        }
        $stay = $this->findSpotAndBeginStay($user, $vehicle, $parkingId);
        $stay->setEnd($stay->getStart());
        $stay->setPrice(0.0);

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

    /**
     * @Route(path="/end/{stayId}", methods={ Request::METHOD_PUT })
     */
    public function endStay(string $stayId): JsonResponse
    {
        /** @var Stay $stay */
        $stay = $this->stayService->get($stayId);
        if (null == $stay) {
            return ControllerUtils::errorResponse(MessageIndex::STAY_NOT_FOUND_ID,
                Response::HTTP_NOT_FOUND);
        }
        $stay = $this->stayService->endStay($stay);

        return new JsonResponse($stay->jsonSerialize());
    }

    /**
     * @Route(path="/resume/{stayId}", methods={ Request::METHOD_PUT })
     */
    public function resumeStay(string $stayId) {
        /** @var Stay $stay */
        $stay = $this->stayService->get($stayId);
        if (null == $stay) {
            return ControllerUtils::errorResponse(MessageIndex::STAY_NOT_FOUND_ID,
                Response::HTTP_NOT_FOUND);
        }
        $stay = $this->stayService->resumeStay($stay);
        $stay->setEnd($stay->getStart());
        $stay->setPrice(0.0);

        return new JsonResponse($stay->jsonSerialize());
    }

    /**
     * @Route(path="/get", methods={ Request::METHOD_GET })
     */
    public function findStayByUser(Request $request)
    {
        $email = (string) $request->query->get('email');
        /** @var User $user */
        $user = $this->userService->findByEmail($email);
        if (null == $user) {
            return ControllerUtils::errorResponse(MessageIndex::USER_NOT_FOUND,
                Response::HTTP_NOT_FOUND);
        }
        $vehicles = $this->vehicleService->findByUser($user);
        if (null == $vehicles) {
            return ControllerUtils::errorResponse(MessageIndex::STAY_NOT_FOUND_USER,
                Response::HTTP_NOT_FOUND);
        }
        /** @var Stay $stay */
        $stay = $this->stayService->findActiveStayByUserVehicles($vehicles);
        if (null == $stay) {
            return ControllerUtils::errorResponse(MessageIndex::STAY_NOT_FOUND_USER,
                Response::HTTP_NOT_FOUND);
        } else {
            $stay->setEnd($stay->getStart());
            $stay->setPrice(0.0);
            return new JsonResponse($stay->jsonSerialize());
        }
    }
}
