<?php

namespace IPS\thirdinf\api;

use DateTime;
use IPS\_DateTime;
use IPS\Db\_Select;
use IPS\Helpers\_Chart;
use IPS\Request;
use IPS\Api\Controller;
use IPS\Api\Response;

class _attendance extends Controller
{
    private const AAR_TABLE = 'cms_custom_database_12';
    private const MISSION_TABLE = 'cms_custom_database_14';
    private const SOLDIERS_TABLE = 'perscom_personnel';
    private const COMBAT_UNIT_TABLE = 'perscom_combat_units';
    private const RANK_TABLE = 'perscom_ranks';

    /**
     * GET /thirdinf/attendance
     * Generates an attendance sheet, from and to must be formatted as 'Y-m-d'
     *
     * @reqapiparam     string  from
     * @reqapiparam     string  to
     * @apiparam        int  combatUnit
     * @return          Response
     */
    public function GETindex(): Response
    {
        $params = $this->getParams();
        $attendance = $this->getAttendance($params['from'], $params['to'], $params['combatUnit']);

        return new Response(200, $attendance);
    }

    /**
     * GET /thirdinf/attendance/graphs
     * Generates attendance graphs, same params as the sheet apply
     *
     * @reqapiparam     string  from
     * @reqapiparam     string  to
     * @apiparam        int  combatUnit
     */
    public function GETitem()
    {
        $params = $this->getParams();
        $events = $this->getAttendance($params['from'], $params['to'], $params['combatUnit']);

        $chartData = [];
        foreach ($events as $event) {
            $totals = array_reduce($event['attendance'], static function ($total, $attendance) {
                $total['present'] += $attendance['totalPresent'];
                $total['excused'] += $attendance['totalExcused'];
                $total['absent'] += $attendance['totalAbsent'];
                return $total;
            }, [
                'present' => 0,
                'excused' => 0,
                'absent' => 0,
            ]);
            $chartData[] = array_merge(['date' => $event['date']], $totals);
        }
        $chartData[] = ['Date', 'Present', 'Excused', 'Absent'];

        return new Response(200, array_reverse($chartData));
    }

    private function getParams(): array
    {
        $from = Request::i()->from;
        $to = Request::i()->to;
        $combatUnit = Request::i()->combatUnit;
        $fromTimestamp = DateTime::createFromFormat('Y-m-d', $from)->getTimestamp();
        $toTimestamp = DateTime::createFromFormat('Y-m-d', $to)->getTimestamp() + 24 * 60 * 60;

        return ['from' => $fromTimestamp, 'to' => $toTimestamp, 'combatUnit' => $combatUnit];
    }

    private function getAttendance(int $fromTimestamp, int $toTimestamp, $combatUnit): array
    {
        $aarTable = self::AAR_TABLE;
        $missionTable = self::MISSION_TABLE;
        $combatUnitTable = self::COMBAT_UNIT_TABLE;

        $where = "{$aarTable}.record_approved = 1 AND {$aarTable}.field_87 > $fromTimestamp AND {$aarTable}.field_87 < $toTimestamp";
        if ($combatUnit) {
            $combatUnit = (int)$combatUnit;
            $where .= " AND {$combatUnitTable}.combat_units_id = $combatUnit";
        }

        /** @var _Select $select */
        $select = \IPS\Db::i()->select("
                {$aarTable}.field_113 AS attendance,
                {$aarTable}.field_87 AS `date`,
                {$aarTable}.primary_id_field as aarId,
                {$aarTable}.record_dynamic_furl as aarFurl,
                {$missionTable}.field_102 AS missionName,
                {$missionTable}.primary_id_field AS missionId,
                {$missionTable}.record_dynamic_furl AS missionFurl,
                {$combatUnitTable}.combat_units_position AS combatUnit,
                {$combatUnitTable}.combat_units_order AS combatUnitOrder,
                {$combatUnitTable}.combat_units_nickname AS combatUnitNickname",
            self::AAR_TABLE,
            $where,
            "{$aarTable}.field_87 DESC"
        );
        $select->join(
            self::MISSION_TABLE,
            "${missionTable}.primary_id_field=${aarTable}.field_101",
            'INNER'
        );
        $select->join(
            self::COMBAT_UNIT_TABLE,
            "${combatUnitTable}.combat_units_position=${aarTable}.field_86",
            'INNER'
        );

        $events = [];
        foreach ($select as $row) {
            $missionId = $row['missionId'];
            $date = date('m/d/Y', $row['date']);
            $uniqid = "{$missionId}-{$date}";

            if (!isset($events[$uniqid])) {
                $missionUrl = "/missions/{$row['missionFurl']}-r{$missionId}";
                $events[$uniqid] = [
                    'missionName' => $row['missionName'],
                    'missionUrl' => $missionUrl,
                    'date' => $date
                ];
            }

            $attendance = $this->hydrateAttendance($row['attendance']);
            $totals = $this->calculateTotals($attendance);
            $totals['aarUrl'] = "/after-action-reports/${row['aarFurl']}-r{$row['aarId']}";
            $totals['combatUnit'] = $row['combatUnit'];
            $totals['combatUnitOrder'] = $row['combatUnitOrder'];
            $totals['combatUnitNickname'] = $row['combatUnitNickname'];
            $totals['soldiers'] = $attendance;

            $events[$uniqid]['attendance'][] = $totals;
        }

        return array_values($events);
    }

    /**
     * @param string $attendance
     * @return array
     */
    private function hydrateAttendance(string $attendance): array
    {
        $arr = json_decode($attendance, true);
        if (empty($arr)) {
            return [];
        }
        $soldierIds = implode(', ', array_keys($arr));

        $soldiersTable = self::SOLDIERS_TABLE;
        $ranksTable = self::RANK_TABLE;

        /** @var _Select $select */
        $select = \IPS\Db::i()->select("
                {$soldiersTable}.personnel_id as id,
                CONCAT({$soldiersTable}.personnel_firstname, ' ', {$soldiersTable}.personnel_lastname) AS name,
                ${ranksTable}.ranks_name AS rankName,
                {$ranksTable}.ranks_image_small AS rankImg",
            self::SOLDIERS_TABLE,
            "{$soldiersTable}.personnel_id IN ({$soldierIds})",
            "{$ranksTable}.ranks_order ASC"
        );
        $select->join(
            self::RANK_TABLE,
            "{$ranksTable}.ranks_id={$soldiersTable}.personnel_rank",
            'INNER'
        );

        $hydrated = [];
        foreach ($select as $soldier) {
            $soldier['attendance'] = $arr[$soldier['id']];
            $soldier['rankImg'] = '/uploads/' . $soldier['rankImg'];
            $hydrated[] = $soldier;
        }
        return $hydrated;
    }

    /**
     * @param array $soldiers
     * @return array
     */
    private function calculateTotals(array $soldiers): array
    {
        $result = ['totalPresent' => 0, 'totalExcused' => 0, 'totalAbsent' => 0];
        foreach ($soldiers as ['attendance' => $attendance]) {
            $attendanceLabel = ucfirst($attendance);
            $result["total{$attendanceLabel}"]++;
        }
        return $result;
    }
}
