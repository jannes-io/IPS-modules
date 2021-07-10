<?php

namespace IPS\penh\modules\front\operations;

use IPS\Helpers\Form;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

/**
 * afteractionreport
 */
class _afteractionreport extends \IPS\Dispatcher\Controller
{
    public function execute(): void
    {
        parent::execute();
        \IPS\Output::i()->cssFiles = array_merge(\IPS\Output::i()->cssFiles, \IPS\Theme::i()->css('personnel/ranks.css', 'perscom', 'front'));
        \IPS\Output::i()->cssFiles = array_merge(\IPS\Output::i()->cssFiles, \IPS\Theme::i()->css('afteractionreport/form.css', 'penh', 'front'));

        \IPS\Output::i()->jsFiles = array_merge(\IPS\Output::i()->jsFiles, \IPS\Output::i()->js('front_afteractionreport.js', 'penh', 'front'));
    }

    protected function manage(): void
    {
        try {
            $aar = \IPS\penh\Operation\AfterActionReport::loadAndCheckPerms(\IPS\Request::i()->id);
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        $mission = $aar->item();
        $operation = $mission->container();
        $combatUnit = $aar->combatUnit();

        \IPS\Output::i()->breadcrumb[] = [$operation->url(), $operation->name];
        \IPS\Output::i()->breadcrumb[] = [$mission->url(), $mission->name];
        \IPS\Output::i()->breadcrumb[] = [null, $aar->title()];

        $attendance = [];
        foreach (($aar->getAttendance() ?? []) as $soldierId => $status) {
            try {
                $soldier = \IPS\perscom\Personnel\Soldier::load($soldierId);
            } catch (\Exception $ex) {
                // don't care for now
                continue;
            }
            if (!isset($attendance[$status])) {
                $attendance[$status] = [];
            }
            $attendance[$status][] = $soldier;
        }

        \IPS\Output::i()->title = $combatUnit->position . ': ' . $mission->name;
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations')->afterActionReport($aar, $attendance);
    }

    public function add(): void
    {
        try {
            $mission = \IPS\penh\Operation\Mission::loadAndCheckPerms(\IPS\Request::i()->id);
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        if (!$mission->can('add')) {
            \IPS\Output::i()->error('node_error', '2F176/1', 403, '');
            return;
        }

        $form = \IPS\penh\Operation\AfterActionReport::buildForm($mission);
        if ($values = $form->values()) {
            $aar = \IPS\penh\Operation\AfterActionReport::createFromForm($values, $mission);
            $aar->save();
            $aar->processAfterCreate();
            \IPS\Output::i()->redirect($aar->url());
            return;
        }

        $status = \IPS\penh\Operation\AfterActionReport::availableStatus();

        $operation = $mission->container();
        $langCreate = \IPS\Member::loggedIn()->language()->addToStack('aar_create');
        \IPS\Output::i()->breadcrumb[] = [$operation->url(), $operation->name];
        \IPS\Output::i()->breadcrumb[] = [$mission->url(), $mission->name];
        \IPS\Output::i()->breadcrumb[] = [null, $langCreate];

        \IPS\Output::i()->title = $langCreate;
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations')->afterActionReportForm($form, json_encode($status, true));
    }

    public function edit(): void
    {
        try {
            $aar = \IPS\penh\Operation\AfterActionReport::loadAndCheckPerms(\IPS\Request::i()->id);
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        if (!$aar->canEdit()) {
            \IPS\Output::i()->error('node_error', '2F176/1', 403, '');
            return;
        }

        $mission = $aar->item();
        $form = \IPS\penh\Operation\AfterActionReport::buildForm($mission, $aar);
        if ($values = $form->values()) {
            $aar->processForm($values);
            $aar->save();
            \IPS\Output::i()->redirect($aar->url());
            return;
        }

        $status = \IPS\penh\Operation\AfterActionReport::availableStatus();

        $operation = $mission->container();
        \IPS\Output::i()->breadcrumb[] = [$operation->url(), $operation->name];
        \IPS\Output::i()->breadcrumb[] = [$mission->url(), $mission->name];
        \IPS\Output::i()->breadcrumb[] = [$aar->url(), $aar->title()];

        \IPS\Output::i()->title = $aar->title();
        \IPS\Output::i()->output = \IPS\Theme::i()->getTemplate('operations')->afterActionReportForm($form, json_encode($status, true));
    }

    public function delete(): void
    {
        try {
            $aar = \IPS\penh\Operation\AfterActionReport::loadAndCheckPerms(\IPS\Request::i()->id);
        } catch (\Exception $ex) {
            \IPS\Output::i()->error('node_error', '2F176/1', 404, '');
            return;
        }

        if (!$aar->canDelete()) {
            \IPS\Output::i()->error('node_error', '2F176/1', 403, '');
            return;
        }

        $mission = $aar->item();
        $aar->delete();
        \IPS\Output::i()->redirect($mission->url());
    }

    public function personnel(): void
    {
        try {
            $personnel = \IPS\perscom\Personnel\Soldier::roots('view', null, [
                ['personnel_combat_unit = ?', \IPS\Request::i()->id]
            ]);
        } catch (\Exception $e) {
            \IPS\Output::i()->json('[]', 404);
            return;
        }

        \IPS\Output::i()->json(array_values(array_map(static function ($soldier) {
            $rank = $soldier->get_rank();

            return [
                'id' => $soldier->id,
                'firstname' => $soldier->firstname,
                'lastname' => $soldier->lastname,
                'rank' => [
                    'order' => $rank->order,
                    'image_small' => $rank->image_smal,
                    'icon' => $rank->icon,
                ]
            ];
        }, $personnel)));
    }
}
