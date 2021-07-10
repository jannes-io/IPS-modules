<?php

namespace IPS\penh\extensions\core\FrontNavigation;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Front Navigation Extension: Operations
 */
class _Operations extends \IPS\core\FrontNavigation\FrontNavigationAbstract
{
    public static function typeTitle(): string
    {
        return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_penh_operations');
    }

    public function canAccessContent()
    {
        return \IPS\Member::loggedIn()->canAccessModule(\IPS\Application\Module::get('penh', 'operations'));
    }

    public function title()
    {
        return \IPS\Member::loggedIn()->language()->addToStack('frontnavigation_penh_operations');
    }

    public function link()
    {
        return \IPS\Http\Url::internal('app=penh&module=operations', 'front', 'operation');
    }

    public function active()
    {
        return \IPS\Dispatcher::i()->application->directory === 'penh'
            && \IPS\Dispatcher::i()->module->key === 'operations';
    }
}
