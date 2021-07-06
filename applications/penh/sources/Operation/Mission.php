<?php

namespace IPS\penh\Operation;

use IPS\Helpers\Form;

/**
 * Class _Mission
 * @package IPS\penh\Operation
 *
 * @property int $id
 * @property int $operation_id
 * @property string $name
 * @property string $start
 * @property string $content
 */
class _Mission extends \IPS\Content\Item implements
    \IPS\Content\Permissions,
    \IPS\Content\Views
{
    use \IPS\Content\Statistics;

    public static $multitons;
    public static $application = 'penh';
    public static $module = 'operations';
    public static $databaseTable = 'penh_missions';
    public static $databasePrefix = 'mission_';
    public static $databaseColumnId = 'id';
    public static $containerNodeClass = 'IPS\penh\Operation\Operation';
    public static $title = 'name';

    public static $databaseColumnMap = [
        'author' => 'author',
        'author_name' => 'author_name',
        'container' => 'operation_id',
        'date' => 'created_at',
        'title' => 'name',
        'views' => 'views',
        'content' => 'content',
    ];

    public function get__title(): string
    {
        return $this->name;
    }

    public function url($action = null)
    {
        return \IPS\Http\Url::internal('app=penh&module=operations&controller=mission&id=' . $this->id);
    }

    public static function formElements($item = null, \IPS\Node\Model $container = null)
    {
        $form = parent::formElements($item, $container);
        $form['mission_start'] = new Form\Date('mission_start', $item->start ?? null, true, ['time' => true]);

        $form['mission_create_event'] = new Form\Checkbox('mission_create_event', null, false);
        $form['mission_create_combat_record_entry'] = new Form\Checkbox('mission_create_combat_record_entry', null, false);

        $form['mission_content'] = new Form\Editor('mission_content', $item->content ?? null, true, [
            'app' => 'penh',
            'key' => 'Operation',
            'autoSaveKey' => 'penh_mission_content-' . ($item->id ?? 'new')
        ]);

        return $form;
    }

    public function processForm($values): void
    {
        parent::processForm($values);
        $this->start = $values['mission_start']->getTimestamp();
        $this->content = $values['mission_content'];
    }
}
