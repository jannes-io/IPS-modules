<?php


namespace IPS\penh\modules\front\personnel;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * orbat
 */
class _orbat extends \IPS\Dispatcher\Controller
{
	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		parent::execute();
	}

	/**
	 * ...
	 *
	 * @return	void
	 */
	protected function manage()
	{
	    $combatUnits = \IPS\perscom\Units\CombatUnit::roots();

	    \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel', 'penh', 'front')->orbat($combatUnits);
	}
}
