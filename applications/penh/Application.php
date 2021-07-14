<?php
/**
 * @brief        PERSCOM Enhanced Application Class
 * @author        <a href='https://3rdinf.us'>3rd Infantry Division</a>
 * @copyright    (c) 2021 3rd Infantry Division
 * @package        Invision Community
 * @subpackage    PERSCOM Enhanced
 * @since        04 Jul 2021
 * @version
 */

namespace IPS\penh;

/**
 * PERSCOM Enhanced Application Class
 */
class _Application extends \IPS\Application
{
    protected function get__icon(): string
    {
        return 'angle-double-up';
    }

    public function defaultFrontNavigation(): array
    {
        return [
            'rootTabs' => [
                [
                    'key' => 'PerscomEnhanced',
                    'children' => [
                        ['key' => 'Operations'],
                        [
                            'app' => 'core',
                            'key' => 'Menu',
                            'title' => 'frontnavigation_penh_personnel',
                            'children' => [
                                ['key' => 'Orbat'],
                                ['key' => 'Attendance'],
                                ['key' => 'StrengthSheet'],
                            ]
                        ]
                    ]
                ]
            ],
            'browseTabs' => [],
            'browseTabsEnd' => [],
            'activityTabs' => [],
        ];
    }
}
