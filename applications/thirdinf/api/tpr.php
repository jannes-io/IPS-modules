<?php

namespace IPS\thirdinf\api;

use DateInterval;
use DateTime;
use Exception;
use IPS\Api\Response;
use IPS\Api\Controller;

class _tpr extends Controller
{
    private const FORM_TPR = 2;

    private const STATUS_PENDING = 1;
    private const STATUS_APPROVED = 2;

    /**
     * GET /thirdinf/tpr
     * Gets a list of all pending/approved TPRs in the last 72 hours
     *
     * @return Response
     * @throws Exception
     */
    public function GETindex()
    {
        $threeDaysAgo = (new DateTime())->sub(new DateInterval('P3D'));

        $select = \IPS\Db::i()->select(
            'requests_soldier AS soldierId, requests_status AS status',
            'perscom_personnel_action_requests',
            'requests_form = ' . self::FORM_TPR . ' and requests_date > ' . $threeDaysAgo->getTimestamp()
        );

        $result = iterator_to_array($select);
        $map = array_reduce($result, static function ($acc, $row) {
            $status = $row['status'];

            if ($status === self::STATUS_PENDING) {
                $acc['pending'][] = $row['soldierId'];
            } elseif ($status === self::STATUS_APPROVED) {
                $acc['approved'][] = $row['soldierId'];
            }

            return $acc;
        }, ['pending' => [], 'approved' => []]);

        return new Response(200, $map);
    }
}
