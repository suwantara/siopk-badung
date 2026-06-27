<?php

namespace App\Enums;

enum StatusVerifikasi: string
{
    case Menunggu     = 'menunggu';
    case AiReview     = 'ai_review';
    case ReviewDinas  = 'review_dinas';
    case Disetujui    = 'disetujui';
    case Ditolak      = 'ditolak';
    case Duplikat     = 'duplikat';
}
