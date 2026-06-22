<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ImageProcessingException;
use App\Exceptions\InsufficientStockException;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit test untuk arsitektur error handling (BusinessException dan AppException).
 */
class ExceptionsTest extends TestCase
{
    /**
     * Uji rendering dan reporting untuk BusinessException.
     * Harus return HTTP 422 dan mencatat log dengan level INFO.
     */
    public function test_business_exception_rendering_and_logging(): void
    {
        // Mock Log facade to assert it logs at INFO level
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'INSUFFICIENT_STOCK') &&
                    $context['code'] === 'INSUFFICIENT_STOCK' &&
                    $context['context']['item_title'] === 'Fisika Dasar';
            });

        $exception = new InsufficientStockException('Fisika Dasar', 0);

        // Uji log reporting
        $exception->report();

        // Uji response rendering
        $response = $exception->render();
        $this->assertEquals(422, $response->status());

        $data = $response->getData(true);
        $this->assertTrue($data['error']);
        $this->assertEquals('business', $data['type']);
        $this->assertEquals('INSUFFICIENT_STOCK', $data['code']);
        $this->assertStringContainsString('Fisika Dasar', $data['message']);
        $this->assertEquals(0, $data['context']['available_stock']);
    }

    /**
     * Uji rendering dan reporting untuk AppException.
     * Harus return HTTP 500 dengan pesan aman, dan mencatat log dengan level ERROR.
     */
    public function test_app_exception_rendering_and_logging(): void
    {
        // Mock Log facade to assert it logs at ERROR level
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'IMAGE_PROCESSING_FAILED') &&
                    $context['code'] === 'IMAGE_PROCESSING_FAILED' &&
                    $context['debug']['filename'] === 'cover.png';
            });

        $exception = new ImageProcessingException('cover.png', 'Gagal memproses resolusi.');

        // Uji log reporting
        $exception->report();

        // Uji response rendering (harus return pesan generik)
        $response = $exception->render();
        $this->assertEquals(500, $response->status());

        $data = $response->getData(true);
        $this->assertTrue($data['error']);
        $this->assertEquals('system', $data['type']);
        $this->assertEquals('IMAGE_PROCESSING_FAILED', $data['code']);
        // Pesan aman yang ramah pengguna
        $this->assertEquals('Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.', $data['message']);
    }
}
