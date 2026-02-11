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

    private static function getDefaultTeams(): array
    {
        return [
            '💰 Sales',
            '💼 Management',
            '👩‍💼 Administration',
            '🚛 Warehouse',
        ];
    }

    private static function getDefaultCustomRoles(): array
    {
        return [
            [
                'name' => 'Customer Support Lead',
                'description' => 'Lead support agent with full conversation and contact management',
                'permissions' => [
                    'conversation_manage',
                    'contact_manage',
                    'report_manage',
                ],
            ],
            [
                'name' => 'Sales Representative',
                'description' => 'Sales team member with conversation and contact access',
                'permissions' => [
                    'conversation_unassigned_manage',
                    'conversation_participating_manage',
                    'contact_manage',
                ],
            ],
            [
                'name' => 'Knowledge Manager',
                'description' => 'Manages knowledge base and participates in conversations',
                'permissions' => [
                    'knowledge_base_manage',
                    'conversation_participating_manage',
                ],
            ],
            [
                'name' => 'Junior Agent',
                'description' => 'Entry-level agent with basic conversation access',
                'permissions' => [
                    'conversation_participating_manage',
                ],
            ],
            [
                'name' => 'Analytics Specialist',
                'description' => 'Focused on reports and data analysis',
                'permissions' => [
                    'report_manage',
                    'conversation_participating_manage',
                ],
            ],
            [
                'name' => 'Escalation Handler',
                'description' => 'Handles unassigned conversations and escalations',
                'permissions' => [
                    'conversation_unassigned_manage',
                    'conversation_participating_manage',
                    'contact_manage',
                ],
            ],
        ];
    }

    private static function getDefaultUsers(): array
    {
        return [
            [
                'name' => 'Michael Scott',
                'gender' => 'male',
                'email' => 'michael_scott@paperlayer.test',
                'teams' => ['Sales', 'Management', 'Administration', 'Warehouse'],
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
                'name' => 'Karen Filippelli',
                'gender' => 'female',
                'email' => 'karn@paperlayer.test',
                'teams' => ['Sales'],
                'custom_role' => 'Sales Representative',
            ],
            [
                'name' => 'Danny Cordray',
                'gender' => 'male',
                'email' => 'danny@paperlayer.test',
                'teams' => ['Sales'],
                'custom_role' => 'Customer Support Lead',
            ],
            [
                'name' => 'Ben Nugent',
                'gender' => 'male',
                'email' => 'ben@paperlayer.test',
                'teams' => ['Sales'],
                'custom_role' => 'Junior Agent',
            ],
            [
                'name' => 'Cathy Simms',
                'gender' => 'female',
                'email' => 'cathy@paperlayer.test',
                'teams' => ['Administration'],
                'custom_role' => 'Knowledge Manager',
            ],
            [
                'name' => 'Hunter Jo',
                'gender' => 'male',
                'email' => 'hunter@paperlayer.test',
                'teams' => ['Administration'],
                'custom_role' => 'Analytics Specialist',
            ],
            [
                'name' => 'Stephanie Wilson',
                'gender' => 'female',
                'email' => 'stephanie@paperlayer.test',
                'teams' => ['Administration'],
                'custom_role' => 'Escalation Handler',
            ],
            [
                'name' => 'Lonny Collins',
                'gender' => 'female',
                'email' => 'lonny@paperlayer.test',
                'teams' => ['Warehouse'],
                'custom_role' => 'Customer Support Lead',
            ],
            [
                'name' => 'Madge Madsen',
                'gender' => 'female',
                'email' => 'madge@paperlayer.test',
                'teams' => ['Warehouse'],
            ],
        ];
    }

    private static function getDefaultLabels(): array
    {
        return [
            [
                'title' => 'billing',
                'color' => '#28AD21',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'software',
                'color' => '#8F6EF2',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'delivery',
                'color' => '#A2FDD5',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'ops-handover',
                'color' => '#A53326',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'premium-customer',
                'color' => '#6FD4EF',
                'show_on_sidebar' => true,
            ],
            [
                'title' => 'lead',
                'color' => '#F161C8',
                'show_on_sidebar' => true,
            ],
        ];
    }

    private static function getDefaultContacts(): array
    {
        return [
            [
                'name' => 'Lorrie Trosdall',
                'email' => 'ltrosdall0@bravesites.test',
                'conversations' => [[
                    'channel' => 'WebWidget',
                    'source_id' => 'web_001',
                    'assignee' => 'michael_scott@paperlayer.test',
                    'priority' => 'medium',
                    'labels' => ['software'],
                    'messages' => [
                        [
                            'content' => "Hi, I'm having trouble logging in to my account.",
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'Hi! Sorry to hear that. Can you please provide me with your username and email address so I can look into it for you?',
                            'message_type' => 'outgoing',
                            'sender' => 'michael_scott@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Tiffanie Cloughton',
                'email' => 'tcloughton1@newyorker.test',
                'conversations' => [[
                    'channel' => 'FacebookPage',
                    'source_id' => 'fb_002',
                    'assignee' => 'karn@paperlayer.test',
                    'priority' => 'high',
                    'labels' => ['billing', 'premium-customer'],
                    'messages' => [
                        [
                            'content' => 'Hi, I need some help with my billing statement.',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => "Hello! I'd be happy to assist you with that. Can you please tell me which billing statement you're referring to?",
                            'message_type' => 'outgoing',
                            'sender' => 'michael_scott@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Mikel Kipple',
                'email' => 'mkipple2@marriott.test',
                'conversations' => [[
                    'channel' => 'TwitterProfile',
                    'source_id' => 'tw_003',
                    'assignee' => 'danny@paperlayer.test',
                    'priority' => 'low',
                    'labels' => ['lead'],
                    'messages' => [
                        [
                            'content' => 'I have a question about your enterprise plan pricing.',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'Happy to help. I can walk you through enterprise pricing and available onboarding support.',
                            'message_type' => 'outgoing',
                            'sender' => 'danny@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Shae Wallis',
                'email' => 'swallis3@washingtonpost.test',
                'conversations' => [[
                    'channel' => 'Whatsapp',
                    'source_id' => 'wa_004',
                    'assignee' => 'stephanie@paperlayer.test',
                    'priority' => 'urgent',
                    'labels' => ['ops-handover'],
                    'messages' => [
                        [
                            'content' => 'Our team needs urgent help with handover before shift change.',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => "Thanks for flagging this. I'm escalating immediately and sharing a clear handover checklist.",
                            'message_type' => 'outgoing',
                            'sender' => 'stephanie@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Renie Champ',
                'email' => 'rchamp4@stackoverflow.test',
                'conversations' => [[
                    'channel' => 'Sms',
                    'source_id' => 'sms_005',
                    'assignee' => 'ben@paperlayer.test',
                    'priority' => 'medium',
                    'labels' => ['delivery'],
                    'messages' => [
                        [
                            'content' => 'Our delivery updates are delayed in dashboard.',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'I checked and found the sync lag. We have started a fix and I will keep you posted.',
                            'message_type' => 'outgoing',
                            'sender' => 'ben@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Louise Farny',
                'email' => 'lfarny5@developer.test',
                'conversations' => [[
                    'channel' => 'Email',
                    'source_id' => 'email_006',
                    'assignee' => 'cathy@paperlayer.test',
                    'priority' => 'low',
                    'labels' => ['software'],
                    'messages' => [
                        [
                            'content' => 'Can you share best practices for knowledge base structure?',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'Absolutely. I have shared a starter taxonomy and publishing workflow you can adopt.',
                            'message_type' => 'outgoing',
                            'sender' => 'cathy@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Jaye Geldert',
                'email' => 'jgeldert6@airbnb.test',
                'conversations' => [[
                    'channel' => 'Api',
                    'source_id' => 'api_007',
                    'assignee' => 'hunter@paperlayer.test',
                    'priority' => 'medium',
                    'labels' => ['lead'],
                    'messages' => [
                        [
                            'content' => 'Which endpoint should I use for conversation assignment analytics?',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'Use the reports endpoints for assignment analytics; I sent example query parameters.',
                            'message_type' => 'outgoing',
                            'sender' => 'hunter@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Inna McIlory',
                'email' => 'imcilory7@spotify.test',
                'conversations' => [[
                    'channel' => 'Telegram',
                    'source_id' => 'tg_008',
                    'assignee' => 'lonny@paperlayer.test',
                    'priority' => 'high',
                    'labels' => ['premium-customer'],
                    'messages' => [
                        [
                            'content' => 'We need white-glove migration support for premium workspace setup.',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'Thank you for reaching out. I am coordinating your premium onboarding right now.',
                            'message_type' => 'outgoing',
                            'sender' => 'lonny@paperlayer.test',
                        ],
                    ],
                ]],
            ],
            [
                'name' => 'Micaela Fosey',
                'email' => 'mfosey8@figma.test',
                'conversations' => [[
                    'channel' => 'Line',
                    'source_id' => 'line_009',
                    'assignee' => 'madge@paperlayer.test',
                    'priority' => 'high',
                    'labels' => ['delivery', 'ops-handover'],
                    'messages' => [
                        [
                            'content' => 'Can you help us coordinate shipment handover in local timezone?',
                            'message_type' => 'incoming',
                        ],
                        [
                            'content' => 'Yes. I have added a step-by-step handover timeline and assigned an operations owner.',
                            'message_type' => 'outgoing',
                            'sender' => 'madge@paperlayer.test',
                        ],
                    ],
                ]],
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
