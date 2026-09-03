<?php

namespace Tests\Unit;

use App\Services\EngagementRateService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EngagementRateServiceTest extends TestCase
{
    private EngagementRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EngagementRateService();
    }

    #[Test]
    public function it_calculates_engagement_rate(): void
    {
        $result = $this->service->calculate([
            'likes' => 500,
            'comments' => 80,
            'shares' => 40,
            'saves' => 25,
        ], 12000);

        $this->assertSame(645, $result['total_interactions']);
        $this->assertSame(12000, $result['followers']);
        $this->assertEqualsWithDelta(5.38, $result['rate'], 0.01);
    }

    #[Test]
    public function it_returns_zero_rate_when_followers_is_zero(): void
    {
        $result = $this->service->calculate(['likes' => 10, 'comments' => 0, 'shares' => 0, 'saves' => 0], 0);

        $this->assertSame(0.0, $result['rate']);
        $this->assertSame('N/A', $result['grade']);
    }

    #[Test]
    public function it_assigns_grades_by_benchmark(): void
    {
        $benchmarks = ['excellent' => 5.0, 'good' => 3.0, 'average' => 1.5, 'poor' => 0.5];

        $this->assertSame('Excellent', $this->service->grade(5.1, $benchmarks));
        $this->assertSame('Good', $this->service->grade(3.5, $benchmarks));
        $this->assertSame('Average', $this->service->grade(2.0, $benchmarks));
        $this->assertSame('Below Average', $this->service->grade(0.8, $benchmarks));
        $this->assertSame('Poor', $this->service->grade(0.2, $benchmarks));
    }

    #[Test]
    public function it_handles_zero_interactions(): void
    {
        $result = $this->service->calculate(['likes' => 0, 'comments' => 0, 'shares' => 0, 'saves' => 0], 5000);

        $this->assertSame(0.0, $result['rate']);
        $this->assertSame('Poor', $result['grade']);
    }
}