<?php

namespace IPS\thirdinf;

use DateTime;
use IPS\_Member;

class _Vote extends \IPS\Patterns\ActiveRecord
{
    public static $databaseTable = 'thirdinf_clanlist_votes';
    public static $databasePrefix = '';

    /**
     * \IPS\thirdinf\Clanlist\Vote constructor.
     * @param _Member $member
     */
    public function __construct($member)
    {
        $this->member_id = $member->member_id;
        $this->timestamp = time();
    }
}
