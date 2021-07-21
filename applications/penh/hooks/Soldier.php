//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class penh_hook_Soldier extends _HOOK_CLASS_
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

    public function getHighlightedAwards()
    {
        $eligibleAwards = \IPS\Settings::i()->penh_personnel_highlighted_awards;
        if (empty($eligibleAwards)) {
            return [];
        }

        $awards = \IPS\perscom\Awards\Award::roots('view', null,
            ["awards_id IN ({$eligibleAwards})"]
        );

        $highlightedAwards = [];
        foreach ($awards as $award) {
            $select = \IPS\Db::i()->select(
                'COUNT(*)',
                \IPS\perscom\Records\Service::$databaseTable,
                ["service_records_soldier = ? AND service_records_text LIKE '%{$award->name}%'", $this->id]
            );
            if ($select->first() > 0) {
                $highlightedAwards[] = $award;
            }
        }

        return $highlightedAwards;
	}
}
