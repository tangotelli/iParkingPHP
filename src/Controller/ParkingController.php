<?php

namespace App\Controller;

use App\Document\Location;
use App\Document\Parking;
use App\Repository\ParkingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/parking")
 */
class ParkingController extends AbstractController
{
    private ParkingRepository $parkingRepository;

    public function __construct(ParkingRepository $parkingRepository)
    {
        $this->parkingRepository = $parkingRepository;
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
        $this->parkingRepository->create($parking);

        return new JsonResponse(['Status' => 'OK', 'Parking ID' => json_encode($parking->getId())]);
    }
}
