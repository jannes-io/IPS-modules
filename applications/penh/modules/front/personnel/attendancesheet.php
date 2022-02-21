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

            $url = \IPS\Http\Url::internal('app=penh&module=personnel&controller=attendancesheet&do=combatunitsheet&from=&to=')
                ->setQueryString($queryString);
            \IPS\Output::i()->redirect($url);
            return;
        }

        $soldierForm = new Form('attendance_soldier_sheet', 'attendance_generate');
        $soldierForm->addHeader('attendance_soldier_sheet');
        $soldierForm->add(new Form\Node('attendance_combat_unit', null, true, [
            'multiple' => false,
            'class' => 'IPS\perscom\Units\CombatUnit',
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
            $queryString['combatunit'] = $values['attendance_combat_unit']->id;

            $url = \IPS\Http\Url::internal('app=penh&module=personnel&controller=attendancesheet&do=soldiersheet&from=&to=&combatunit=')
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
        $combatUnit = \IPS\perscom\Units\CombatUnit::load(\IPS\Request::i()->combatunit);
        $roster = \IPS\perscom\Personnel\Roster::load($combatUnit->roster);

        if (empty($combatUnit) || empty($roster)) {
            throw new \OutOfRangeException('Combat unit not found');
        }

        $personnel = \IPS\perscom\Personnel\Soldier::sortPersonnel(
            \IPS\perscom\Personnel\Soldier::roots('view', null, ['personnel_combat_unit=?', $combatUnit->id]),
            explode(',', $roster->sort)
        );

        $missions = [];
        $statistics = ['total' => ['total' => 0]];
        foreach (\IPS\penh\Operation\AfterActionReport::availableStatus() as $status) {
            $statistics['total'][$status] = 0;
        }

        foreach ($personnel as $soldier) {
            if (!isset($statistics[$soldier->id])) {
                $statistics[$soldier->id] = [];
                foreach (\IPS\penh\Operation\AfterActionReport::availableStatus() as $status) {
                    $statistics[$soldier->id][$status] = 0;
                }
                $statistics[$soldier->id]['total'] = 0;
            }

            $attendances = \IPS\penh\Operation\Attendance::findBySoldier($soldier->id, $fromParam, $toParam);
            foreach ($attendances as $attendance) {
                $mission = $attendance->aar->item();
                if (!isset($missions[$mission->id])) {
                    $missions[$mission->id] = [
                        'mission' => $mission,
                        'attendance' => []
                    ];
                }
                $missions[$mission->id]['attendance'][] = [
                    'soldier' => $soldier,
                    'attendance' => $attendance,
                ];
                $statistics[$soldier->id][$attendance->status]++;
                $statistics['total'][$attendance->status]++;
                $statistics[$soldier->id]['total']++;
                $statistics['total']['total']++;
            }
        }

        usort($missions, static function ($a, $b) {
            return $a['mission']->start < $b['mission']->start ? 1 : -1;
        });

        $title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->title = $title;
        \IPS\Output::i()->breadcrumb[] = [\IPS\Http\Url::internal('attendancesheet'), $title];
        \IPS\Output::i()->cssFiles = array_merge(\IPS\Output::i()->cssFiles, \IPS\Theme::i()->css('personnel/attendancesheet.css', 'penh', 'front'));
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->soldierSheet($personnel, $combatUnit, $missions, $statistics);
    }
}
