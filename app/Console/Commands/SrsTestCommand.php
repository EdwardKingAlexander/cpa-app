<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SrsTestCommand extends Command
{
    protected $signature = 'srs:test';

    protected $description = 'Exercise the SRS API endpoints using the seeded student account.';

    public function handle(): int
    {
        $student = User::where('email', 'student@cpa.test')->first();

        if (! $student) {
            $this->error('Seeded student user not found. Run php artisan migrate:fresh --seed first.');

            return self::FAILURE;
        }

        $token = $student->createToken('srs-test-cli');
        $plainTextToken = $token->plainTextToken;

        try {
            $dueResponse = $this->dispatchApiRequest('GET', '/api/v1/srs/due', [], $plainTextToken);

            $this->info('GET /api/v1/srs/due');
            $this->line(json_encode($dueResponse['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if ($dueResponse['status'] >= 400) {
                $this->error("Request failed with status {$dueResponse['status']}.");

                return self::FAILURE;
            }

            $cards = is_array($dueResponse['body']) ? $dueResponse['body'] : [];

            if (empty($cards)) {
                $this->warn('No cards returned. Seeded data may be missing or already reviewed.');

                return self::SUCCESS;
            }

            $firstCard = $cards[0];

            $reviewPayload = [
                'card_id' => $firstCard['card_id'],
                'grade' => 'good',
            ];

            $reviewResponse = $this->dispatchApiRequest('POST', '/api/v1/srs/review', $reviewPayload, $plainTextToken);

            $this->info('POST /api/v1/srs/review');
            $this->line(json_encode($reviewResponse['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if ($reviewResponse['status'] >= 400) {
                $this->error("Review failed with status {$reviewResponse['status']}.");

                return self::FAILURE;
            }
        } finally {
            $token->accessToken->delete();
        }

        return self::SUCCESS;
    }

    /**
     * @return array{status:int,body:mixed}
     */
    private function dispatchApiRequest(string $method, string $uri, array $payload, string $token): array
    {
        $server = [
            'HTTP_AUTHORIZATION' => 'Bearer '.$token,
            'HTTP_ACCEPT' => 'application/json',
        ];

        $content = null;

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $server['CONTENT_TYPE'] = 'application/json';
            $content = json_encode($payload);
        }

        $request = Request::create(
            $uri,
            $method,
            $method === 'GET' ? $payload : [],
            [],
            [],
            $server,
            $content
        );

        $kernel = App::make('Illuminate\Contracts\Http\Kernel');

        $response = $kernel->handle($request);
        $kernel->terminate($request, $response);

        $decoded = json_decode($response->getContent(), true);

        return [
            'status' => $response->getStatusCode(),
            'body' => $decoded ?? $response->getContent(),
        ];
    }
}
