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
    public function execute(): void
    {
        parent::execute();
    }

    public function manage(): void
    {
        \IPS\penh\Operation\Operation::loadIntoMemory();
        $operations = \IPS\penh\Operation\Operation::roots();


        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('operations_title');
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations')->operations($operations);
    }

    public function view(): void
    {
        try {
            $operation = \IPS\penh\Operation\Operation::loadAndCheckPerms(\IPS\Request::i()->id);
            if (!$operation->id) {
                throw new \OutOfRangeException('Operation not found');
            }
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        \IPS\Output::i()->title = $operation->name;

        $table = new \IPS\Helpers\Table\Content('IPS\penh\Operation\Mission', $operation->url(), [], $operation, null, null, false, false, null, false, false, false);
        $table->title = \IPS\Member::loggedIn()->language()->addToStack('missions');
        $table->limit = 3;

        $missionTable = (string)$table;
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations')->operation($operation, $missionTable);
    }

    public function add(): void
    {
        try {
            $operation = \IPS\penh\Operation\Operation::loadAndCheckPerms(\IPS\Request::i()->id);
            if (!$operation->id) {
                throw new \OutOfRangeException('Operation not found');
            }
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        \IPS\Output::i()->title = \IPS\Member::loggedIn()->language()->addToStack('mission_create');
        \IPS\Output::i()->output = \IPS\penh\Operation\Mission::create($operation);
    }
}
