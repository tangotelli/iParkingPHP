<?php

namespace App\Controller;

use App\Util\ControllerUtils;
use App\Util\MessageIndex;
use App\Util\RandomIntegerGenerator;
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
    private RandomIntegerGenerator $randomIntegerGenerator;
    private const ODDS_DENOMINATOR = 3;

    public function __construct(RandomIntegerGenerator $randomIntegerGenerator)
    {
        $this->randomIntegerGenerator = $randomIntegerGenerator;
    }

    /**
     * @Route(path="/pay", methods={ Request::METHOD_POST })
     */
    public function pay(Request $request): JsonResponse
    {
        $random = $this->randomIntegerGenerator->generate();
        if (0 == $random % self::ODDS_DENOMINATOR) {
            return ControllerUtils::errorResponse(MessageIndex::PAYMENT_FAILED,
                Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            return new JsonResponse(['Message' => MessageIndex::PAYMENT_COMPLETED], Response::HTTP_OK);
        }
    }
}
