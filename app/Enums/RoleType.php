<?php

namespace App\Enums;

class RoleType
{
    const APPLICANT = 'applicant';
    const COMPANY = 'company';
    const INVESTIGATOR = 'investigator_user';
    const REVIEWER = 'reviewer_user';
    const ADMIN = 'admin_user';

    public static function staffRoles(): array
    {
        return [
            self::INVESTIGATOR,
            self::REVIEWER,
            self::ADMIN,
        ];
    }
}