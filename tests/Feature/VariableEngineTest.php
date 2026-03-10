<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\VariableEngine;
use PHPUnit\Framework\TestCase;

class VariableEngineTest extends TestCase
{
    public function testReplacesYearToken(): void
    {
        $result = VariableEngine::render('Tahun {year}');
        $this->assertSame('Tahun ' . date('Y'), $result);
    }

    public function testUnknownPlaceholderRemainsUnchanged(): void
    {
        $result = VariableEngine::render('Nilai {foo}');
        $this->assertSame('Nilai {foo}', $result);
    }

    public function testInvalidPlaceholderPatternIsIgnored(): void
    {
        $result = VariableEngine::render('X {<script>} Y');
        $this->assertSame('X {<script>} Y', $result);
    }

    public function testDateLongFormatSupportedWithFallback(): void
    {
        $result = VariableEngine::render('{date:long}');

        $this->assertNotSame('{date:long}', $result);
        $this->assertMatchesRegularExpression('/^\d{1,2}\s.+\s\d{4}$|^\d{2}-\d{2}-\d{4}$/u', $result);
    }
}
