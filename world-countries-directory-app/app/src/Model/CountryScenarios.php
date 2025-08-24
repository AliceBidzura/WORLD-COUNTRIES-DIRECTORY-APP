<?php

namespace App\Model;

use app\src\Model\Exception\CountryNotFoundException;
use app\src\Model\Exception\ValidationException;

class CountryScenarios
{
    private CountryRepository $repository;

    public function __construct(CountryRepository $repository)
    {
        $this->repository = $repository;
    }
    
    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function get(string $code): Country
    {
        $country = $this->repository->get($code);
        if (!$country) {
            throw new CountryNotFoundException("Country not found");
        }
        return $country;
    }

    public function store(Country $country): void
    {
        $this->assertValidCountry($country);
        $this->repository->store($country);
    }

    public function edit(string $code, Country $country): Country
    {
        $this->assertValidCode($code);
        $existing = $this->repository->get($code); 
        // Коды в payload менять нельзя — если переданы и отличаются, это 400
        if (!empty($country->isoAlpha2) && strtoupper($country->isoAlpha2) !== $existing->isoAlpha2) {
            throw new ValidationException('Codes are immutable');
        }
        if (!empty($country->isoAlpha3) && strtoupper($country->isoAlpha3) !== $existing->isoAlpha3) {
            throw new ValidationException('Codes are immutable');
        }
        if (!empty($country->isoNumeric) && $country->isoNumeric !== $existing->isoNumeric) {
            throw new ValidationException('Codes are immutable');
        }
        $this->assertValidNamesPopArea($country->shortName, $country->fullName, $country->population, $country->square);

        return $this->repository->edit($code, $country);
    }

    public function delete(string $code): void
    {
        $this->assertValidCode($code);
        $this->repository->delete($code);
    }

    private function assertValidCode(string $code): void
    {
        $code = trim($code);
        if (preg_match('/^[A-Z]{2}$/', $code)) return;         // iso2
        if (preg_match('/^[A-Z]{3}$/', $code)) return;         // iso3
        if (preg_match('/^\d{3}$/', $code)) return;            // numeric
        throw new ValidationException('Invalid code format');
    }

    private function assertValidCountry(Country $c): void
    {
        if (!preg_match('/^[A-Z]{2}$/', $c->isoAlpha2)) throw new ValidationException('isoAlpha2 must be 2 uppercase letters');
        if (!preg_match('/^[A-Z]{3}$/', $c->isoAlpha3)) throw new ValidationException('isoAlpha3 must be 3 uppercase letters');
        if (!preg_match('/^\d{3}$/', $c->isoNumeric)) throw new ValidationException('isoNumeric must be 3 digits');

        $this->assertValidNamesPopArea($c->shortName, $c->fullName, $c->population, $c->square);
    }

    private function assertValidNamesPopArea(string $short, string $full, int $pop, float $sq): void
    {
        if (trim($short) === '' || trim($full) === '') {
            throw new ValidationException('Names must be non-empty');
        }
        if ($pop < 0) throw new ValidationException('Population must be >= 0');
        if ($sq < 0) throw new ValidationException('Square must be >= 0');
    }
}