<?php

namespace IPS\awsservermanager\Server;

class _SteamServerInfo
{
    /** @var string */
    public $name;

    /** @var string */
    public $game;

    /** @var int */
    public $players;

    /** @var int */
    public $maxPlayers;

    public function __construct(array $serverInfoArr)
    {
        $this->name = $serverInfoArr['name'] ?? '';
        $this->game = $serverInfoArr['product'] ?? '';
        $this->players = $serverInfoArr['players'] ?? 0;
        $this->maxPlayers = $serverInfoArr['max_players'] ?? 0;
    }
}
