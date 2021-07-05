<?php

namespace IPS\thirdid\Report;

use IPS\Helpers\Form;

/**
 * @property int $id
 * @property $date_due
 * @property int $supervisor_id
 * @property int $report_type_id
 */
class _Schedule extends \IPS\Patterns\ActiveRecord
{
    public static $application = 'thirdid';
    public static $multitons;

    public static $databaseTable = 'thirdid_report_schedules';
    public static $databasePrefix = 'report_schedule_';
    public static $databaseColumnOrder = 'id';

    public function form(&$form): void
    {
        $form->addHeader(self::$databasePrefix . 'form_title');
        $form->add(new Form\Node(self::$databasePrefix . 'report_type_id', $this->report_type_id, true, [
            'class' => '\IPS\thirdid\Report\Type',
        ], null, null, null, 'report_type_id'));
        $form->add(new Form\Date(self::$databasePrefix . 'date_due', $this->date_due, true, [], null, null, null, 'date_due'));
        $form->add(new Form\Member(self::$databasePrefix . 'supervisor_id', $this->supervisor_id, true, [], null, null, null, 'supervisor_id'));
    }

    public function formatFormValues($values)
    {
        $values[self::$databasePrefix . 'report_type_id'] = $values[self::$databasePrefix . 'report_type_id']->id;
        $values[self::$databasePrefix . 'date_due'] = $values[self::$databasePrefix . 'date_due']->format('Y-m-d');
        $values[self::$databasePrefix . 'supervisor_id'] = $values[self::$databasePrefix . 'supervisor_id']->member_id;
        return $values;
    }

    protected function get__title(): string
    {
        $reportType = \IPS\thirdid\Report\Type::load($this->report_type_id);
        return $this->date_due . ' - ' . $reportType->name;
    }
}
