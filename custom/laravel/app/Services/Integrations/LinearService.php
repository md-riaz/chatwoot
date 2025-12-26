<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LinearService
{
    protected string $apiUrl = 'https://api.linear.app/graphql';
    protected ?string $apiKey;

    public function __construct(?Integration $integration = null)
    {
        if ($integration && $integration->type === 'linear') {
            $this->apiKey = $integration->credentials['api_key'] ?? null;
        }
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * Get teams
     */
    public function getTeams(): array
    {
        $query = <<<'GRAPHQL'
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
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['teams']['nodes'] ?? [];
    }

    /**
     * Get projects for a team
     */
    public function getProjects(?string $teamId = null): array
    {
        $filter = $teamId ? "(filter: { team: { id: { eq: \"{$teamId}\" } } })" : '';

        $query = <<<GRAPHQL
        query {
            projects{$filter} {
                nodes {
                    id
                    name
                    description
                    state
                    team {
                        id
                        name
                    }
                }
            }
        }
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['projects']['nodes'] ?? [];
    }

    /**
     * Get issue by ID
     */
    public function getIssue(string $issueId): ?array
    {
        $query = <<<GRAPHQL
        query {
            issue(id: "{$issueId}") {
                id
                identifier
                title
                description
                url
                state {
                    id
                    name
                    type
                }
                priority
                assignee {
                    id
                    name
                    email
                }
                team {
                    id
                    name
                }
                createdAt
                updatedAt
            }
        }
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['issue'] ?? null;
    }

    /**
     * Create an issue
     */
    public function createIssue(string $teamId, string $title, ?string $description = null, array $options = []): array
    {
        $input = array_merge([
            'teamId' => $teamId,
            'title' => $title,
            'description' => $description,
        ], $options);

        $inputJson = json_encode($input);

        $mutation = <<<GRAPHQL
        mutation {
            issueCreate(input: {$inputJson}) {
                success
                issue {
                    id
                    identifier
                    title
                    url
                }
            }
        }
        GRAPHQL;

        $result = $this->query($mutation);

        if ($result['data']['issueCreate']['success'] ?? false) {
            return [
                'success' => true,
                'issue' => $result['data']['issueCreate']['issue'],
            ];
        }

        return [
            'success' => false,
            'error' => $result['errors'][0]['message'] ?? 'Failed to create issue',
        ];
    }

    /**
     * Update an issue
     */
    public function updateIssue(string $issueId, array $updates): array
    {
        $inputJson = json_encode($updates);

        $mutation = <<<GRAPHQL
        mutation {
            issueUpdate(id: "{$issueId}", input: {$inputJson}) {
                success
                issue {
                    id
                    identifier
                    title
                    url
                    state {
                        name
                    }
                }
            }
        }
        GRAPHQL;

        $result = $this->query($mutation);

        if ($result['data']['issueUpdate']['success'] ?? false) {
            return [
                'success' => true,
                'issue' => $result['data']['issueUpdate']['issue'],
            ];
        }

        return [
            'success' => false,
            'error' => $result['errors'][0]['message'] ?? 'Failed to update issue',
        ];
    }

    /**
     * Add comment to issue
     */
    public function addComment(string $issueId, string $body): array
    {
        $bodyEscaped = json_encode($body);

        $mutation = <<<GRAPHQL
        mutation {
            commentCreate(input: { issueId: "{$issueId}", body: {$bodyEscaped} }) {
                success
                comment {
                    id
                    body
                    createdAt
                }
            }
        }
        GRAPHQL;

        $result = $this->query($mutation);

        if ($result['data']['commentCreate']['success'] ?? false) {
            return [
                'success' => true,
                'comment' => $result['data']['commentCreate']['comment'],
            ];
        }

        return [
            'success' => false,
            'error' => $result['errors'][0]['message'] ?? 'Failed to add comment',
        ];
    }

    /**
     * Search issues
     */
    public function searchIssues(string $query, int $limit = 25): array
    {
        $queryEscaped = addslashes($query);

        $graphql = <<<GRAPHQL
        query {
            issueSearch(query: "{$queryEscaped}", first: {$limit}) {
                nodes {
                    id
                    identifier
                    title
                    url
                    state {
                        name
                        type
                    }
                    team {
                        name
                    }
                }
            }
        }
        GRAPHQL;

        $result = $this->query($graphql);
        return $result['data']['issueSearch']['nodes'] ?? [];
    }

    /**
     * Get workflow states for a team
     */
    public function getWorkflowStates(string $teamId): array
    {
        $query = <<<GRAPHQL
        query {
            team(id: "{$teamId}") {
                states {
                    nodes {
                        id
                        name
                        type
                        position
                    }
                }
            }
        }
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['team']['states']['nodes'] ?? [];
    }

    /**
     * Get labels for a team
     */
    public function getLabels(?string $teamId = null): array
    {
        $filter = $teamId ? "(filter: { team: { id: { eq: \"{$teamId}\" } } })" : '';

        $query = <<<GRAPHQL
        query {
            issueLabels{$filter} {
                nodes {
                    id
                    name
                    color
                }
            }
        }
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['issueLabels']['nodes'] ?? [];
    }

    /**
     * Get viewer (authenticated user)
     */
    public function getViewer(): ?array
    {
        $query = <<<'GRAPHQL'
        query {
            viewer {
                id
                name
                email
                admin
            }
        }
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['viewer'] ?? null;
    }

    /**
     * Make GraphQL query
     */
    protected function query(string $query): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'query' => $query,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Linear API request failed', ['error' => $e->getMessage()]);
            return ['errors' => [['message' => $e->getMessage()]]];
        }
    }
}
