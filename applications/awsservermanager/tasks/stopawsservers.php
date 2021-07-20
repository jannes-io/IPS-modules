<?php
/**
 * @brief        stopawsservers Task
 * @author        <a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright    (c) Invision Power Services, Inc.
 * @license        https://www.invisioncommunity.com/legal/standards/
 * @package        Invision Community
 * @subpackage    awsservermanager
 * @since        06 Mar 2021
 */

namespace IPS\awsservermanager\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */

use DateTime;
use IPS\awsservermanager\Server;

if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * stopawsservers Task
 */
class _stopawsservers extends \IPS\Task
{
    /**
     * Execute
     *
     * If ran successfully, should return anything worth logging. Only log something
     * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
     * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
     * Tasks should execute within the time of a normal HTTP request.
     *
     * @return    mixed    Message to log or NULL
     * @throws    \IPS\Task\Exception
     */
    public function execute()
    {
        try {
            $runningServers = [];
            foreach (\IPS\Db::i()->select('*', Server::$databaseTable) as $serverData) {
                /** @var Server $server */
                $server = Server::constructFromData($serverData);
                if ($server->getState() === 'running') {
                    $runningServers[] = $server;
                }
            }
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        foreach ($runningServers as $server) {
            $steamInfo = $server->getSteamInfo();
            if ($steamInfo !== null && $steamInfo->players > 0) {
                $now = new DateTime();
                $server->last_activity = $now->format('Y-m-d H:i:s');
                $server->save();
            }

            if ($steamInfo === null || $steamInfo->players === 0) {
                $this->stopServerIfExpired($server);
            }
        }

        return null;
    }

    protected function stopServerIfExpired(Server $server): void
    {
        $minutesSinceLastActivity = \IPS\Settings::i()->minutes_since_last_activity;

        $stopThreshold = (new DateTime())->sub(new \DateInterval("PT{$minutesSinceLastActivity}M"));
        $lastSeenWithPlayers = DateTime::createFromFormat('Y-m-d H:i:s', $server->last_activity);

        if ($lastSeenWithPlayers < $stopThreshold) {
            $server->stop();
        }
    }

    /**
     * Cleanup
     *
     * If your task takes longer than 15 minutes to run, this method
     * will be called before execute(). Use it to clean up anything which
     * may not have been done
     *
     * @return    void
     */
    public function cleanup()
    {
    }
}
