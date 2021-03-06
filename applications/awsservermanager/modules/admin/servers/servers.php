<?php

namespace IPS\awsservermanager\modules\admin\servers;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * servers
 */
class _servers extends \IPS\Node\Controller
{
    public static $csrfProtected = true;

    protected $nodeClass = 'IPS\awsservermanager\Server';

    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        \IPS\Dispatcher::i()->checkAcpPermission('servers_manage');
        parent::execute();
    }
}
