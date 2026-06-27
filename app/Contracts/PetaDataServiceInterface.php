<?php

namespace App\Contracts;

use Illuminate\Http\Request;

interface PetaDataServiceInterface
{
    public function getPetaData(Request $request, bool $isAdmin = false): array;
}
