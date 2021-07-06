<?php

namespace IPS\penh\modules\admin\settings;

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
    public function execute(): void
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        parent::execute();
    }

    /**
     * ...
     *
     * @return    void
     */
    protected function manage(): void
    {
        $form = new Form;

        $form->addTab('penh_settings_missions');
        $form->addHeader('penh_settings_calendar');
        $form->add(new Form\YesNo('penh_settings_calendar_enable', \IPS\Settings::i()->penh_settings_calendar_enable, false));
        $form->add(new Form\Node('penh_settings_calendar_node', \IPS\Settings::i()->penh_settings_calendar_node, false, [
            'class' => '\IPS\calendar\Calendar'
        ]));

        $form->addHeader('penh_settings_combat_record');
        $form->add(new Form\YesNo('penh_settings_combat_record_entry_enable', \IPS\Settings::i()->penh_settings_combat_record_entry_enable, false));

        if ($values = $form->values()) {
            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
