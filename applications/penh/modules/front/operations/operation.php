<?php


namespace IPS\penh\modules\front\operations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * operation
 */
class _operation extends \IPS\Content\Controller
{
    protected static $contentModel = '\IPS\penh\Operation\Operation';

    /**
     * Execute
     *
     * @return    void
     */
    public function execute()
    {
        parent::execute();
    }

    public function manage()
    {
        \IPS\penh\Operation\Operation::loadIntoMemory();
        $operations = \IPS\penh\Operation\Operation::roots();

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('penh_operations_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations', 'penh', 'front')->operations($operations);
    }
}
