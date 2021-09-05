<?php

namespace IPS\penh\Operation;

use IPS\Helpers\Form;

/**
 * Class _AfterActionReport
 * @package IPS\penh\Operation
 *
 * @property int $id
 * @property int $mission_id
 * @property int $combat_unit_id
 * @property string $attendance
 * @property string $content
 * @property int $author
 * @property string $author_name
 * @property int $created_at
 */
class _AfterActionReport extends \IPS\Content\Comment
{
    protected static $multitons;
    protected static $defaultValues = null;

    public static $application = 'penh';
    public static $module = 'operations';
    public static $databaseTable = 'penh_mission_aars';
    public static $databasePrefix = 'aar_';
    public static $title = 'mission_aars';
    public static $itemClass = 'IPS\penh\Operation\Mission';
    public static $commentTemplate = [['operations', 'penh', 'front'], 'afterActionReportRow'];

    public static $databaseColumnMap = [
        'item' => 'mission_id',
        'author' => 'author',
        'author_name' => 'author_name',
        'content' => 'content',
        'date' => 'created_at',
    ];

    public function title(): string
    {
        $combatUnit = $this->combatUnit();
        return $combatUnit->position . ' - ' . $combatUnit->name;
    }

    public function url($action = 'find')
    {
        return \IPS\Http\Url::internal('app=penh&module=operations&controller=afteractionreport&id=' . $this->id);
    }

    public function combatUnit()
    {
        return \IPS\perscom\Units\CombatUnit::load($this->combat_unit_id);
    }

    public function start()
    {
        return $this->start ?? $this->item()->start;
    }

    public function end()
    {
        return $this->end ?? $this->item()->end;
    }

    public function getAttendance(): array
    {
        return json_decode($this->attendance, true) ?? [];
    }

    public function setAttendance(array $attendance): void
    {
        $this->attendance = json_encode($attendance) ?? '{}';
    }

    public static function availableStatus(): array
    {
        $statusValue = \IPS\Settings::i()->penh_aar_status ?? '';
        return \is_array($statusValue) ? $statusValue : explode(',', $statusValue);
    }

    public static function buildForm(\IPS\penh\Operation\Mission $mission, $aar = null)
    {
        $form = new Form;
        $form->add(new Form\Node('aar_combat_unit_id', $aar->combat_unit_id ?? null, true, [
            'class' => 'IPS\perscom\Units\CombatUnit',
        ]));
        $form->add(new Form\Text('aar_attendance', $aar->attendance ?? '{}', false));
        $form->add(new Form\Editor('aar_content', $aar->content ?? \IPS\Settings::i()->penh_aar_template, true, [
            'app' => 'penh',
            'key' => 'AfterActionReport',
            'autoSaveKey' => 'penh_aar_content-' . ($aar->id ?? 'new'),
        ]));

        return $form;
    }

    public static function createFromForm($values, $mission): self
    {
        $aar = static::create($mission, $values['aar_content']);
        $aar->processForm($values);

        return $aar;
    }

    public function processForm($values): void
    {
        $this->content = $values['aar_content'];
        $this->attendance = $values['aar_attendance'];
        $this->combat_unit_id = $values['aar_combat_unit_id']->id;
    }

    public function processAfterCreate(): void
    {
        $this->addCombatRecords();
    }

    protected function addCombatRecords(): void
    {
        if (!\IPS\Settings::i()->penh_combat_record_entry_enable || empty(\IPS\Settings::i()->penh_combat_record_aar_status)) {
            return;
        }

        /** @var _Mission $mission */
        $mission = $this->item();
        if (!$mission->create_combat_record) {
            return;
        }

        $eligibleStatus = \IPS\Settings::i()->penh_combat_record_aar_status;
        foreach ($this->getAttendance() as $soldierId => $status) {
            if ($status !== $eligibleStatus) {
                continue;
            }
            try {
                $soldier = \IPS\perscom\Personnel\Soldier::load($soldierId);
            } catch (\Exception $e) {
                continue;
            }

            $soldier->addCombatRecord(\IPS\DateTime::ts($this->end()), $mission->combat_record_entry);
        }
    }
}
