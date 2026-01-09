<?php

namespace App\DataTransferObjects;

use Spatie\LaravelData\Data;

class SeedData extends Data
{
    public function __construct(
        public CompanyData $company,
        public array $users,
        public array $teams,
        public array $customRoles,
        public array $labels,
        public array $contacts,
    ) {}

    /**
     * Get default seed data configuration.
     */
    public static function getDefault(): self
    {
        return new self(
            company: new CompanyData(
                name: 'PaperLayer',
                domain: 'paperlayer.test'
            ),
            users: self::getDefaultUsers(),
            teams: self::getDefaultTeams(),
            customRoles: self::getDefaultCustomRoles(),
            labels: self::getDefaultLabels(),
            contacts: self::getDefaultContacts(),
        );
    }

    private static function getDefaultUsers(): array
    {
        return [
            [
                'name' => 'Michael Scott',
                'gender' => 'male',
                'email' => 'michael_scott@paperlayer.test',
                'teams' => ['sales', 'management', 'administration', 'warehouse'],
                'role' => 'administrator',
            ],
            [
                'name' => 'David Wallace',
                'gender' => 'male',
                'email' => 'david@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Deangelo Vickers',
                'gender' => 'male',
                'email' => 'deangelo@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Jo Bennett',
                'gender' => 'female',
                'email' => 'jo@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Josh Porter',
                'gender' => 'male',
                'email' => 'josh@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Charles Miner',
                'gender' => 'male',
                'email' => 'charles@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Ed Truck',
                'gender' => 'male',
                'email' => 'ed@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Dan Gore',
                'gender' => 'male',
                'email' => 'dan@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Craig D',
                'gender' => 'male',
                'email' => 'craig@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Jim Halpert',
                'gender' => 'male',
                'email' => 'jim@paperlayer.test',
                'teams' => ['Sales'],
                'custom_role' => 'Sales Representative',
            ],
            [
                'name' => 'Dwight Schrute',
                'gender' => 'male',
                'email' => 'dwight@paperlayer.test',
                'teams' => ['Sales'],
                'custom_role' => 'Assistant Regional Manager',
            ],
            [
                'name' => 'Pam Beesly',
                'gender' => 'female',
                'email' => 'pam@paperlayer.test',
                'teams' => ['Administration'],
                'custom_role' => 'Receptionist',
            ],
            [
                'name' => 'Stanley Hudson',
                'gender' => 'male',
                'email' => 'stanley@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Kevin Malone',
                'gender' => 'male',
                'email' => 'kevin@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Angela Martin',
                'gender' => 'female',
                'email' => 'angela@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Oscar Martinez',
                'gender' => 'male',
                'email' => 'oscar@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Phyllis Vance',
                'gender' => 'female',
                'email' => 'phyllis@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Meredith Palmer',
                'gender' => 'female',
                'email' => 'meredith@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Creed Bratton',
                'gender' => 'male',
                'email' => 'creed@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Kelly Kapoor',
                'gender' => 'female',
                'email' => 'kelly@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Ryan Howard',
                'gender' => 'male',
                'email' => 'ryan@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Toby Flenderson',
                'gender' => 'male',
                'email' => 'toby@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Andy Bernard',
                'gender' => 'male',
                'email' => 'andy@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Erin Hannon',
                'gender' => 'female',
                'email' => 'erin@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Gabe Lewis',
                'gender' => 'male',
                'email' => 'gabe@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Holly Flax',
                'gender' => 'female',
                'email' => 'holly@paperlayer.test',
                'teams' => ['Administration'],
            ],
            [
                'name' => 'Todd Packer',
                'gender' => 'male',
                'email' => 'todd@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Jan Levinson',
                'gender' => 'female',
                'email' => 'jan@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Karen Filippelli',
                'gender' => 'female',
                'email' => 'karen@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Robert California',
                'gender' => 'male',
                'email' => 'robert@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Nellie Bertram',
                'gender' => 'female',
                'email' => 'nellie@paperlayer.test',
                'teams' => ['Management'],
            ],
            [
                'name' => 'Pete Miller',
                'gender' => 'male',
                'email' => 'pete@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Clark Green',
                'gender' => 'male',
                'email' => 'clark@paperlayer.test',
                'teams' => ['Sales'],
            ],
            [
                'name' => 'Nate Nickerson',
                'gender' => 'male',
                'email' => 'nate@paperlayer.test',
                'teams' => ['Warehouse'],
            ],
            [
                'name' => 'Darryl Philbin',
                'gender' => 'male',
                'email' => 'darryl@paperlayer.test',
                'teams' => ['Warehouse'],
            ],
        ];
    }

