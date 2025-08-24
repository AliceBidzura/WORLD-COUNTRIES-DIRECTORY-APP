<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class StatusController extends AbstractController
{
    #[Route('/api', name: 'api_status', methods: ['GET'])]
    public function status(Request $request): JsonResponse
    {
        return $this->json([
            'status' => 'server is running',
            'host' => $request->getHost(),
            'protocol' => $request->getScheme(),
        ]);
    }

    #[Route('/api/ping', name: 'api_ping', methods: ['GET'])]
    public function ping(): JsonResponse
    {
        return $this->json([
            'status' => 'pong'
        ]);
    }
}
