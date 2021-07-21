<?php

namespace IPS\penh\modules\admin\operations;

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
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        parent::execute();
    }

    protected function manage()
    {
        $form = new Form;

        $form->addHeader('penh_operations_settings');
        $form->add(new Form\Editor('penh_operations_content', \IPS\Settings::i()->penh_operations_content ?? null, true, [
            'app' => 'penh',
            'key' => 'Operation',
            'autoSaveKey' => 'penh_operations_content-' . ($item->id ?? 'new')
        ]));
        $form->add(new Form\Editor('penh_missions_template', \IPS\Settings::i()->penh_missions_template ?? null, true, [
            'app' => 'penh',
            'key' => 'Operation',
            'autoSaveKey' => 'penh_missions_template-' . ($item->id ?? 'new')
        ]));
        $form->add(new Form\Editor('penh_aar_template', \IPS\Settings::i()->penh_aar_template ?? null, true, [
            'app' => 'penh',
            'key' => 'Operation',
            'autoSaveKey' => 'penh_aar_template-' . ($item->id ?? 'new')
        ]));
        $statusValue = \IPS\Settings::i()->penh_aar_status ?? '';
        $form->add(new Form\Stack('penh_aar_status', \is_array($statusValue) ? $statusValue : explode(',', $statusValue)));

        $form->addHeader('penh_calendar_settings');
        $form->add(new Form\YesNo('penh_calendar_enable', \IPS\Settings::i()->penh_calendar_enable, false));
        $form->add(new Form\Node('penh_calendar_node', \IPS\Settings::i()->penh_calendar_node, false, [
            'class' => '\IPS\calendar\Calendar'
        ]));

        $form->addHeader('penh_combat_record_settings');
        $form->add(new Form\YesNo('penh_combat_record_entry_enable', \IPS\Settings::i()->penh_combat_record_entry_enable, false));
        $form->add(new Form\Text('penh_combat_record_aar_status', \IPS\Settings::i()->penh_combat_record_aar_status, false));

        if ($values = $form->values()) {
            $values['penh_calendar_node'] = $values['penh_calendar_node'] instanceof \IPS\Node\Model ? $values['penh_calendar_node']->id : null;
            $values['penh_aar_status'] = \is_array($values['penh_aar_status']) ? implode(',', $values['penh_aar_status'] ?? []) : $values['penh_aar_status'];
            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
