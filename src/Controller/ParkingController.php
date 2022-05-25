<?php

namespace App\Controller;

use App\Document\Location;
use App\Document\Parking;
use App\Service\ParkingService;
use App\Service\SpotService;
use App\Util\ControllerUtils;
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
        if (null != $parking->getId()) {
            return new JsonResponse($parking->jsonSerialize());
        } else {
            return ControllerUtils::errorResponse('Parking could not be created',
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route(path="/id/{parkingId}", methods={ Request::METHOD_GET })
     */
    public function get(string $parkingId): JsonResponse
    {
        /** @var Parking $parking */
        $parking = $this->parkingService->get($parkingId);
        if (null == $parking) {
            return ControllerUtils::errorResponse('No parking found with that Id',
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
            return ControllerUtils::errorResponse('No parking found with that Id',
                Response::HTTP_NOT_FOUND);
        }
        /** @var string $spotCode */
        $spotCode = $requestData['spotCode'];
        /** @var Parking $parking */
        $parking = $this->parkingService->get($requestData['parkingId']);
        $spot = $this->spotService->create($parking, $spotCode);
        if (null != $spot->getId()) {
            return new JsonResponse($spot->jsonSerialize());
        } else {
            return ControllerUtils::errorResponse('Spot could not be created',
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
}
