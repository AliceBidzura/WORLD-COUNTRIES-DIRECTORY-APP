<?php

namespace App\Rdb;

use App\Model\Country;
use App\Model\CountryRepository;
use App\Rdb\SqlHelper;
use app\src\Model\Exception\ConflictException;
use app\src\Model\Exception\StorageException;
use app\src\Model\Exception\ValidationException;
use app\src\Model\Exception\CountryNotFoundException;

class CountryStorage implements CountryRepository
{
    private SqlHelper $sqlHelper;

    public function __construct(SqlHelper $sqlHelper)
    {
        $this->sqlHelper = $sqlHelper;
    }

    public function getAll(): array
    {
        $conn = $this->sqlHelper->openDbConnection();
        $result = $conn->query("SELECT * FROM countries");
        $countries = [];

        while ($row = $result->fetch_assoc()) {
            $countries[] = $this->mapRowToCountry($row);
        }

        $conn->close();
        return $countries;
    }

    public function get(string $code): ?Country
    {
        $conn = $this->sqlHelper->openDbConnection();

        if (preg_match('/^[A-Z]{2}$/', $code)) {
            $col = 'iso_alpha2';
        } elseif (preg_match('/^[A-Z]{3}$/', $code)) {
            $col = 'iso_alpha3';
        } elseif (preg_match('/^\d{3}$/', $code)) {
            $col = 'iso_numeric';
        } else {
            throw new CountryNotFoundException('Invalid country code');
        }

        $stmt = $conn->prepare("SELECT * FROM countries WHERE $col = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();

        $stmt->close();
        $conn->close();

        return $row ? $this->mapRowToCountry($row) : null;
    }

    public function store(Country $country): void
    {
        $conn = $this->sqlHelper->openDbConnection();
        $sql = "INSERT INTO countries
                (short_name, full_name, iso_alpha2, iso_alpha3, iso_numeric, population, square)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'sssssid',
                $country->shortName,
                $country->fullName,
                $country->isoAlpha2,
                $country->isoAlpha3,
                $country->isoNumeric,
                $country->population,
                $country->square
            );
            $stmt->execute();
            $stmt->close();
        } catch (\mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                throw new ConflictException('Duplicate code or name');
            }
            throw new StorageException('Insert failed: ' . $e->getMessage());
        } finally {
            $conn->close();
        }
    }

    public function edit(string $code, Country $country): Country
    {
        [$col, $val] = $this->detectCodeColumnAndValue($code);

        $conn = $this->sqlHelper->openDbConnection();
        $sql = "UPDATE countries
                   SET short_name = ?, full_name = ?, population = ?, square = ?
                 WHERE {$col} = ?";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ssids',
                $country->shortName,
                $country->fullName,
                $country->population,
                $country->square,
                $val
            );
            $stmt->execute();
            if ($stmt->affected_rows === 0) {
                // либо не найдено, либо значения такие же
                $stmt->close();
                $exists = $this->get($code);
                if (!$exists) {
                    $conn->close();
                    throw new CountryNotFoundException('Country not found for update');
                }
            } else {
                $stmt->close();
            }
            $conn->close();

            // данные после апдейта
            $updated = $this->get($code);
            if (!$updated) throw new CountryNotFoundException('Country vanished after update?');
            return $updated;

        } catch (\mysqli_sql_exception $e) {
            if ((int)$e->getCode() === 1062) {
                throw new ConflictException('Duplicate name');
            }
            throw new StorageException('Update failed: ' . $e->getMessage());
        }
    }

    public function delete(string $code): void
    {
        [$col, $val] = $this->detectCodeColumnAndValue($code);

        $conn = $this->sqlHelper->openDbConnection();
        $stmt = $conn->prepare("DELETE FROM countries WHERE {$col} = ?");
        $stmt->bind_param('s', $val);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            $stmt->close();
            $conn->close();
            throw new CountryNotFoundException('Country not found for delete');
        }

        $stmt->close();
        $conn->close();
    }

    private function detectCodeColumnAndValue(string $code): array
    {
        if (preg_match('/^[A-Z]{2}$/', $code)) return ['iso_alpha2', $code];
        if (preg_match('/^[A-Z]{3}$/', $code)) return ['iso_alpha3', $code];
        if (preg_match('/^\d{3}$/', $code))   return ['iso_numeric', $code];
        // на всякий:
        throw new \InvalidArgumentException('Invalid code format');
    }

    private function mapRowToCountry(array $row): Country
    {
        return new Country(
            $row['short_name'],
            $row['full_name'],
            $row['iso_alpha2'],
            $row['iso_alpha3'],
            $row['iso_numeric'],
            (int)$row['population'],
            (float)$row['square']
        );
    }
}