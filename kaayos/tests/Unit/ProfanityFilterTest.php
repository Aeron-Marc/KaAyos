<?php

namespace Tests\Unit;

use App\Services\ProfanityFilter;
use Tests\TestCase;

class ProfanityFilterTest extends TestCase
{
    public function testFiltersEnglishProfanity()
    {
        $filter = new ProfanityFilter();

        $this->assertStringContainsString('***', $filter->filter('This is fucking terrible'));
        $this->assertStringContainsString('***', $filter->filter('You are a bastard'));
        $this->assertTrue($filter->containsProfanity('shit happens'));
    }

    public function testFiltersFilipinoProfanity()
    {
        $filter = new ProfanityFilter();

        $this->assertStringContainsString('***', $filter->filter('Putang ina mo'));
        $this->assertStringContainsString('***', $filter->filter('Gago ka'));
        $this->assertTrue($filter->containsProfanity('Tangina'));
    }

    public function testHandlesObfuscation()
    {
        $filter = new ProfanityFilter();

        // l33t speak: f@ck → fuck
        $this->assertTrue($filter->containsProfanity('f@ck you'));
        // 5 for s: $hit → shit
        $this->assertTrue($filter->containsProfanity('$hit happens'));
        // Repeated letters: foooool → fool
        $this->assertTrue($filter->containsProfanity('foooool'));
    }

    public function testAllowsCleanText()
    {
        $filter = new ProfanityFilter();

        $clean = 'This is a perfectly fine message with no issues at all.';
        $this->assertSame($clean, $filter->filter($clean));
        $this->assertFalse($filter->containsProfanity($clean));
    }

    public function testMasksProfanityByDefault()
    {
        $filter = new ProfanityFilter();

        $result = $filter->filter('This is fucking awful');
        $this->assertStringNotContainsString('fuck', $result);
        $this->assertStringContainsString('***', $result);
    }

    public function testHandlesEmptyAndNull()
    {
        $filter = new ProfanityFilter();

        $this->assertSame('', $filter->filter(''));
        $this->assertFalse($filter->containsProfanity(''));
    }
}
