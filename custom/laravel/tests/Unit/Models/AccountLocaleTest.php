<?php

namespace Tests\Unit\Models;

use App\Enums\Locale;
use App\Models\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountLocaleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_set_locale_from_string()
    {
        $account = new Account();
        $account->name = 'Test Account';
        $account->locale = 'en';
        $account->status = 1;

        // Check the raw attribute value is integer
        $this->assertEquals(0, $account->getAttributes()['locale']);
        
        // Check the casted value is Locale enum
        $this->assertInstanceOf(Locale::class, $account->locale);
        $this->assertEquals(Locale::EN, $account->locale);
    }

    /** @test */
    public function it_can_set_locale_from_different_language_codes()
    {
        $testCases = [
            ['code' => 'en', 'expected' => 0],
            ['code' => 'fr', 'expected' => 3],
            ['code' => 'es', 'expected' => 12],
            ['code' => 'de', 'expected' => 4],
            ['code' => 'pt', 'expected' => 9],
        ];

        foreach ($testCases as $test) {
            $account = new Account();
            $account->name = 'Test Account';
            $account->locale = $test['code'];
            $account->status = 1;

            $this->assertEquals(
                $test['expected'], 
                $account->getAttributes()['locale'],
                "Failed for locale code: {$test['code']}"
            );
        }
    }

    /** @test */
    public function it_can_save_account_with_locale_string()
    {
        $account = Account::create([
            'name' => 'Test Company',
            'locale' => 'en',
            'status' => 1,
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'name' => 'Test Company',
            'locale' => 0, // Integer value in database
        ]);

        // Refresh from database
        $account->refresh();
        
        $this->assertInstanceOf(Locale::class, $account->locale);
        $this->assertEquals('en', $account->locale->getCode());
    }

    /** @test */
    public function it_provides_locale_code_accessor()
    {
        $account = new Account();
        $account->name = 'Test Account';
        $account->locale = 'es';
        
        $this->assertEquals('es', $account->locale_code);
    }

    /** @test */
    public function locale_code_accessor_returns_en_as_default()
    {
        $account = new Account();
        $account->name = 'Test Account';
        
        // Without setting locale explicitly
        $this->assertEquals('en', $account->locale_code);
    }

    /** @test */
    public function it_validates_integer_locale_values()
    {
        $account = new Account();
        $account->name = 'Test Account';
        
        // Valid integer should work
        $account->locale = 0; // EN
        $this->assertEquals(0, $account->getAttributes()['locale']);
        
        // Invalid integer should throw ValueError
        $this->expectException(\ValueError::class);
        $account->locale = 999; // Invalid locale value
    }

    /** @test */
    public function it_can_handle_locale_from_factory()
    {
        $account = Account::factory()->create([
            'locale' => 'fr',
        ]);

        $this->assertDatabaseHas('accounts', [
            'id' => $account->id,
            'locale' => 3, // French is index 3
        ]);

        $account->refresh();
        $this->assertEquals('fr', $account->locale->getCode());
    }
}
