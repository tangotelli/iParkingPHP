<?php

namespace App\Controller;

use App\Document\User;
use App\Document\Vehicle;
use App\Service\BookingService;
use App\Service\UserService;
use App\Service\VehicleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/booking")
 */
class BookingController extends AbstractController
{
    private BookingService $bookingService;
    private VehicleService $vehicleService;
    private UserService $userService;
    private Serializer $serializer;

    public function __construct(BookingService $bookingService, VehicleService $vehicleService,
                                UserService $userService)
    {
        $this->bookingService = $bookingService;
        $this->vehicleService = $vehicleService;
        $this->userService = $userService;
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @Route(path="/new", methods={ Request::METHOD_POST })
     */
    public function bookSpot(Request $request): JsonResponse
    {
        $body = $request->getContent();
        $postData = json_decode((string) $body, true);
        $parkingId = $postData['parkingId'];
        /** @var User $user */
        $user = $this->userService->findByEmail($postData['email']);
        if (null == $user) {
            return new JsonResponse(['Status' => 'KO - No user found with that email'], 404);
        }
        /** @var Vehicle $vehicle */
        $vehicle = $this->vehicleService->findByUserAndNickname($user, $postData['vehicle']);
        if (null == $vehicle) {
            return new JsonResponse(['Status' => 'KO - No vehicle found with that nickname'], 404);
        }
        if ($this->bookingService->anySpotFree($parkingId)) {
            $booking = $this->bookingService->bookSpot($parkingId, $vehicle);

            return new JsonResponse(
                $this->serializer->serialize(
                    $booking,
                    JsonEncoder::FORMAT,
                    [AbstractNormalizer::IGNORED_ATTRIBUTES => ['start', 'end', 'parking', 'vehicle', 'spot'],
                     DateTimeNormalizer::FORMAT_KEY => 'Y-m-d H:i:s', ]));
        } else {
            return new JsonResponse(['Status' => 'KO - No free spots in the given parking'], 401);
        }
    }
}
