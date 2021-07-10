<?php

namespace IPS\penh\extensions\core\EditorLocations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Editor Extension: Operation
 */
class _AfterActionReport
{
    public function canUseHtml($member): bool
    {
        return true;
    }

    public function canAttach($member, $field): bool
    {
        return true;
    }
}
