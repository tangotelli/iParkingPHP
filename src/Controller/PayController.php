<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
            return new JsonResponse(['Status' => 'KO - Payment failed'], 404);
        } else {
            return new JsonResponse(['Status' => 'OK - Payment succeeded'], 200);
        }
    }
}
