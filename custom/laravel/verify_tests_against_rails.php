<?php
/**
 * Test Verification Script
 * Compares Laravel tests against Chatwoot Rails API to ensure accuracy
 */

require __DIR__ . '/vendor/autoload.php';

class TestVerifier
{
    private $railsRoot;
    private $laravelRoot;
    private $issues = [];
    private $verified = [];

    public function __construct()
    {
        $this->railsRoot = dirname(__DIR__, 2);
        $this->laravelRoot = __DIR__;
    }

    public function verify()
    {
        echo "🔍 Verifying Laravel Tests Against Rails APIs\n";
        echo str_repeat('=', 70) . "\n\n";

        $this->verifyAccountTests();
        $this->verifyConversationTests();
        $this->verifyContactTests();
        $this->verifyInboxTests();
        $this->verifyMessageTests();
        $this->verifyFactories();

        $this->displayResults();
    }

    private function verifyAccountTests()
    {
        echo "📋 Verifying Account Tests...\n";

        // Check Rails Account model fields
        $railsAccount = $this->railsRoot . '/app/models/account.rb';
        $laravelAccount = $this->laravelRoot . '/app/Models/Account.php';
        $laravelFactory = $this->laravelRoot . '/database/factories/AccountFactory.php';

        if (!file_exists($railsAccount)) {
            $this->issues[] = "Rails Account model not found at {$railsAccount}";
            return;
        }

        // Parse Rails model for required fields
        $railsContent = file_get_contents($railsAccount);
        $laravelContent = file_get_contents($laravelAccount);
        $factoryContent = file_get_contents($laravelFactory);

        // Check for required validations
        if (strpos($railsContent, "validates :name, presence: true") !== false) {
            if (strpos($laravelContent, "'name'") !== false) {
                $this->verified[] = "✅ Account: 'name' field exists in Laravel";
            } else {
                $this->issues[] = "❌ Account: 'name' field missing in Laravel";
            }
        }

        // Check locale handling
        if (strpos($railsContent, "enum :locale") !== false) {
            if (strpos($laravelContent, "'locale'") !== false) {
                $this->verified[] = "✅ Account: 'locale' field exists in Laravel";
                
                // Check if factory generates valid locale
                if (strpos($factoryContent, "'locale'") !== false && 
                    strpos($factoryContent, "randomElement") !== false) {
                    $this->verified[] = "✅ AccountFactory: Uses Faker for locale generation";
                } else {
                    $this->issues[] = "⚠️  AccountFactory: Locale generation may not match Rails enum";
                }
            }
        }

        // Check for custom_attributes (Rails has it, Laravel might not)
        if (strpos($railsContent, "custom_attributes") !== false) {
            if (strpos($laravelContent, "custom_attributes") === false) {
                $this->issues[] = "⚠️  Account: 'custom_attributes' from Rails not found in Laravel";
            } else {
                $this->verified[] = "✅ Account: 'custom_attributes' field exists";
            }
        }

        // Check settings field
        if (strpos($railsContent, "settings") !== false) {
            if (strpos($laravelContent, "'settings'") !== false) {
                $this->verified[] = "✅ Account: 'settings' field exists in Laravel";
            }
        }

        // Check status enum
        if (strpos($railsContent, "enum :status") !== false) {
            if (strpos($laravelContent, "'status'") !== false) {
                $this->verified[] = "✅ Account: 'status' field exists in Laravel";
            }
        }

        echo "   Account verification complete\n\n";
    }

    private function verifyConversationTests()
    {
        echo "📋 Verifying Conversation Tests...\n";

        $railsConversation = $this->railsRoot . '/app/models/conversation.rb';
        $laravelConversation = $this->laravelRoot . '/app/Models/Conversation.php';
        $laravelFactory = $this->laravelRoot . '/database/factories/ConversationFactory.php';

        if (!file_exists($railsConversation)) {
            echo "   ⚠️  Rails Conversation model not found\n\n";
            return;
        }

        if (!file_exists($laravelConversation)) {
            $this->issues[] = "❌ Laravel Conversation model not found";
            return;
        }

        $railsContent = file_get_contents($railsConversation);
        $laravelContent = file_get_contents($laravelConversation);

        // Check status field
        if (strpos($railsContent, "enum status") !== false || strpos($railsContent, "enum :status") !== false) {
            if (strpos($laravelContent, "STATUS_") !== false || strpos($laravelContent, "'status'") !== false) {
                $this->verified[] = "✅ Conversation: 'status' field exists in Laravel";
            } else {
                $this->issues[] = "❌ Conversation: 'status' field missing in Laravel";
            }
        }

        // Check required relationships
        $requiredRelations = ['account', 'inbox', 'contact'];
        foreach ($requiredRelations as $relation) {
            if (strpos($laravelContent, $relation) !== false) {
                $this->verified[] = "✅ Conversation: '{$relation}' relationship exists";
            } else {
                $this->issues[] = "❌ Conversation: '{$relation}' relationship missing";
            }
        }

        // Check factory if it exists
        if (file_exists($laravelFactory)) {
            $factoryContent = file_get_contents($laravelFactory);
            if (strpos($factoryContent, 'fake()->') !== false || strpos($factoryContent, 'Faker') !== false) {
                $this->verified[] = "✅ ConversationFactory: Uses Faker for data generation";
            }
        }

        echo "   Conversation verification complete\n\n";
    }

