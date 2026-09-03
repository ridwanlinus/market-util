<?php

namespace Tests\Unit;

use App\Services\CsvImportService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CsvImportServiceTest extends TestCase
{
    #[Test]
    public function it_parses_csv_with_header(): void
    {
        $csv = "date,impressions,clicks,spend\n2026-09-01,1000,50,250000\n2026-09-02,2000,80,300000";

        $rows = (new CsvImportService())->parse($csv);

        $this->assertCount(2, $rows);
        $this->assertSame('2026-09-01', $rows[0]['date']);
        $this->assertSame('1000', $rows[0]['impressions']);
        $this->assertSame('300000', $rows[1]['spend']);
    }

    #[Test]
    public function it_rejects_empty_or_header_only_csv(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new CsvImportService())->parse('');
    }

    #[Test]
    public function it_converts_numeric_values(): void
    {
        $csv = new CsvImportService();

        $this->assertSame(42.5, $csv->numeric(['a' => '42.5'], 'a'));
        $this->assertSame(42, $csv->int(['a' => '42.9'], 'a'));
        $this->assertSame(0.0, $csv->numeric(['a' => 'abc'], 'a'));
    }

    #[Test]
    public function it_normalizes_dates(): void
    {
        $csv = new CsvImportService();

        $this->assertSame('2026-09-01', $csv->date(['d' => '2026-09-01'], 'd'));
        $this->assertSame('2026-09-01', $csv->date(['d' => '2026/09/01'], 'd'));
        $this->assertNull($csv->date(['d' => ''], 'd'));
        $this->assertSame('2026-09-01', $csv->date(['d' => ''], 'd', '2026-09-01'));
    }
}