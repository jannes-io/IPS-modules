<?php
/**
 * @brief		generateSquadXML Task
 * @author		<a href='https://www.invisioncommunity.com'>Invision Power Services, Inc.</a>
 * @copyright	(c) Invision Power Services, Inc.
 * @license		https://www.invisioncommunity.com/legal/standards/
 * @package		Invision Community
 * @subpackage	penh
 * @since		01 Feb 2022
 */

namespace IPS\penh\tasks;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * generateSquadXML Task
 */
class _generateSquadXML extends \IPS\Task
{
	/**
	 * Execute
	 *
	 * If ran successfully, should return anything worth logging. Only log something
	 * worth mentioning (don't log "task ran successfully"). Return NULL (actual NULL, not '' or 0) to not log (which will be most cases).
	 * If an error occurs which means the task could not finish running, throw an \IPS\Task\Exception - do not log an error as a normal log.
	 * Tasks should execute within the time of a normal HTTP request.
	 *
	 * @return	mixed	Message to log or NULL
	 * @throws	\IPS\Task\Exception
	 */
	public function execute()
	{
        if (!\IPS\Settings::i()->penh_personnel_squadxml_enable) {
            return null;
        }

        $xml = new \SimpleXMLElement('<squad nick="' . \IPS\Settings::i()->penh_personnel_squadxml_nick . '" />');
        $xml->addChild('name', \IPS\Settings::i()->board_name);
        $xml->addChild('title', \IPS\Settings::i()->board_name);
        $xml->addChild('email', \IPS\Settings::i()->email_out);

        if (\IPS\Settings::i()->base_url) {
            $xml->addChild('web', \IPS\Settings::i()->base_url);
        }

        $rootDir = \IPS\ROOT_PATH . DIRECTORY_SEPARATOR;
        $logo = \IPS\File::get('penh_SquadXML', \IPS\Settings::i()->penh_personnel_squadxml_logo);
        if ($logo) {
            copy(\IPS\ROOT_PATH . $logo->url->data['path'], $rootDir . 'logo.paa');
            $xml->addChild('picture', 'logo.paa');
        }

        $pidField = \IPS\Settings::i()->penh_personnel_squadxml_pid_field;
        if (empty($pidField)) {
            throw new \InvalidArgumentException('Player ID field must be specified.');
        }
        $pidColumn = \IPS\perscom\Personnel\CustomField::load($pidField)->column();

        $personnel = \IPS\perscom\Personnel\Soldier::roots();
        foreach ($personnel as $soldier) {
            $playerId = $soldier->$pidColumn;
            if (empty($playerId)) {
                continue;
            }

            $member = $xml->addChild('member');
            $member->addAttribute('id', $playerId);
            $member->addAttribute('nick', $soldier->get_member()->_name);

            $combatUnit = $soldier->get_combat_unit();
            $combatUnitPosition = $soldier->get_combat_unit_position()->_title;
            $member->addChild('name', $soldier->_title);
            $member->addChild('email', 'N/A');
            $member->addChild('icq', $combatUnit->position);
            $member->addChild('remark', "{$combatUnit->_title}, $combatUnitPosition");
        }
        file_put_contents($rootDir . 'penh_squad.xml', $xml->asXML());
		return null;
	}

	/**
	 * Cleanup
	 *
	 * If your task takes longer than 15 minutes to run, this method
	 * will be called before execute(). Use it to clean up anything which
	 * may not have been done
	 *
	 * @return	void
	 */
	public function cleanup()
	{
	}
}
