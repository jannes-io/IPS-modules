<?php

namespace IPS\awsservermanager\modules\front\servers;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * servers
 */
class _servers extends \IPS\Dispatcher\Controller
{
    /**
     * ...
     *
     * @return    void
     */
    protected function manage(): void
    {
        $servers = [];
        foreach (\IPS\Db::i()->select('*', \IPS\awsservermanager\Server::$databaseTable) as $serverData) {
            $server = \IPS\awsservermanager\Server::constructFromData($serverData);
            if ($server->canView()) {
                $servers[] = $server;
            }
        }

        \IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('front_servers.js', 'awsservermanager', 'front'));
        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('servers_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('servers', 'awsservermanager', 'front')->servers($servers);
    }
}
