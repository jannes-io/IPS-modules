<?php


namespace IPS\penh\modules\front\operations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * mission
 */
class _mission extends \IPS\Content\Controller
{
    public static $contentModel = 'IPS\penh\Operation\Mission';

	public function execute(): void
	{
		parent::execute();
	}

    public function manage(): void
    {
        $mission = parent::manage();
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations', 'penh', 'front')->mission($mission);
	}
}
