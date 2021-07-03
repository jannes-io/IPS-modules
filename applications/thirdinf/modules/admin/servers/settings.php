<?php

namespace IPS\thirdinf\modules\admin\servers;

use IPS\Dispatcher\Controller;
use IPS\Helpers\_Form;

/**
 * settings
 */
class _settings extends Controller
{
    public function execute(): void
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        parent::execute();
    }

    protected function manage()
    {
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('server_settings');

        $form = new \IPS\Helpers\Form;
        $form->addHeader('server_settings');
        $form->add(new \IPS\Helpers\Form\Text('ti_steam_api_key', \IPS\Settings::i()->ti_steam_api_key, false, [], null, null, null, 'ti_steam_api_key'));

        if ($values = $form->values()) {
            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