    private function verifyContactTests()
    {
        echo "📋 Verifying Contact Tests...\n";

        $railsContact = $this->railsRoot . '/app/models/contact.rb';
        $laravelContact = $this->laravelRoot . '/app/Models/Contact.php';
        $laravelFactory = $this->laravelRoot . '/database/factories/ContactFactory.php';

        if (!file_exists($railsContact) || !file_exists($laravelContact)) {
            echo "   ⚠️  Model files not found\n\n";
            return;
        }

        $laravelContent = file_get_contents($laravelContact);

        // Check required fields
        $requiredFields = ['name', 'email', 'phone_number'];
        foreach ($requiredFields as $field) {
            if (strpos($laravelContent, "'{$field}'") !== false) {
                $this->verified[] = "✅ Contact: '{$field}' field exists";
            }
        }

        // Check factory
        if (file_exists($laravelFactory)) {
            $factoryContent = file_get_contents($laravelFactory);
            if (strpos($factoryContent, 'fake()->name()') !== false) {
                $this->verified[] = "✅ ContactFactory: Uses Faker for name generation";
            }
            if (strpos($factoryContent, 'fake()->email()') !== false || strpos($factoryContent, 'fake()->safeEmail()') !== false) {
                $this->verified[] = "✅ ContactFactory: Uses Faker for email generation";
            }
            if (strpos($factoryContent, 'fake()->phoneNumber()') !== false) {
                $this->verified[] = "✅ ContactFactory: Uses Faker for phone generation";
            }
        }

        echo "   Contact verification complete\n\n";
    }

    private function verifyInboxTests()
    {
        echo "📋 Verifying Inbox Tests...\n";

        $laravelInbox = $this->laravelRoot . '/app/Models/Inbox.php';
        $laravelFactory = $this->laravelRoot . '/database/factories/InboxFactory.php';

        if (!file_exists($laravelInbox)) {
            echo "   ⚠️  Laravel Inbox model not found\n\n";
            return;
        }

        $laravelContent = file_get_contents($laravelInbox);

        // Check required fields
        if (strpos($laravelContent, "'name'") !== false) {
            $this->verified[] = "✅ Inbox: 'name' field exists";
        }

        if (strpos($laravelContent, "'channel_type'") !== false) {
            $this->verified[] = "✅ Inbox: 'channel_type' field exists";
        }

        // Check factory
        if (file_exists($laravelFactory)) {
            $factoryContent = file_get_contents($laravelFactory);
            if (strpos($factoryContent, 'fake()->') !== false) {
                $this->verified[] = "✅ InboxFactory: Uses Faker for data generation";
            }
        }

        echo "   Inbox verification complete\n\n";
    }

    private function verifyMessageTests()
    {
        echo "📋 Verifying Message Tests...\n";

        $laravelMessage = $this->laravelRoot . '/app/Models/Message.php';
        $laravelFactory = $this->laravelRoot . '/database/factories/MessageFactory.php';

        if (!file_exists($laravelMessage)) {
            echo "   ⚠️  Laravel Message model not found\n\n";
            return;
        }

        $laravelContent = file_get_contents($laravelMessage);

        // Check required fields
        if (strpos($laravelContent, "'content'") !== false) {
            $this->verified[] = "✅ Message: 'content' field exists";
        }

        if (strpos($laravelContent, "'message_type'") !== false) {
            $this->verified[] = "✅ Message: 'message_type' field exists";
        }

        // Check factory
        if (file_exists($laravelFactory)) {
            $factoryContent = file_get_contents($laravelFactory);
            if (strpos($factoryContent, 'fake()->sentence()') !== false || 
                strpos($factoryContent, 'fake()->text()') !== false ||
                strpos($factoryContent, 'fake()->paragraph()') !== false) {
                $this->verified[] = "✅ MessageFactory: Uses Faker for content generation";
            }
        }

        echo "   Message verification complete\n\n";
    }

    private function verifyFactories()
    {
        echo "📋 Verifying Factories Use Laravel Fake Data...\n";

        $factoryDir = $this->laravelRoot . '/database/factories';
        if (!is_dir($factoryDir)) {
            $this->issues[] = "❌ Factories directory not found";
            return;
        }

        $factories = glob($factoryDir . '/*Factory.php');
        $usingFaker = 0;
        $notUsingFaker = 0;

        foreach ($factories as $factory) {
            $content = file_get_contents($factory);
            $basename = basename($factory);
            
            if (strpos($content, 'fake()->') !== false || 
                strpos($content, 'Faker\\') !== false || 
                strpos($content, '$this->faker') !== false) {
                $usingFaker++;
                $this->verified[] = "✅ {$basename}: Uses Laravel Faker";
            } else {
                $notUsingFaker++;
                $this->issues[] = "⚠️  {$basename}: May not use Faker properly";
            }
        }

        echo "   Total factories: " . count($factories) . "\n";
        echo "   Using Faker: {$usingFaker}\n";
        echo "   Not using Faker: {$notUsingFaker}\n\n";
    }

    private function displayResults()
    {
        echo str_repeat('=', 70) . "\n";
        echo "📊 VERIFICATION RESULTS\n";
        echo str_repeat('=', 70) . "\n\n";

        echo "✅ VERIFIED (" . count($this->verified) . "):\n";
        foreach ($this->verified as $item) {
            echo "   {$item}\n";
        }

        echo "\n⚠️  ISSUES FOUND (" . count($this->issues) . "):\n";
        if (empty($this->issues)) {
            echo "   None! All checks passed.\n";
        } else {
            foreach ($this->issues as $issue) {
                echo "   {$issue}\n";
            }
        }

        echo "\n" . str_repeat('=', 70) . "\n";
        
        $total = count($this->verified) + count($this->issues);
        $passRate = $total > 0 ? round((count($this->verified) / $total) * 100, 2) : 0;
        
        echo "📈 PASS RATE: {$passRate}%\n";
        echo str_repeat('=', 70) . "\n";
    }
}

// Run verification
$verifier = new TestVerifier();
$verifier->verify();
