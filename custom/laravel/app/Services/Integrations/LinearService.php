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
                }
            }
        }
        GRAPHQL;

        $result = $this->query($query);
        return $result['data']['teams']['nodes'] ?? [];
    }

    /**
     * Create an issue
     */
    public function createIssue(string $teamId, string $title, ?string $description = null): array
    {
        $input = [
            'teamId' => $teamId,
            'title' => $title,
            'description' => $description,
        ];

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
     * Search issues
     */
    public function searchIssues(string $query): array
    {
        $queryEscaped = addslashes($query);

        $graphql = <<<GRAPHQL
        query {
            issueSearch(query: "{$queryEscaped}") {
                nodes {
                    id
                    identifier
                    title
                    url
                }
            }
        }
        GRAPHQL;

        $result = $this->query($graphql);
        return $result['data']['issueSearch']['nodes'] ?? [];
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
