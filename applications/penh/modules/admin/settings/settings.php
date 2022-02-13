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
    public static $csrfProtected = true;

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
            'multiple' => true,
        ]));

        $form->addHeader('penh_personnel_profile');
        $form->add(new Form\Node('penh_personnel_highlighted_awards', \IPS\Settings::i()->penh_personnel_highlighted_awards, false, [
            'class' => '\IPS\perscom\Awards\Award',
            'multiple' => true,
        ]));

        $defaultUniformPath = \IPS\Settings::i()->penh_personnel_default_uniform ?? '';
        $defaultUniform = !empty($defaultUniformPath) ? \IPS\File::get('penh_DefaultUniform', $defaultUniformPath) : null;
        $form->add(new Form\Upload('penh_personnel_default_uniform', $defaultUniform, false, [
            'storageExtension' => 'penh_DefaultUniform',
            'allowedFileTypes' => ['gif', 'jpeg', 'jpe', 'jpg', 'png'],
        ]));

        $form->addHeader('penh_personnel_squadxml');
        $form->addMessage('penh_personnel_squadxml_help');
        $form->add(new Form\YesNo('penh_personnel_squadxml_enable', \IPS\Settings::i()->penh_personnel_squadxml_enable, false));
        $form->add(new Form\Node('penh_personnel_squadxml_pid_field', \IPS\Settings::i()->penh_personnel_squadxml_pid_field, false, [
            'class' => 'IPS\perscom\Personnel\CustomField',
            'subnodes' => false
        ]));
        $form->add(new Form\Text('penh_personnel_squadxml_nick', \IPS\Settings::i()->penh_personnel_squadxml_nick, false));
        $squadXMLLogoPath = \IPS\Settings::i()->penh_personnel_squadxml_logo ?? '';
        $squadXMLLogo = !empty($squadXMLLogoPath) ? \IPS\File::get('penh_SquadXML', $squadXMLLogoPath) : null;
        $form->add(new Form\Upload('penh_personnel_squadxml_logo', $squadXMLLogo, false, [
            'storageExtension' => 'penh_SquadXML',
            'allowedFileTypes' => ['paa'],
        ]));

        if ($values = $form->values()) {
            $values['penh_strength_sheet_ignore_status'] = \is_array($values['penh_strength_sheet_ignore_status'])
                ? implode(',', array_keys($values['penh_strength_sheet_ignore_status'] ?? []))
                : $values['penh_strength_sheet_ignore_status'];

            $values['penh_personnel_highlighted_awards'] = \is_array($values['penh_personnel_highlighted_awards'])
                ? implode(',', array_keys($values['penh_personnel_highlighted_awards'] ?? []))
                : $values['penh_personnel_highlighted_awards'];

            $values['penh_personnel_squadxml_pid_field'] = $values['penh_personnel_squadxml_pid_field'] !== null ? (string)$values['penh_personnel_squadxml_pid_field']->id : null;
            $values['penh_personnel_default_uniform'] = $values['penh_personnel_default_uniform'] !== null ? (string)$values['penh_personnel_default_uniform'] : null;
            $values['penh_personnel_squadxml_logo'] = $values['penh_personnel_squadxml_logo'] !== null ? (string)$values['penh_personnel_squadxml_logo'] : null;

            $form->saveAsSettings($values);
        }

        \IPS\Output::i()->output = $form;
    }
}
