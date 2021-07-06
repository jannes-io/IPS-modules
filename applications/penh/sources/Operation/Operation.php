<?php

namespace IPS\penh\Operation;

use IPS\Helpers\Form;

/**
 * Class _Operation
 * @package IPS\penh\Operation
 *
 * @property int $id
 * @property string $name
 * @property string $content
 * @property string $start
 * @property string|null $end
 */
class _Operation extends \IPS\Node\Model implements \IPS\Node\Permissions
{
    use \IPS\Node\Statistics;

    public static $multitons;
    public static $application = 'penh';
    public static $module = 'operation';
    public static $databaseTable = 'penh_operations';
    public static $databasePrefix = 'operation_';
    public static $databaseColumnOrder = 'id DESC';
    public static $nodeTitle = 'operations';
    public static $urlTemplate = 'operation';
    public static $urlBase = 'app=penh&module=operations&controller=operation&do=view&id=';
    public static $seoTitleColumn = 'name';

    protected static $restrictions = [
        'app' => 'penh',
        'module' => 'operations',
        'prefix' => 'operations_'
    ];

    public static $permApp = 'penh';
    public static $permType = 'operation';

    public static $permissionMap = [
        'view' => 'view',
        'read' => 2,
        'add' => 3,
    ];

    public static $titleLangPrefix = 'penh_operation_';
    public static $modPerm = 'penh_operations';
    public static $contentItemClass = 'IPS\penh\Operation\Mission';
    public static $permissionLangPrefix = 'penh_operations_';

    public function form(&$form): void
    {
        $form->add(new Form\Text('operation_name', $this->name, true));
        $form->add(new Form\Editor('operation_content', $this->content, true, [
            'app' => 'penh',
            'key' => 'Operation',
            'autoSaveKey' => 'penh_operation_content-' . ($this->id ?: 'new')
        ]));
        $form->add(new Form\Date('operation_start', $this->start, true));
        $form->add(new Form\Date('operation_end', $this->end, false));
    }

    public function formatFormValues($values): array
    {
        $values['operation_start'] = $values['operation_start']->getTimestamp();
        $values['operation_end'] = $values['operation_end'] !== null ? $values['operation_end']->getTimestamp() : null;
        return $values;
    }

    public function get__title(): string
    {
        return $this->name;
    }

    public function url()
    {
        return \IPS\Http\Url::internal('app=penh&module=operations&controller=operation&do=view&id='. $this->id);
    }
}
