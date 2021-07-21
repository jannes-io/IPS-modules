//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class penh_hook_soldierTPR extends _HOOK_CLASS_
{
	public function isTPRd()
	{
	    $days = \IPS\Settings::i()->personnel_action_request_duration ?? 3;
		$duration = (new \DateTime())->sub(new \DateInterval('P' . $days . 'D'));

        $select = \IPS\Db::i()->select(
            'requests_status AS status',
            'perscom_personnel_action_requests',
            [
				'requests_form = ? and requests_date > ? and requests_soldier = ?',
				\IPS\Settings::i()->personnel_action_request_id ?? 0,
				$duration->getTimestamp(),
				$this->id
			]
        );

        $result = iterator_to_array($select);
		if (empty($result)) {
			return null;
		}
        return $result[0];
	}

}
