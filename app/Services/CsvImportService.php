<?php

namespace App\Services;

class CsvImportService
{
    /**
     * Parse konten CSV menjadi array baris asosiatif.
     *
     * @return array<int, array<string, string>>
     */
    public function parse(string $content): array
    {
        $content = trim($content);

        if ($content === '') {
            throw new \InvalidArgumentException('File CSV kosong.');
        }

        $rows = str_getcsv($content, "\n");
        $rows = array_filter(array_map('trim', $rows), fn ($r) => $r !== '');

        if (count($rows) < 2) {
            throw new \InvalidArgumentException('CSV harus memiliki header dan minimal satu baris data.');
        }

        $header = str_getcsv(array_shift($rows));
        $header = array_map(fn ($h) => strtolower(trim(str_replace(["\xEF\xBB\xBF", '"'], '', $h))), $header);

        $out = [];
        foreach ($rows as $row) {
            $cols = str_getcsv($row);
            $assoc = [];
            foreach ($header as $i => $name) {
                $assoc[$name] = trim($cols[$i] ?? '');
            }
            $out[] = $assoc;
        }

        return $out;
    }

    public function numeric(array $row, string $key, float $default = 0): float
    {
        $value = $row[$key] ?? '';

        return is_numeric($value) ? (float) $value : $default;
    }

    public function int(array $row, string $key, int $default = 0): int
    {
        return (int) $this->numeric($row, $key, $default);
    }

    public function date(array $row, string $key, ?string $fallback = null): ?string
    {
        $value = trim($row[$key] ?? '');

        if ($value === '') {
            return $fallback;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return $fallback;
        }
    }
}