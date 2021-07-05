<?php

namespace IPS\thirdid\Report;

use IPS\Helpers\Form;

/**
 * @property int $id
 * @property string $name
 */
class _Type extends \IPS\Node\Model implements \IPS\Node\Permissions
{
    public static $nodeTitle = 'thirdid_report_type';
    public static $databaseTable = 'thirdid_report_types';
    public static $databasePrefix = 'report_type_';
    public static $databaseColumnOrder = 'id';
    public static $permApp = 'thirdid';
    public static $permType = 'report_types';
    public static $permissionLangPrefix = 'thirdid_report_type_';
    public static $permissionMap = [
        'view' => 'view'
    ];
    public static $seoTitleColumn = 'id';
    public static $multitons;
    public static $restrictions = [
        'app' => 'thirdid',
        'module' => 'report',
        'prefix' => 'report_type_'
    ];

    public function form(&$form): void
    {
        $form->addHeader(self::$databasePrefix . 'form_title');
        $form->add(new Form\Text(self::$databasePrefix . 'name', $this->name, true, [], null, null, null, 'name'));
    }

    protected function get__title(): string
    {
        return $this->name;
    }
}
