<?php


namespace IPS\penh\modules\front\operations;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * mission
 */
class _mission extends \IPS\Content\Controller
{
    public static $contentModel = 'IPS\penh\Operation\Mission';

    public function execute(): void
    {
        parent::execute();
    }

    public function manage(): void
    {
        $mission = parent::manage();

        if ($mission === null) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations')->mission($mission);
    }

    public function edit(): void
    {
        try {
            $mission = static::$contentModel::loadAndCheckPerms(\IPS\Request::i()->id);
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        if (!$mission->canEdit()) {
            \IPS\Output::i()->error('node_error', '2F176/1', 403, '');
            return;
        }

        $form = $mission->buildEditForm();
        if ($values = $form->values()) {
            $mission->processForm($values);
            $mission->save();
            $mission->processAfterEdit($values);

            \IPS\Output::i()->redirect($mission->url());
            return;
        }

        $operation = $mission->container();

        \IPS\Output::i()->title = $mission->name;
        \IPS\Output::i()->breadcrumb[] = [$operation->url, $operation->name];
        \IPS\Output::i()->breadcrumb[] = [$mission->url, $mission->mapped('title')];
        \IPS\Output::i()->output = $form;
    }

    public function delete(): void
    {
        try {
            $mission = static::$contentModel::loadAndCheckPerms(\IPS\Request::i()->id);
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        if (!$mission->canDelete()) {
            \IPS\Output::i()->error('node_error', '2F176/1', 403, '');
            return;
        }

        $operation = $mission->container();
        if ($mission->calendar_event !== null) {
            $event = \IPS\calendar\Event::load($mission->calendar_event);
            $event->delete();
        }
        $mission->delete();

        \IPS\Output::i()->redirect($operation->url());
    }
}
