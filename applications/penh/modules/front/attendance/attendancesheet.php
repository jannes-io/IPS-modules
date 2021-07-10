<?php

namespace IPS\penh\modules\front\attendance;

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

        if ($values = $form->values()) {
            $url = \IPS\Http\Url::internal('app=penh&module=attendance&controller=attendancesheet&do=sheet&from=&to=')->setQueryString([
                'from' => $values['attendance_from']->getTimestamp(),
                'to' => $values['attendance_to']->getTimestamp(),
            ]);
            \IPS\Output::i()->redirect($url);
            return;
        }

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('attendance')->attendanceSheetForm($form);
    }

    public function sheet(): void
    {
        $now = new \DateTime();
        $threeMonthsAgo = (new \DateTime())->sub(new \DateInterval('P3M'));

        $fromParam = \IPS\Request::i()->from ?? $threeMonthsAgo->getTimestamp();
        $toParam = \IPS\Request::i()->to ?? $now->getTimestamp();

        $select = \IPS\Db::i()->select(
            '*',
            \IPS\penh\Operation\Mission::$databaseTable,
            ['mission_start > ? and mission_end < ?', $fromParam, $toParam],
            'mission_start DESC'
        );
        $missions = [];
        foreach ($select as $mission) {
            $missions[] = \IPS\penh\Operation\Mission::constructFromData($mission);
        }

        $attendance = [];
        foreach ($missions as $mission) {
            $record = [
                'mission' => $mission,
                'statistics' => [],
                'afterActionReports' => [],
            ];

            foreach ($mission->comments() as $aar) {
                $combatUnitAttendance = $this->getCombatUnitAttendance($aar);
                $record['afterActionReports'][] = $combatUnitAttendance;

                if (empty($record['statistics'])) {
                    $record['statistics'] = $combatUnitAttendance['statistics'];
                } else {
                    foreach ($record['statistics'] as $status => $amount) {
                        $record['statistics'][$status] = $amount + $combatUnitAttendance['statistics'][$status];
                    }
                }
            }
            $attendance[] = $record;
        }

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('attendance_sheet_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('attendance')->attendanceSheet($attendance);
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
            $attendance[$status][] = \IPS\perscom\Personnel\Soldier::load($soldierId);
            $stats[$status]++;
        }

        $stats['total'] = array_sum($stats);

        return [
            'combatUnit' => $combatUnit,
            'attendance' => $attendance,
            'statistics' => $stats,
            'url' => $aar->url(),
        ];
    }
}
