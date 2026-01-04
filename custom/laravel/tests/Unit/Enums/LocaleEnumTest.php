<?php

namespace Tests\Unit\Enums;

use App\Enums\Locale;
use Tests\TestCase;

class LocaleEnumTest extends TestCase
{
    /** @test */
    public function it_can_get_locale_code_from_enum()
    {
        $this->assertEquals('en', Locale::EN->getCode());
        $this->assertEquals('fr', Locale::FR->getCode());
        $this->assertEquals('es', Locale::ES->getCode());
        $this->assertEquals('pt_BR', Locale::PT_BR->getCode());
    }

    /** @test */
    public function it_can_create_enum_from_code()
    {
        $this->assertEquals(Locale::EN, Locale::fromCode('en'));
        $this->assertEquals(Locale::FR, Locale::fromCode('fr'));
        $this->assertEquals(Locale::ES, Locale::fromCode('es'));
        $this->assertEquals(Locale::PT_BR, Locale::fromCode('pt_br'));
    }

    /** @test */
    public function it_handles_case_insensitive_codes()
    {
        $this->assertEquals(Locale::EN, Locale::fromCode('EN'));
        $this->assertEquals(Locale::FR, Locale::fromCode('FR'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_code()
    {
        $this->expectException(\InvalidArgumentException::class);
        Locale::fromCode('invalid');
    }

    /** @test */
    public function it_has_correct_integer_values()
    {
        $this->assertEquals(0, Locale::EN->value);
        $this->assertEquals(1, Locale::AR->value);
        $this->assertEquals(3, Locale::FR->value);
        $this->assertEquals(12, Locale::ES->value);
    }

    /** @test */
    public function it_can_get_all_codes()
    {
        $codes = Locale::getCodes();
        $this->assertIsArray($codes);
        $this->assertContains('en', $codes);
        $this->assertContains('fr', $codes);
        $this->assertContains('es', $codes);
    }
}
