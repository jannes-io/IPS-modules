//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class penh_hook_ActivityTracker extends _HOOK_CLASS_
{
    protected function report()
    {
        $soldier = \IPS\Member::loggedIn()->isSoldier();

        $pendingReports = \iterator_to_array(\IPS\Db::i()->select(
            'reportin_id',
            'perscom_reportin',
            [
                'reportin_soldier = ? and reportin_status = ?',
                $soldier->id,
                \IPS\perscom\Records\ReportIn::REPORT_IN_STATUS_PENDING,
            ],
            'reportin_date DESC',
            1
        ));

        if (!empty($pendingReports)) {
            $reportInId = $pendingReports[0];
            $url = \IPS\Http\Url::internal('app=perscom&module=activitytracker&controller=activitytracker&do=update&record=')
                ->setQueryString(['record' => $reportInId]);
            \IPS\Output::i()->redirect($url);
            return;
        }

        parent::report();
    }
}
