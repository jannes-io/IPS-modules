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
    public function execute(): void
    {
        \IPS\Dispatcher::i()->checkAcpPermission('settings_manage');
        parent::execute();
    }

    protected function manage(): void
    {
        $form = new Form;

        $form->addHeader('penh_strength_sheet_settings');
        $form->add(new Form\Node('penh_strength_sheet_ignore_status', \IPS\Settings::i()->penh_strength_sheet_ignore_status, false, [
            'class' => '\IPS\perscom\Personnel\Status',
            'multiple' => true
        ], null, null, null, 'penh_strength_sheet_ignore_status'));

        $form->addHeader('penh_personnel_profile');
        $form->add(new Form\Node('penh_personnel_highlighted_awards', \IPS\Settings::i()->penh_personnel_highlighted_awards, false, [
            'class' => '\IPS\perscom\Awards\Award',
            'multiple' => true
        ], null, null, null, 'penh_personnel_highlighted_awards'));

        if ($values = $form->values()) {
            $values['penh_strength_sheet_ignore_status'] = \is_array($values['penh_strength_sheet_ignore_status'])
                ? implode(',', array_keys($values['penh_strength_sheet_ignore_status'] ?? []))
                : $values['penh_strength_sheet_ignore_status'];

            $values['penh_personnel_highlighted_awards'] = \is_array($values['penh_personnel_highlighted_awards'])
                ? implode(',', array_keys($values['penh_personnel_highlighted_awards'] ?? []))
                : $values['penh_personnel_highlighted_awards'];

            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
