<?php

namespace IPS\penh\Operation;

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
class _Mission extends \IPS\Content\Item implements \IPS\Content\Permissions
{
    public static $multitons;
    public static $application = 'penh';
    public static $module = 'operation';
    public static $databaseTable = 'penh_missions';
    public static $databasePrefix = 'mission_';
    public static $containerNodeClass = 'IPS\penh\Operation\Operation';

    public static $databaseColumnMap = [
        'container' => 'operation_id'
    ];
}
