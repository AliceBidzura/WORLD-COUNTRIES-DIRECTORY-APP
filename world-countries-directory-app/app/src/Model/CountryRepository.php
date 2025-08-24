<?php

namespace App\Model;
use App\Model\Country;

interface CountryRepository
{
    public function getAll(): array;
    public function get(string $code): ?Country;
    public function store(Country $country): void;
    /**может быть iso2/iso3/isoNumeric*/
    public function edit(string $code, Country $country): Country;
    public function delete(string $code): void;
}