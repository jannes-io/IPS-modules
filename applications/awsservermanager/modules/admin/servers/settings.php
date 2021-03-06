<?php

namespace IPS\awsservermanager\modules\admin\servers;

use IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * settings
 */
class _settings extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        parent::execute();
    }

    /**
     * ...
     *
     * @return    void
     */
    protected function manage()
    {
        $form = new Form;
        $form->addHeader('aws_credentials');
        $form->add(new Form\Text('aws_region', \IPS\Settings::i()->aws_region, false, ['placeholder' => 'us-east-2'], null, null, null, 'aws_region'));
        $form->add(new Form\Text('aws_access_key_id', \IPS\Settings::i()->aws_access_key_id, false, [], null, null, null, 'aws_access_key_id'));
        $form->add(new Form\Password('aws_access_key_secret', \IPS\Settings::i()->aws_access_key_secret, false, [], null, null, null, 'aws_access_key_secret'));
        $form->addHeader('steam_api_key');
        $form->add(new Form\Password('steam_api_key', \IPS\Settings::i()->steam_api_key, false, [], null, null, null, 'steam_api_key'));

        if ($values = $form->values()) {
            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
