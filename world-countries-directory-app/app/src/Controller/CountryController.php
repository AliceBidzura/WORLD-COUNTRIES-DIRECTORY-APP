<?php

namespace App\Controller;

use App\Model\Country;
use App\Model\CountryScenarios;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use app\src\Model\Exception\ConflictException;
use app\src\Model\Exception\StorageException;
use app\src\Model\Exception\ValidationException;
use app\src\Model\Exception\CountryNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/country')]
class CountryController extends AbstractController
{
    private CountryScenarios $scenarios;

    public function __construct(CountryScenarios $scenarios)
    {
        $this->scenarios = $scenarios;
    }

    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $countries = $this->scenarios->getAll();
        //return $this->json($countries);

        $response = new JsonResponse($countries);
        $response->headers->set('Content-Type', 'application/json; charset=utf-8');

        return $response;
    }

    #[Route('/{code}', methods: ['GET'])]
    public function get(string $code): JsonResponse
    {
        try {
            $country = $this->scenarios->get($code);
            return $this->json($country);
        } catch (CountryNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $country = new Country(
                $data['shortName'] ?? '',
                $data['fullName'] ?? '',
                $data['isoAlpha2'] ?? '',
                $data['isoAlpha3'] ?? '',
                $data['isoNumeric'] ?? '',
                $data['population'] ?? 0,
                $data['square'] ?? 0.0
            );

            $this->scenarios->store($country);

            return $this->json($country, Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (ConflictException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (StorageException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{code}', methods: ['PUT'])]
    public function edit(string $code, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $country = new Country(
                $data['shortName'] ?? '',
                $data['fullName'] ?? '',
                $data['isoAlpha2'] ?? '',
                $data['isoAlpha3'] ?? '',
                $data['isoNumeric'] ?? '',
                $data['population'] ?? 0,
                $data['square'] ?? 0.0
            );

            $updated = $this->scenarios->edit($code, $country);
            return $this->json($updated);
        } catch (CountryNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (StorageException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{code}', methods: ['DELETE'])]
    public function delete(string $code): JsonResponse
    {
        try {
            $this->scenarios->delete($code);
            return $this->json(null, Response::HTTP_NO_CONTENT);
        } catch (CountryNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (StorageException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}