<?php

namespace IPS\thirdinf\api;

use DateInterval;
use DateTime;
use Exception;
use IPS\_Member;
use IPS\Api\Controller;
use IPS\Api\Response;
use IPS\Db\_Select;
use IPS\Request;
use IPS\thirdinf\_Vote;

class _clanlist extends Controller
{
    /**
     * GET /thirdinf/clanlist
     * Get a list of combat units
     *
     * @reqapiparam     int     member_id
     * @return          Response
     * @throws Exception
     */
    public function GETindex()
    {
        $id = Request::i()->member_id;

        /** @var _Member $member */
        $member = \IPS\Member::load($id);
        if ($member->member_id === null) {
            return new Response(400, "member with id $id does not exist");
        }

        $lastMonth = (new DateTime())->sub(new DateInterval('P29D'));

         /** @var _Select $select */
         $select = \IPS\Db::i()->select(
             'id, `timestamp`',
             'thirdinf_clanlist_votes',
             "member_id = {$member->member_id} AND `timestamp` > {$lastMonth->getTimestamp()}"
         );

         $table = iterator_to_array($select);

         $dailyHashMap = array_reduce($table, static function ($acc, $row) {
             $day = date('Y-m-d', $row['timestamp']);
             if (!isset($acc[$day])) {
                 $acc[$day] = 1;
             } elseif ($acc[$day] < 2) {
                 $acc[$day]++;
             }
             return $acc;
         }, []);

        return new Response(200, array_sum($dailyHashMap));
    }

    /**
     * POST /thirdinf/clanlist
     * Get a list of combat units
     *
     * @reqapiparam    int     member_id
     * @return      Response
     * @throws Exception
     */
    public function POSTindex()
    {
        $id = Request::i()->member_id;

        /** @var _Member $member */
        $member = \IPS\Member::load($id);
        if ($member->member_id === null) {
            return new Response(400, "member with id $id does not exist");
        }

        /** @var _Vote $vote */
        $vote = new \IPS\thirdinf\Vote($member);
        $vote->save();

        return new Response(200, $vote->id);
    }
}
