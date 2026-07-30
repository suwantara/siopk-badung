<?php

namespace Tests\Unit;

use App\Enums\AiProvider;
use App\Enums\StatusVerifikasi;
use App\Enums\UserRole;
use Tests\TestCase;

class EnumsTest extends TestCase
{
    public function test_status_verifikasi_has_six_values(): void
    {
        $cases = StatusVerifikasi::cases();
        $this->assertCount(6, $cases);
    }

    public function test_status_verifikasi_values(): void
    {
        $this->assertEquals('menunggu', StatusVerifikasi::Menunggu->value);
        $this->assertEquals('ai_review', StatusVerifikasi::AiReview->value);
        $this->assertEquals('review_dinas', StatusVerifikasi::ReviewDinas->value);
        $this->assertEquals('disetujui', StatusVerifikasi::Disetujui->value);
        $this->assertEquals('ditolak', StatusVerifikasi::Ditolak->value);
        $this->assertEquals('duplikat', StatusVerifikasi::Duplikat->value);
    }

    public function test_ai_provider_values(): void
    {
        $this->assertEquals('claude', AiProvider::Claude->value);
        $this->assertEquals('openai', AiProvider::OpenAI->value);
        $this->assertEquals('deepseek', AiProvider::DeepSeek->value);
        $this->assertEquals('groq', AiProvider::Groq->value);
        $this->assertEquals('custom', AiProvider::Custom->value);
    }

    public function test_user_role_values(): void
    {
        $this->assertEquals('superadmin', UserRole::Superadmin->value);
        $this->assertEquals('admin', UserRole::Admin->value);
        $this->assertEquals('verifikator', UserRole::Verifikator->value);
        $this->assertEquals('petugas', UserRole::Petugas->value);
    }
}
