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
        $combatUnitSheet = new Form('attendance_combat_unit_sheet', 'attendance_generate');
        $combatUnitSheet->addHeader('attendance_combat_unit_sheet');
        $combatUnitSheet->add(new Form\Node('attendance_combat_unit', null, false, [
            'multiple' => true,
            'class' => 'IPS\perscom\Units\CombatUnit',
        ]));
        $combatUnitSheet->add(new Form\Date('attendance_from'));
        $combatUnitSheet->add(new Form\Date('attendance_to'));

        if ($values = $combatUnitSheet->values()) {
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

            $url = \IPS\Http\Url::internal('app=penh&module=personnel&controller=attendancesheet&do=combatunitsheet&from=&to=')
                ->setQueryString($queryString);
            \IPS\Output::i()->redirect($url);
            return;
        }

        $soldierForm = new Form('attendance_soldier_sheet', 'attendance_generate');
        $soldierForm->addHeader('attendance_soldier_sheet');
        $soldierForm->add(new Form\Node('attendance_soldier', null, true, [
            'multiple' => false,
            'class' => 'IPS\perscom\Personnel\Soldier',
        ]));
        $soldierForm->add(new Form\Date('attendance_from'));
        $soldierForm->add(new Form\Date('attendance_to'));
        if ($values = $soldierForm->values()) {
            $queryString = [];
            if (!empty($values['attendance_from'])) {
                $queryString['from'] = $values['attendance_from']->getTimestamp();
            }
            if (!empty($values['attendance_to'])) {
                $queryString['to'] = $values['attendance_to']->getTimestamp();
            }
            $queryString['soldier'] = $values['attendance_soldier']->id;

            $url = \IPS\Http\Url::internal('app=penh&module=personnel&controller=attendancesheet&do=soldiersheet&from=&to=&soldier=')
                ->setQueryString($queryString);
            \IPS\Output::i()->redirect($url);
            return;
        }

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->attendanceSheetForm($combatUnitSheet, $soldierForm);
    }

    protected function getDefaultDates(): array
    {
        $now = new \DateTime();
        $now->setTime(23, 59, 59);
        $threeMonthsAgo = (new \DateTime())->sub(new \DateInterval('P3M'));
        $threeMonthsAgo->setTime(0, 0, 0);

        $fromParam = \IPS\Request::i()->from ?: $threeMonthsAgo->getTimestamp();
        $toParam = \IPS\Request::i()->to ?: $now->getTimestamp();
        return ['from' => $fromParam, 'to' => $toParam];
    }

    public function combatunitsheet(): void
    {
        ['from' => $fromParam, 'to' => $toParam] = $this->getDefaultDates();
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
                $combatUnits,
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
        $roster = \IPS\perscom\Personnel\Roster::load($combatUnit->roster);
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

        foreach ($attendance as $status => $soldiers) {
            $attendance[$status] = \IPS\perscom\Personnel\Soldier::sortPersonnel($soldiers, explode(',', $roster->sort));
        }

        $stats['total'] = array_sum($stats) ?: 1;

        return [
            'combatUnit' => $combatUnit,
            'attendance' => $attendance,
            'statistics' => $stats,
            'url' => $aar->url(),
        ];
    }

    public function soldiersheet(): void
    {
        ['from' => $fromParam, 'to' => $toParam] = $this->getDefaultDates();

        $soldierId = \IPS\Request::i()->soldier;
        $soldier = \IPS\perscom\Personnel\Soldier::load($soldierId);
        $attendance = \IPS\penh\Operation\Attendance::findBySoldier($soldierId, $fromParam, $toParam);

        usort($attendance, static function ($a, $b) {
            return $a->aar->item()->start < $b->aar->item()->start ? 1 : -1;
        });

        $statistics = array_reduce($attendance, static function ($acc, $attended) {
            if (!isset($acc[$attended->status])) {
                $acc[$attended->status] = 0;
            }
            $acc[$attended->status]++;
            $acc['total']++;
            return $acc;
        }, ['total' => 0]);

        $title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->title = $title;
        \IPS\Output::i()->breadcrumb[] = [\IPS\Http\Url::internal('attendancesheet'), $title];
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->soldierSheet($soldier, $attendance, $statistics);
    }
}
