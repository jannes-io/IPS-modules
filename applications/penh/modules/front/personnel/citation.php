<?php


namespace IPS\penh\modules\front\personnel;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * citation
 */
class _citation extends \IPS\Dispatcher\Controller
{
    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        parent::execute();
    }

    /**
     * @return    void
     */
    protected function manage()
    {
        $id = \IPS\Request::i()->id;
        $serviceRecord = \IPS\perscom\Records\Service::load($id);

        $award = \call_user_func([$serviceRecord->item_class, 'load'], $serviceRecord->item_id);

        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('personnel')->citation($serviceRecord, $award);
    }
}
