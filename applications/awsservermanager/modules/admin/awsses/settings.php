<?php


namespace IPS\awsservermanager\modules\admin\awsses;

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
        $form->add(new Form\Text('aws_ses_region', \IPS\Settings::i()->aws_ses_region, false, ['placeholder' => 'us-east-2']));
        $form->add(new Form\Text('aws_ses_access_key_id', \IPS\Settings::i()->aws_ses_access_key_id, false));
        $form->add(new Form\Password('aws_ses_access_key_secret', \IPS\Settings::i()->aws_ses_access_key_secret, false));
        $form->add(new Form\YesNo('aws_ses_enable', \IPS\Settings::i()->aws_ses_enable, false));
        $form->add(new Form\YesNo('aws_ses_enable_bulk_only', \IPS\Settings::i()->aws_ses_enable_bulk_only, false));

        if ($values = $form->values()) {
            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
