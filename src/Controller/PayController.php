<?php

namespace App\Controller;

use App\Util\ControllerUtils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/payment")
 */
class PayController extends AbstractController
{
    private const ODDS_DENOMINATOR = 3;

    /**
     * @Route(path="/pay", methods={ Request::METHOD_POST })
     */
    public function pay(Request $request): JsonResponse
    {
        $random = random_int(1, 10);
        if (0 == $random % self::ODDS_DENOMINATOR) {
            return ControllerUtils::errorResponse('Payment failed',
                Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            return new JsonResponse(['Message' => 'Payment succeeded'], 200);
        }
    }
}
