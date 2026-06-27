<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin  = 'superadmin';
    case Admin       = 'admin';
    case Verifikator = 'verifikator';
    case Petugas     = 'petugas';
}
