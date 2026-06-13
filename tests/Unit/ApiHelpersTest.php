<?php

namespace Ayurviro\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ApiHelpersTest extends TestCase
{
    private string $runnerPath;

    protected function setUp(): void
    {
        $this->runnerPath = __DIR__ . '/../Fixtures/test_json_response.php';
    }

    public function test_jsonResponse_outputs_valid_json(): void
    {
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' response 2>NUL');
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('key', $decoded);
        $this->assertSame('value', $decoded['key']);
    }

    public function test_jsonResponse_respects_unicode(): void
    {
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' unicode 2>NUL');
        $decoded = json_decode($output, true);

        $this->assertSame('होम', $decoded['msg']);
    }

    public function test_jsonError_outputs_error_structure(): void
    {
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' error "Not found" 404 2>NUL');
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['error']);
        $this->assertSame('Not found', $decoded['message']);
    }

    public function test_jsonError_handles_unicode(): void
    {
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' error "त्रुटि" 400 2>NUL');
        $decoded = json_decode($output, true);

        $this->assertTrue($decoded['error']);
        $this->assertSame('त्रुटि', $decoded['message']);
    }

    public function test_requireAuth_returns_user_when_authenticated(): void
    {
        $name = 'Test User';
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' auth-return 5 "' . $name . '" customer 2>NUL');
        $decoded = json_decode(trim($output), true);

        $this->assertSame(5, $decoded['user_id']);
        $this->assertSame('Test User', $decoded['user_name']);
        $this->assertSame('customer', $decoded['role']);
    }

    public function test_requireAuth_exits_when_not_authenticated(): void
    {
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' auth-exit 2>NUL');
        $decoded = json_decode($output, true);

        $this->assertTrue($decoded['error'] ?? false);
        $this->assertSame('Authentication required.', $decoded['message'] ?? '');
    }

    public function test_requireAuth_sets_correct_role_default(): void
    {
        $output = exec('php ' . escapeshellarg($this->runnerPath) . ' auth-return 10 2>NUL');
        $decoded = json_decode(trim($output), true);

        $this->assertSame('customer', $decoded['role']);
    }
}
