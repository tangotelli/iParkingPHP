<?php

namespace App\Controller;

use App\Document\Location;
use App\Document\Parking;
use App\Service\ParkingService;
use App\Service\SpotService;
use App\Util\ControllerUtils;
use App\Util\MessageIndex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parking")
 */
class ParkingController extends AbstractController
{
    private ParkingService $parkingService;
    private SpotService $spotService;

    public function __construct(ParkingService $parkingService, SpotService $spotService)
    {
        $this->parkingService = $parkingService;
        $this->spotService = $spotService;
    }

    /**
     * @Route(path="/new", methods={ Request::METHOD_POST })
     */
    public function create(Request $request): JsonResponse
    {
        $requestData = ControllerUtils::getRequestData($request);
        $parking = new Parking();
        $parking->setName($requestData['name']);
        $parking->setAddress($requestData['address']);
        $parking->setBookingFare($requestData['booking_fare']);
        $parking->setStayFare($requestData['stay_fare']);
        $location = new Location($requestData['latitude'], $requestData['longitude']);
        $parking->setLocation($location);
        $this->parkingService->create($parking);

        return new JsonResponse($parking->jsonSerialize());
    }

    /**
     * @Route(path="/id/{parkingId}", methods={ Request::METHOD_GET })
     */
    public function get(string $parkingId): JsonResponse
    {
        /** @var Parking $parking */
        $parking = $this->parkingService->get($parkingId);
        if (null == $parking) {
            return ControllerUtils::errorResponse(MessageIndex::PARKING_NOT_FOUND,
                Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($parking->jsonSerialize());
    }

    /**
     * @Route(path="/newSpot", methods={ Request::METHOD_POST })
     */
    public function createSpot(Request $request): JsonResponse
    {
        $requestData = ControllerUtils::getRequestData($request);
        if ($this->spotService->exists($requestData['spotCode'], $requestData['parkingId'])) {
            return ControllerUtils::errorResponse(MessageIndex::SPOT_ALREADY_REGISTERED,
                Response::HTTP_BAD_REQUEST);
        }
        /** @var string $spotCode */
        $spotCode = $requestData['spotCode'];
        /** @var Parking $parking */
        $parking = $this->parkingService->get($requestData['parkingId']);
        $spot = $this->spotService->create($parking, $spotCode);

        return new JsonResponse($spot->jsonSerialize());
    }

    /**
     * @Route(path="/all", methods={ Request::METHOD_GET })
     */
    public function findAll(): JsonResponse
    {
        $parkings = $this->parkingService->findAll();
        /** @var Parking $parking */
        $dataArray = array_map(fn ($parking) => [$parking->jsonSerialize()], $parkings);

        return new JsonResponse($dataArray);
    }

    /**
     * @Route(path="/occupation/{parkingId}", methods={ Request::METHOD_GET })
     */
    public function getLevelOfOccupation(string $parkingId): JsonResponse
    {
        /** @var Parking $parking */
        $parking = $this->parkingService->get($parkingId);
        if (null == $parking) {
            return ControllerUtils::errorResponse(MessageIndex::PARKING_NOT_FOUND,
                Response::HTTP_NOT_FOUND);
        }
        $freeSpots = $this->spotService->countFreeSpots($parkingId);
        $totalSpots = $this->spotService->countSpots($parkingId);
        $occupation = ($totalSpots - $freeSpots) * 100 / $totalSpots;

        return new JsonResponse(['Occupation percentage' => $occupation]);
    }
}
