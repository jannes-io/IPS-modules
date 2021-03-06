<?php


namespace IPS\awsservermanager\modules\front\servers;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * ajax
 */
class _ajax extends \IPS\Dispatcher\Controller
{
    protected $server;

    /**
     * Execute
     *
     * @return    void
     */
    public function execute(): void
    {
        $server = \IPS\awsservermanager\Server::load(\IPS\Request::i()->id);
        if (!$server) {
            \IPS\Output::i()->json('not_found', 404);
            return;
        }
        $this->server = $server;

        parent::execute();
    }

    protected function manage(): void
    {
        if (!$this->server->canView()) {
            \IPS\Output::i()->json('forbidden', 403);
            return;
        }
        $this->getState();
    }

    public function startServer(): void
    {
        if ($this->server->can('start')) {
            $this->server->start();
        }
        $this->getState('start');
    }

    public function stopServer(): void
    {
        if ($this->server->can('stop')) {
            $this->server->stop();
        }
        $this->getState('stop');
    }

    public function rebootServer(): void
    {
        if ($this->server->can('stop')) {
            $this->server->reboot();
        }
        $this->getState('reboot');
    }

    protected function getState(?string $action = null): void
    {
        $output = [];
        $steamInfo = $this->server->getSteamInfo();
        $output['steam'] = $steamInfo === null ? null : [
            'name' => $steamInfo->name,
            'game' => $steamInfo->game,
            'players' => $steamInfo->players,
            'max_players' => $steamInfo->maxPlayers
        ];

        switch ($action) {
            case 'start':
                $output['state'] = 'starting';
                break;
            case 'stop':
                $output['state'] = 'stopping';
                break;
            case 'reboot':
                $output['state'] = 'rebooting';
                break;
            default:
                $output['state'] = $this->server->getState();
        }

        \IPS\Output::i()->json($output);
    }
}
