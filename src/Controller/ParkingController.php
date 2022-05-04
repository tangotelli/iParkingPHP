<?php

namespace App\Controller;

use App\Document\Location;
use App\Document\Parking;
use App\Service\ParkingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/parking")
 */
class ParkingController extends AbstractController
{
    private ParkingService $parkingService;
    private Serializer $serializer;

    public function __construct(ParkingService $parkingService)
    {
        $this->parkingService = $parkingService;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
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

        return new JsonResponse(['Status' => 'OK', 'Parking ID' => json_encode($parking->getId())]);
    }

    /**
     * @Route(path="/{parkingId}", methods={ Request::METHOD_GET })
     */
    public function get(Request $request, string $parkingId): JsonResponse
    {
        $parking = $this->parkingService->get($parkingId);
        if (null == $parking) {
            // error
        }

        return new JsonResponse($this->serializer->serialize($parking, JsonEncoder::FORMAT));
    }
}
