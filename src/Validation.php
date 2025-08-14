<?php
declare(strict_types=1);

namespace App;

final class Validation
{
    public static function requireString(array $data, string $key): string
    {
        $value = trim((string)($data[$key] ?? ''));
        if ($value === '') {
            throw new \InvalidArgumentException("El campo '{$key}' es obligatorio.");
        }
        return $value;
    }

    public static function optionalString(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        $value = trim((string)($data[$key] ?? ''));
        return $value === '' ? null : $value;
    }

    public static function nonNegativeInt(array $data, string $key, int $default = 0): int
    {
        $value = $data[$key] ?? $default;
        if (!is_numeric($value) || (int)$value < 0) {
            throw new \InvalidArgumentException("El campo '{$key}' debe ser un entero mayor o igual a 0.");
        }
        return (int)$value;
    }

    public static function optionalDate(array $data, string $key): ?string
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        $value = trim((string)($data[$key] ?? ''));
        if ($value === '') {
            return null;
        }
        $isValid = (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
        if (!$isValid) {
            throw new \InvalidArgumentException("El campo '{$key}' debe tener formato YYYY-MM-DD.");
        }
        return $value;
    }
}