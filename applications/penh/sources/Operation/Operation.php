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
class _Operation extends \IPS\Node\Model implements \IPS\Content\Permissions
{
    public static $multitons;
    public static $databaseTable = 'penh_operations';
    public static $databasePrefix = 'operation_';
    public static $databaseColumnOrder = 'id DESC';
    public static $nodeTitle = 'operations';

    protected static $restrictions = [
        'app' => 'penh',
        'module' => 'operations',
        'prefix' => 'operations_'
    ];

    public static $permApp = 'operation';
    public static $permType = 'operation';

    public static $permissionMap = [
        'view' => 'view',
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
        $values['operation_start'] = $values['operation_start']->format('Y-m-d');
        $values['operation_end'] = $values['operation_end'] !== null ? $values['operation_end']->format('Y-m-d') : null;
        return $values;
    }

    public function get__title(): string
    {
        return $this->name;
    }

    public function getStartDate(): \IPS\DateTime
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $this->start);
        return \IPS\DateTime::ts($dt->getTimestamp());
    }

    public function getEndDate(): ?\IPS\DateTime
    {
        if ($this->end === null) {
            return null;
        }

        $dt = \DateTime::createFromFormat('Y-m-d', $this->end);
        return \IPS\DateTime::ts($dt->getTimestamp());
    }
}
