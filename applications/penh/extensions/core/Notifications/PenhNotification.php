<?php
/**
 * @brief        Notification Options
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    PERSCOM Enhanced
 * @since        05 Sep 2021
 */

namespace IPS\penh\extensions\core\Notifications;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * Notification Options
 */
class _PenhNotification
{
    public function getConfiguration($member): array
    {
        return [
            'missions' => ['default' => ['inline'], 'disabled' => ['email']]
        ];
    }

    public function parse_missions(\IPS\Notification\Inline $notification): array
    {
        $mission = $notification->item;
        if (!$mission) {
            throw new \OutOfRangeException('Notification sent without mission');
        }

        return [
            'title' => \IPS\Member::loggedIn()->language()->addToStack('notifications__missions_title', false, [
                'sprintf' => [
                    $mission->name,
                ]
            ]),
            'url' => $mission->url(),
            'content' => $mission->content(),
            'author' => $mission->author(),
        ];
    }
}
