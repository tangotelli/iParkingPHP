<?php

namespace App\Controller;

use App\Document\Parking;
use App\Document\Spot;
use App\Document\Status;
use App\Service\ParkingService;
use App\Service\SpotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/spot")
 */
class SpotController extends AbstractController
{
    private ParkingService $parkingService;
    private SpotService $spotService;
    private Serializer $serializer;

    public function __construct(ParkingService $parkingService, SpotService $spotService)
    {
        $this->parkingService = $parkingService;
        $this->spotService = $spotService;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @Route(path="/new", methods={ Request::METHOD_POST })
     */
    public function create(Request $request): JsonResponse
    {
        $body = $request->getContent();
        $postData = json_decode((string) $body, true);
        /** @var Parking $parking */
        $parking = $this->parkingService->get($postData['parkingId']);
        $spot = new Spot();
        $spot->setCode($postData['spotCode']);
        $spot->setParking($parking);
        $spot->setStatus(Status::FREE());
        $this->spotService->create($spot);
        if (null != $spot->getId()) {
            return new JsonResponse(['Status' => 'OK', 'Spot ID' => json_encode($spot->getId())]);
        } else {
            return new JsonResponse(['Status' => 'KO'], 401);
        }
    }
}
