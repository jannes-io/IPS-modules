<?php

namespace IPS\penh\Operation;

/**
 * Class _Attendance
 * @package IPS\penh\Operation
 *
 * @property int $soldier_id
 * @property int $aar_id
 * @property string $status
 */
class _Attendance extends \IPS\Patterns\ActiveRecord
{
    public static $databaseTable = 'penh_mission_attendance';
    public static $databasePrefix = 'attendance_';

    public function get_soldier(): ?\IPS\perscom\Personnel\Soldier
    {
        try {
            return \IPS\perscom\Personnel\Soldier::load($this->soldier_id);
        } catch (\OutOfRangeException $e) {
        }

        return null;
    }

    public function get_aar(): ?\IPS\penh\Operation\AfterActionReport
    {
        try {
            return \IPS\penh\Operation\AfterActionReport::load($this->aar_id);
        } catch (\OutOfRangeException $e) {
        }

        return null;
    }

    public static function findByAar(int $aarId): array
    {
        $select = \IPS\Db::i()->select(
            '*',
            self::$databaseTable,
            [
                'attendance_aar_id = ?',
                $aarId,
            ]
        );

        return array_map(static function ($attendance) {
            return self::constructFromData($attendance);
        }, iterator_to_array($select));
    }

    public static function findBySoldier(int $soldierId, int $from, int $to): array
    {
        $attendanceTable = self::$databaseTable;
        $aarTable = \IPS\penh\Operation\AfterActionReport::$databaseTable;
        $missionTable = \IPS\penh\Operation\Mission::$databaseTable;
        $select = \IPS\Db::i()->select(
            "{$attendanceTable}.*",
            $attendanceTable,
            [
                "attendance_soldier_id = ? AND {$missionTable}.mission_start > ? AND {$missionTable}.mission_end < ?",
                $soldierId,
                $from,
                $to
            ]
        );
        $select->join($aarTable, "{$attendanceTable}.attendance_aar_id = {$aarTable}.aar_id", 'LEFT');
        $select->join($missionTable, "{$missionTable}.mission_id = {$aarTable}.aar_mission_id", 'LEFT');

        return array_map(static function ($attendance) {
            return self::constructFromData($attendance);
        }, iterator_to_array($select));
    }

    public static function findOneBySoldierAndAar(int $soldierId, int $aarId): ?\IPS\penh\Operation\Attendance
    {
        $select = \IPS\Db::i()->select(
            '*',
            self::$databaseTable,
            [
                'attendance_aar_id = ? AND attendance_soldier_id = ?',
                $aarId,
                $soldierId,
            ],
            null,
            1
        );
        $attendance = iterator_to_array($select);
        if (empty($attendance)) {
            return null;
        }
        return self::constructFromData($attendance[0]);
    }
}
