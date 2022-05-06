<?php

namespace App\Service;

use App\Document\Spot;
use Doctrine\ODM\MongoDB\DocumentManager;

class SpotService
{
    private DocumentManager $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function create(Spot $spot)
    {
        $this->documentManager->persist($spot);
        $this->documentManager->flush();
    }
}