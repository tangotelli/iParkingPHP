<?php

namespace App\Controller;

use App\Document\Spot;
use App\Document\Status;
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
    private SpotService $spotService;
    private Serializer $serializer;

    public function __construct(SpotService $spotService)
    {
        $this->spotService = $spotService;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @Route(path="/book/{parkingId}", methods={ Request::METHOD_PUT })
     */
    public function bookSpot(string $parkingId): JsonResponse
    {
        if ($this->spotService->bookSpot($parkingId)) {
            return new JsonResponse(['Status' => 'OK']);
        } else {
            return new JsonResponse(['Status' => 'KO'], 401);
        }
    }
}
