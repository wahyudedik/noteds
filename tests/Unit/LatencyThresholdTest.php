<?php

namespace Tests\Unit;

use Tests\TestCase;

class LatencyThresholdTest extends TestCase
{
    public function testRollingAverageCalculation()
    {
        $samples = [
            ['ts' => time() - 60, 'ms' => 600],
            ['ts' => time() - 50, 'ms' => 800],
            ['ts' => time() - 40, 'ms' => 1000],
        ];
        $sum = array_sum(array_map(fn($x) => $x['ms'], $samples));
        $avg = round($sum / count($samples), 2);
        $this->assertEquals($avg, (600 + 800 + 1000) / 3);
    }

    public function testWarningThresholdAtEightyPercent()
    {
        $critical = 1000;
        $warning = (int) round($critical * 0.8);
        $this->assertEquals(800, $warning);
        $avg = 820;
        $this->assertTrue($avg >= $warning && $avg < $critical);
    }
}
