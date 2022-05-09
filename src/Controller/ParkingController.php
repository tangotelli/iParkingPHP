<?php

namespace App\Controller;

use App\Document\Location;
use App\Document\Parking;
use App\Service\ParkingService;
use App\Service\SpotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $parking = new Parking();
        $body = $request->getContent();
        $postData = json_decode((string) $body, true);
        $parking->setName($postData['name']);
        $parking->setAddress($postData['address']);
        $parking->setBookingFare($postData['booking_fare']);
        $parking->setStayFare($postData['stay_fare']);
        $location = new Location($postData['latitude'], $postData['longitude']);
        $parking->setLocation($location);
        $this->parkingService->create($parking);
        if (null != $parking->getId()) {
            return new JsonResponse($parking->jsonSerialize());
        } else {
            return new JsonResponse(['Status' => 'KO'], 401);
        }
    }

    /**
     * @Route(path="/{parkingId}", methods={ Request::METHOD_GET })
     */
    public function get(string $parkingId): JsonResponse
    {
        /** @var Parking $parking */
        $parking = $this->parkingService->get($parkingId);
        if (null == $parking) {
            return new JsonResponse(['Status' => 'KO - No parking found with that Id'], 404);
        }

        return new JsonResponse($parking->jsonSerialize());
    }

    /**
     * @Route(path="/newSpot", methods={ Request::METHOD_POST })
     */
    public function createSpot(Request $request): JsonResponse
    {
        $body = $request->getContent();
        $postData = json_decode((string) $body, true);
        if ($this->spotService->exists($postData['spotCode'], $postData['parkingId'])) {
            return new JsonResponse(['Status' => 'KO - No parking found with that Id'], 404);
        }
        /** @var string $spotCode */
        $spotCode = $postData['spotCode'];
        /** @var Parking $parking */
        $parking = $this->parkingService->get($postData['parkingId']);
        $spot = $this->spotService->create($parking, $spotCode);
        if (null != $spot->getId()) {
            return new JsonResponse($spot->jsonSerialize());
        } else {
            return new JsonResponse(['Status' => 'KO'], 401);
        }
    }
}
