<?php

namespace App\Model;

class Country
{
    public string $shortName;   // короткое наименование (например, "Россия")
    public string $fullName;    // полное наименование (например, "Российская Федерация")
    public string $isoAlpha2;   // двухбуквенный код (RU)
    public string $isoAlpha3;   // трёхбуквенный код (RUS)
    public string $isoNumeric;  // числовой код в виде строки ("643")
    public int $population;     // население (например, 146150789)
    public float $square;       // площадь в кв. км. (например, 17125191)

    public function __construct(
        string $shortName,
        string $fullName,
        string $isoAlpha2,
        string $isoAlpha3,
        string $isoNumeric,
        int $population,
        float $square
    ) {
        $this->shortName = $shortName;
        $this->fullName = $fullName;
        $this->isoAlpha2 = $isoAlpha2;
        $this->isoAlpha3 = $isoAlpha3;
        $this->isoNumeric = $isoNumeric;
        $this->population = $population;
        $this->square = $square;
    }
}