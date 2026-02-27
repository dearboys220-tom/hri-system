<?php

namespace App\Services;
use App\Models\ApplicantProfile;

class MemberIdService
{
    public function generate(): string
    {
        $lastId = ApplicantProfile::max('id') ?? 0;
        $nextNumber = $lastId + 1;

        return 'HRI-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}