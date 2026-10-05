<?php

namespace App\Enums;

enum StatusVerifikasi: string
{
    case Menunggu = 'menunggu';
    case AiReview = 'ai_review';
    case ReviewDinas = 'review_dinas';
    case Disetujui = 'disetujui';
    case Ditolak = 'ditolak';
    case Duplikat = 'duplikat';

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Menunggu => [self::AiReview, self::Disetujui, self::Ditolak, self::Duplikat],
            self::AiReview => [self::ReviewDinas, self::Disetujui, self::Ditolak, self::Duplikat],
            self::ReviewDinas => [self::Disetujui, self::Ditolak, self::Duplikat],
            self::Disetujui => [],
            self::Ditolak => [],
            self::Duplikat => [],
        };
    }

    public function isTerminal(): bool
    {
        return empty($this->allowedTransitions());
    }
}
