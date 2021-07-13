<?php

namespace IPS\penh\modules\front\personnel;

use \IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * strengthsheet
 */
class _strengthsheet extends \IPS\Dispatcher\Controller
{
    public function execute(): void
    {
        parent::execute();
        \IPS\Output::i()->cssFiles = array_merge(\IPS\Output::i()->cssFiles, \IPS\Theme::i()->css('personnel/combatUnit.css', 'penh', 'front'));
    }

    protected function manage(): void
    {
        $form = new Form('strength_sheet_form', 'strength_sheet_generate');
        $form->add(new Form\Node('strength_sheet_combat_unit', null, true, [
            'class' => 'IPS\perscom\Units\CombatUnit'
        ]));

        if ($values = $form->values()) {
            $url = \IPS\Http\Url::internal('app=penh&module=personnel&controller=strengthsheet&do=sheet&combatunit=')->setQueryString([
                'combatunit' => $values['strength_sheet_combat_unit']->id,
            ]);
            \IPS\Output::i()->redirect($url);
            return;
        }

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('strength_sheet_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->strengthSheetForm($form);
    }

    public function sheet(): void
    {
        try {
            $combatUnit = \IPS\perscom\Units\CombatUnit::load(\IPS\Request::i()->combatunit);
            $childUnits = \IPS\perscom\Units\CombatUnit::roots(null, null, ['combat_units_parent = ?', $combatUnit->id]);

            $withPersonnel = array_map(static function ($unit) {
                $personnel = \IPS\perscom\Personnel\Soldier::roots('view', null, ['personnel_combat_unit = ?', $unit->id]);
                $roster = \IPS\perscom\Personnel\Roster::load($unit->roster);
                $sorted = \IPS\perscom\Personnel\Soldier::sortPersonnel($personnel, explode(',', $roster->sort));
                return ['combatUnit' => $unit, 'personnel' => $sorted];
            }, array_merge(array_values($childUnits), [$combatUnit]));
            $parentUnit = array_pop($withPersonnel);
        } catch (\Exception $e) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        $soldierTable = \IPS\perscom\Personnel\Soldier::$databaseTable;
        $statusTable = \IPS\perscom\Personnel\Status::$databaseTable;
        $ignoreStatus = implode(',', array_keys(\IPS\Settings::i()->penh_strength_sheet_ignore_status ?: []));
        $statusQuery = \IPS\Db::i()->select(
            "{$statusTable}.status_name AS status, COUNT({$soldierTable}.personnel_id) as members",
            $statusTable,
            "{$statusTable}.status_id NOT IN ({$ignoreStatus})",
            null,
            null,
            "{$statusTable}.status_id",
            'members > 0'
        );
        $statusQuery->join($soldierTable, "{$soldierTable}.personnel_status={$statusTable}.status_id", 'LEFT');
        $statusCounts = \iterator_to_array($statusQuery);
        $statusCount = [
            'status' => $statusCounts,
            'total' => array_sum(array_column($statusCounts, 'members'))
        ];

        $combatUnitCount = array_reduce($withPersonnel, static function ($acc, $unit) {
            $amount = \count($unit['personnel']);
            if ($acc['min'] === $amount) {
                $acc['minCombatUnits'][] = $unit['combatUnit'];
            } elseif ($amount < $acc['min']) {
                $acc['min'] = $amount;
                $acc['minCombatUnits'] = [$unit['combatUnit']];
            }

            if ($amount > $acc['max']) {
                $acc['max'] = $amount;
            }

            return $acc;
        }, ['min' => PHP_INT_MAX, 'minCombatUnits' => [], 'max' => 0]);

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('strength_sheet_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->strengthSheet($statusCount, $combatUnitCount, $parentUnit, $withPersonnel);
    }
}
