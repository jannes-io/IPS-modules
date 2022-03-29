//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class penh_hook_ServiceRecordCitation extends _HOOK_CLASS_
{
    public static function awardForm($data)
    {
        $citationField = new \IPS\Helpers\Form\TextArea('penh_award_citation', null);

        $form = parent::awardForm($data);
        if ($form === true) {
            $form = new \IPS\Helpers\Form();
            $form->add($citationField);

            $values = $form->values();
            if (!$values) {
                return true;
            }

            $citationText = nl2br(trim($values['penh_award_citation'] ?? ''));
            if (empty($citationText)) {
                return true;
            }

            foreach (is_array($data) ? $data : [$data] as $soldierId) {
                self::addCitationToLastRecord($soldierId, $citationText);
            }

            return true;
        }

        $form->addHeader('__app_penh');
        $form->add($citationField);

        return $form;
    }

    public static function addCitationToLastRecord($soldierId, $citationText)
    {
        $records = iterator_to_array(\IPS\Db::i()->select(
            'service_records_id',
            self::$databaseTable,
            ['service_records_soldier = ?', $soldierId],
            'service_records_id DESC',
            1
        ));

        if (empty($records)) {
            return;
        }

        $citation = new \IPS\penh\Records\ServiceRecordCitation();
        $citation->service_record_id = $records[0];
        $citation->text = $citationText;
        $citation->save();
    }

    public function get_citation()
    {
        try {
            $citation = \IPS\penh\Records\ServiceRecordCitation::load($this->id);
        } catch (Exception $ex) {
            return null;
        }
        return $citation->text;
    }
}