    private static function getDefaultTeams(): array
    {
        return [
            '📈 Sales',
            '👔 Management', 
            '📋 Administration',
            '📦 Warehouse',
        ];
    }

    private static function getDefaultCustomRoles(): array
    {
        return [
            [
                'name' => 'Customer Support Lead',
                'description' => 'Lead customer support operations and manage escalations',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                    'report_manage',
                    'team_manage',
                ],
            ],
            [
                'name' => 'Sales Representative',
                'description' => 'Handle sales inquiries and manage customer relationships',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                ],
            ],
            [
                'name' => 'Technical Support Specialist',
                'description' => 'Provide technical assistance and troubleshooting',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                    'knowledge_base_manage',
                ],
            ],
            [
                'name' => 'Assistant Regional Manager',
                'description' => 'Assist in regional management and operations',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                    'report_manage',
                ],
            ],
            [
                'name' => 'Receptionist',
                'description' => 'Handle initial customer contact and routing',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                ],
            ],
            [
                'name' => 'Quality Assurance Manager',
                'description' => 'Monitor and ensure service quality standards',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                    'report_manage',
                    'audit_manage',
                ],
            ],
        ];
    }

    private static function getDefaultLabels(): array
    {
        return [
            [
                'title' => 'Bug',
                'color' => '#FF6B6B',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'Feature Request',
                'color' => '#4ECDC4',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'Priority',
                'color' => '#45B7D1',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'Sales',
                'color' => '#96CEB4',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'Support',
                'color' => '#FFEAA7',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'Billing',
                'color' => '#DDA0DD',
                'show_on_sidebar' => true,
            ],
        ];
    }

    private static function getDefaultContacts(): array
    {
        return [
            [
                'name' => 'John Customer',
                'email' => 'john@customer.com',
                'conversations' => [
                    [
                        'channel' => 'WebWidget',
                        'source_id' => 'web_001',
                        'assignee' => 'jim@paperlayer.test',
                        'priority' => 'medium',
                        'labels' => ['Support'],
                        'messages' => [
                            [
                                'content' => 'Hi, I need help with my account setup.',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'Hello! I\'d be happy to help you with your account setup. What specific issue are you experiencing?',
                                'message_type' => 'outgoing',
                                'sender' => 'jim@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'conversations' => [
                    [
                        'channel' => 'FacebookPage',
                        'source_id' => 'fb_002',
                        'assignee' => 'pam@paperlayer.test',
                        'priority' => 'high',
                        'labels' => ['Sales', 'Priority'],
                        'messages' => [
                            [
                                'content' => 'I\'m interested in your premium plan. Can you tell me more about the features?',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'Absolutely! Our premium plan includes advanced reporting, custom roles, and priority support. Would you like me to schedule a demo?',
                                'message_type' => 'outgoing',
                                'sender' => 'pam@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Mike Wilson',
                'email' => 'mike@techcorp.com',
                'conversations' => [
                    [
                        'channel' => 'Email',
                        'source_id' => 'email_003',
                        'assignee' => 'dwight@paperlayer.test',
                        'priority' => 'low',
                        'labels' => ['Bug'],
                        'messages' => [
                            [
                                'content' => 'I found a bug in the reporting dashboard. The export function is not working.',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'Thank you for reporting this issue. I\'ve escalated it to our development team and will keep you updated on the progress.',
                                'message_type' => 'outgoing',
                                'sender' => 'dwight@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Lisa Chen',
                'email' => 'lisa@startup.io',
                'conversations' => [
                    [
                        'channel' => 'TwitterProfile',
                        'source_id' => 'tw_004',
                        'assignee' => 'stanley@paperlayer.test',
                        'priority' => 'medium',
                        'labels' => ['Feature Request'],
                        'messages' => [
                            [
                                'content' => 'Would love to see integration with Slack for our team notifications!',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'Great suggestion! Slack integration is actually available in our current version. I can help you set it up.',
                                'message_type' => 'outgoing',
                                'sender' => 'stanley@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'David Rodriguez',
                'email' => 'david@agency.com',
                'conversations' => [
                    [
                        'channel' => 'Whatsapp',
                        'source_id' => 'wa_005',
                        'assignee' => 'angela@paperlayer.test',
                        'priority' => 'high',
                        'labels' => ['Billing', 'Priority'],
                        'messages' => [
                            [
                                'content' => 'I have a question about my billing. The invoice seems incorrect.',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'I\'ll review your billing details right away. Can you please provide your account ID?',
                                'message_type' => 'outgoing',
                                'sender' => 'angela@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Emma Thompson',
                'email' => 'emma@consulting.com',
                'conversations' => [
                    [
                        'channel' => 'Api',
                        'source_id' => 'api_006',
                        'assignee' => 'oscar@paperlayer.test',
                        'priority' => 'medium',
                        'labels' => ['Support'],
                        'messages' => [
                            [
                                'content' => 'How do I authenticate API requests? The documentation is unclear.',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'For API authentication, you need to include your API key in the Authorization header. I\'ll send you a detailed guide.',
                                'message_type' => 'outgoing',
                                'sender' => 'oscar@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Alex Kim',
                'email' => 'alex@ecommerce.shop',
                'conversations' => [
                    [
                        'channel' => 'Telegram',
                        'source_id' => 'tg_007',
                        'assignee' => 'phyllis@paperlayer.test',
                        'priority' => 'low',
                        'labels' => ['Sales'],
                        'messages' => [
                            [
                                'content' => 'Can I integrate this with my Shopify store?',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'Yes! We have a Shopify integration available in our premium plans. Would you like to learn more?',
                                'message_type' => 'outgoing',
                                'sender' => 'phyllis@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Rachel Green',
                'email' => 'rachel@fashion.com',
                'conversations' => [
                    [
                        'channel' => 'Line',
                        'source_id' => 'line_008',
                        'assignee' => 'kelly@paperlayer.test',
                        'priority' => 'medium',
                        'labels' => ['Support'],
                        'messages' => [
                            [
                                'content' => 'The mobile app keeps crashing when I try to view reports.',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'I\'m sorry to hear about the app crashes. Let me help you troubleshoot this issue.',
                                'message_type' => 'outgoing',
                                'sender' => 'kelly@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Tom Anderson',
                'email' => 'tom@logistics.com',
                'conversations' => [
                    [
                        'channel' => 'Voice',
                        'source_id' => 'voice_009',
                        'assignee' => 'andy@paperlayer.test',
                        'priority' => 'high',
                        'labels' => ['Priority', 'Support'],
                        'messages' => [
                            [
                                'content' => 'Voice call initiated - Customer needs urgent assistance with system outage.',
                                'message_type' => 'incoming',
                            ],
                            [
                                'content' => 'I\'m connecting you with our technical team right away. Please hold.',
                                'message_type' => 'outgoing',
                                'sender' => 'andy@paperlayer.test',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}

class CompanyData extends Data
{
    public function __construct(
        public string $name,
        public string $domain,
    ) {}
}