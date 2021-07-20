<?php

namespace IPS\penh\modules\front\personnel;

use \IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * attendancesheet
 */
class _attendancesheet extends \IPS\Dispatcher\Controller
{
    public function execute(): void
    {
        parent::execute();
    }

    protected function manage(): void
    {
        $form = new Form('attendance_form', 'attendance_generate');
        $form->add(new Form\Date('attendance_from'));
        $form->add(new Form\Date('attendance_to'));
        $form->add(new Form\Node('attendance_combat_unit', null, false, [
            'multiple' => true,
            'class' => 'IPS\perscom\Units\CombatUnit'
        ]));

        if ($values = $form->values()) {
            $queryString = [];
            if (!empty($values['attendance_from'])) {
                $queryString['from'] = $values['attendance_from']->getTimestamp();
            }
            if (!empty($values['attendance_to'])) {
                $queryString['to'] = $values['attendance_to']->getTimestamp();
            }
            if (!empty($values['attendance_combat_unit'])) {
                $queryString['combatunit'] = implode(',', array_keys($values['attendance_combat_unit']));
            }

            $url = \IPS\Http\Url::internal('app=penh&module=personnel&controller=attendancesheet&do=sheet&from=&to=')
                ->setQueryString($queryString);
            \IPS\Output::i()->redirect($url);
            return;
        }

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->attendanceSheetForm($form);
    }

    public function sheet(): void
    {
        $now = new \DateTime();
        $threeMonthsAgo = (new \DateTime())->sub(new \DateInterval('P3M'));

        $fromParam = \IPS\Request::i()->from ?? $threeMonthsAgo->getTimestamp();
        $toParam = \IPS\Request::i()->to ?? $now->getTimestamp();
        $combatUnits = \IPS\Request::i()->combatunit ?? '';

        $missionTable = \IPS\penh\Operation\Mission::$databaseTable;
        $aarTable = \IPS\penh\Operation\AfterActionReport::$databaseTable;
        $select = \IPS\Db::i()->select(
            "{$missionTable}.*, COUNT({$aarTable}.aar_id) AS aarCount",
            \IPS\penh\Operation\Mission::$databaseTable,
            [
                "{$missionTable}.mission_start > ? AND {$missionTable}.mission_end < ?" . (!empty($combatUnits) ? " AND {$aarTable}.aar_combat_unit_id IN ({$combatUnits})" : ''),
                $fromParam,
                $toParam,
                $combatUnits
            ],
            'mission_start DESC',
            null,
            "{$missionTable}.mission_id",
            'aarCount > 0'
        );
        $select->join($aarTable, "{$missionTable}.mission_id={$aarTable}.aar_mission_id", 'LEFT');

        $missions = [];
        foreach ($select as $mission) {
            $missions[] = \IPS\penh\Operation\Mission::constructFromData($mission);
        }

        $attendance = [];
        foreach ($missions as $mission) {
            $record = [
                'mission' => $mission,
                'statistics' => [],
            ];

            $afterActionReports = [];
            foreach ($mission->comments(null, null, 'date', 'asc', null, null, null, empty($combatUnits) ? null : "{$aarTable}.aar_combat_unit_id IN ({$combatUnits})") as $aar) {
                $combatUnitAttendance = $this->getCombatUnitAttendance($aar);
                $afterActionReports[] = $combatUnitAttendance;

                if (empty($record['statistics'])) {
                    $record['statistics'] = $combatUnitAttendance['statistics'];
                } else {
                    foreach ($record['statistics'] as $status => $amount) {
                        $record['statistics'][$status] = $amount + $combatUnitAttendance['statistics'][$status];
                    }
                }
            }

            usort($afterActionReports, static function ($aar1, $aar2) {
                return $aar1['combatUnit']->order - $aar2['combatUnit']->order;
            });

            $record['afterActionReports'] = $afterActionReports;
            $attendance[] = $record;
        }

        $title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->title = $title;
        \IPS\Output::i()->breadcrumb[] = [\IPS\Http\Url::internal('attendancesheet'), $title];
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->attendanceSheet($attendance);
    }

    protected function getCombatUnitAttendance(\IPS\penh\Operation\AfterActionReport $aar): array
    {
        $combatUnit = \IPS\perscom\Units\CombatUnit::load($aar->combat_unit_id);
        $attendance = [];

        $stats = [];
        foreach (\IPS\penh\Operation\AfterActionReport::availableStatus() as $status) {
            $attendance[$status] = [];
            $stats[$status] = 0;
        }

        foreach ($aar->getAttendance() as $soldierId => $status) {
            try {
                $attendance[$status][] = \IPS\perscom\Personnel\Soldier::load($soldierId);
            } catch (\Exception $ex) {
                continue;
            }
            $stats[$status]++;
        }

        $stats['total'] = array_sum($stats) ?: 1;

        return [
            'combatUnit' => $combatUnit,
            'attendance' => $attendance,
            'statistics' => $stats,
            'url' => $aar->url(),
        ];
    }
}
