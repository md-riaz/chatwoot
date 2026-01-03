<?php

namespace App\Services\Integrations\Linear;

use App\Models\Account;
use App\Models\Integration\Hook;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinearService
{
    private const API_URL = 'https://api.linear.app/graphql';

    public function __construct(
        private Account $account
    ) {}

    public function getTeams(): array
    {
        $query = '
            query {
                teams {
                    nodes {
                        id
                        name
                        key
                        description
                    }
                }
            }
        ';

        $response = $this->makeGraphQLRequest($query);
        
        if (isset($response['error'])) {
            return $response;
        }

        return [
            'data' => $response['data']['teams']['nodes'] ?? []
        ];
    }

    public function getTeamEntities(string $teamId): array
    {
        $query = '
            query($teamId: String!) {
                team(id: $teamId) {
                    members {
                        nodes {
                            id
                            name
                            email
                        }
                    }
                    projects {
                        nodes {
                            id
                            name
                            description
                        }
                    }
                    states {
                        nodes {
                            id
                            name
                            type
                        }
                    }
                    labels {
                        nodes {
                            id
                            name
                            color
                        }
                    }
                }
            }
        ';

        $response = $this->makeGraphQLRequest($query, ['teamId' => $teamId]);
        
        if (isset($response['error'])) {
            return $response;
        }

        $team = $response['data']['team'] ?? [];
        
        return [
            'data' => [
                'users' => $team['members']['nodes'] ?? [],
                'projects' => $team['projects']['nodes'] ?? [],
                'states' => $team['states']['nodes'] ?? [],
                'labels' => $team['labels']['nodes'] ?? [],
            ]
        ];
    }

    public function createIssue(array $params): array
    {
        $query = '
            mutation($title: String!, $description: String, $teamId: String!, $assigneeId: String, $priority: Int, $stateId: String, $labelIds: [String!]) {
                issueCreate(input: {
                    title: $title
                    description: $description
                    teamId: $teamId
                    assigneeId: $assigneeId
                    priority: $priority
                    stateId: $stateId
                    labelIds: $labelIds
                }) {
                    success
                    issue {
                        id
                        title
                        identifier
                        url
                    }
                }
            }
        ';

        $variables = [
            'title' => $params['title'],
            'description' => $params['description'] ?? null,
            'teamId' => $params['team_id'],
            'assigneeId' => $params['assignee_id'] ?? null,
            'priority' => isset($params['priority']) ? (int) $params['priority'] : null,
            'stateId' => $params['state_id'] ?? null,
            'labelIds' => $params['label_ids'] ?? [],
        ];

        $response = $this->makeGraphQLRequest($query, $variables);
        
        if (isset($response['error'])) {
            return $response;
        }

        $issue = $response['data']['issueCreate']['issue'] ?? null;
        
        if (!$issue) {
            return ['error' => 'Failed to create issue'];
        }

        return [
            'data' => [
                'id' => $issue['id'],
                'title' => $issue['title'],
                'identifier' => $issue['identifier'],
                'url' => $issue['url'],
            ]
        ];
    }

    public function linkIssue(string $url, string $issueId, string $title): array
    {
        $query = '
            mutation($url: String!, $issueId: String!, $title: String!) {
                attachmentLinkURL(url: $url, issueId: $issueId, title: $title) {
                    success
                    attachment {
                        id
                        title
                        url
                    }
                }
            }
        ';

        $response = $this->makeGraphQLRequest($query, [
            'url' => $url,
            'issueId' => $issueId,
            'title' => $title,
        ]);
        
        if (isset($response['error'])) {
            return $response;
        }

        $attachment = $response['data']['attachmentLinkURL']['attachment'] ?? null;
        
        if (!$attachment) {
            return ['error' => 'Failed to link issue'];
        }

        return [
            'data' => [
                'id' => $issueId,
                'link' => $url,
                'link_id' => $attachment['id'],
            ]
        ];
    }

    public function unlinkIssue(string $linkId): array
    {
        $query = '
            mutation($id: String!) {
                attachmentDelete(id: $id) {
                    success
                }
            }
        ';

        $response = $this->makeGraphQLRequest($query, ['id' => $linkId]);
        
        if (isset($response['error'])) {
            return $response;
        }

        return [
            'data' => ['link_id' => $linkId]
        ];
    }

    public function searchIssues(string $term): array
    {
        $query = '
            query($term: String!) {
                searchIssues(query: $term) {
                    nodes {
                        id
                        title
                        identifier
                        url
                        state {
                            name
                        }
                        assignee {
                            name
                        }
                    }
                }
            }
        ';

        $response = $this->makeGraphQLRequest($query, ['term' => $term]);
        
        if (isset($response['error'])) {
            return $response;
        }

        return [
            'data' => $response['data']['searchIssues']['nodes'] ?? []
        ];
    }

    public function getLinkedIssues(string $url): array
    {
        $query = '
            query($url: String!) {
                attachmentsForURL(url: $url) {
                    nodes {
                        id
                        title
                        url
                        issue {
                            id
                            title
                            identifier
                        }
                    }
                }
            }
        ';

        $response = $this->makeGraphQLRequest($query, ['url' => $url]);
        
        if (isset($response['error'])) {
            return $response;
        }

        return [
            'data' => $response['data']['attachmentsForURL']['nodes'] ?? []
        ];
    }

    private function makeGraphQLRequest(string $query, array $variables = []): array
    {
        $hook = $this->getLinearHook();
        
        if (!$hook) {
            return ['error' => 'Linear integration not configured'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$hook->access_token}",
                'Content-Type' => 'application/json',
            ])->post(self::API_URL, [
                'query' => $query,
                'variables' => $variables,
            ]);

            if (!$response->successful()) {
                throw new \Exception("Linear API error: {$response->status()} - {$response->body()}");
            }

            $data = $response->json();
            
            if (isset($data['errors'])) {
                throw new \Exception('Linear GraphQL errors: ' . json_encode($data['errors']));
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Linear API error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    private function getLinearHook(): ?Hook
    {
        return $this->account->integrationHooks()
            ->where('app_id', 'linear')
            ->where('status', Hook::STATUS_ENABLED)
            ->first();
    }
}