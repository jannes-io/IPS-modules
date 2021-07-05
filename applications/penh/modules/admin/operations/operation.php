<?php


namespace IPS\penh\modules\admin\operations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * operation
 */
class _operation extends \IPS\Node\Controller
{
    public static $csrfProtected = true;

	protected $nodeClass = '\IPS\penh\Operation\Operation';

	/**
	 * Execute
	 *
	 * @return	void
	 */
	public function execute()
	{
		\IPS\Dispatcher::i()->checkAcpPermission( 'operation_manage' );
		parent::execute();
	}
}
