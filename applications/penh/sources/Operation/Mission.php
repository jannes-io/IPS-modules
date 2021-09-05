<?php

namespace IPS\penh\Operation;

use IPS\Helpers\Form;

/**
 * Class _Mission
 * @package IPS\penh\Operation
 *
 * @property int $id
 * @property int $operation_id
 * @property string $name
 * @property int $start
 * @property int $end
 * @property string $content
 * @property bool $create_combat_record
 * @property string $combat_record_entry
 * @property bool $create_calendar_event
 * @property int $calendar_event_id
 */
class _Mission extends \IPS\Content\Item implements
    \IPS\Content\Permissions,
    \IPS\Content\Views
{
    protected static $multitons;

    public static $application = 'penh';
    public static $module = 'operations';
    public static $databaseTable = 'penh_missions';
    public static $databasePrefix = 'mission_';
    public static $databaseColumnId = 'id';
    public static $title = 'name';
    public static $containerNodeClass = 'IPS\penh\Operation\Operation';
    public static $commentClass = 'IPS\penh\Operation\AfterActionReport';
    public static $firstCommentRequired = false;

    public static $databaseColumnMap = [
        'author' => 'author',
        'author_name' => 'author_name',
        'container' => 'operation_id',
        'date' => 'start',
        'title' => 'name',
        'views' => 'views',
        'content' => 'content',
        'num_comments' => 'num_aars',
    ];

    public function get__title(): string
    {
        return $this->name;
    }

    public function url($action = null)
    {
        return \IPS\Http\Url::internal('app=penh&module=operations&controller=mission&id=' . $this->id);
    }

    public function start()
    {
        return \IPS\DateTime::ts($this->start);
    }

    public function end()
    {
        return \IPS\DateTime::ts($this->end);
    }

    public static function formElements($item = null, \IPS\Node\Model $container = null)
    {
        $form = parent::formElements($item, $container);
        $form['mission_start'] = new Form\Date('mission_start', $item->start ?? null, true, ['time' => true]);
        $form['mission_end'] = new Form\Date('mission_end', $item->end ?? null, true, ['time' => true]);

        if ($item === null || !$item->id) {
            if (static::isCalendarEnabled()) {
                $form['mission_create_event'] = new Form\Checkbox('mission_create_event', null, false);
            }
            if (\IPS\Settings::i()->penh_combat_record_entry_enable) {
                $form['mission_create_combat_record_entry'] = new Form\Checkbox('mission_create_combat_record_entry', null, false);
                $form['mission_combat_record_entry '] = new Form\Text('mission_combat_record_entry', null, false);
            }
        }

        $form['mission_content'] = new Form\Editor('mission_content', $item->content ?? \IPS\Settings::i()->penh_missions_template, true, [
            'app' => 'penh',
            'key' => 'Operation',
            'autoSaveKey' => 'penh_mission_content-' . ($item->id ?? 'new')
        ]);

        return $form;
    }

    public function processForm($values): void
    {
        parent::processForm($values);
        $this->start = $values['mission_start']->getTimestamp();
        $this->end = $values['mission_end']->getTimestamp();
        $this->content = $values['mission_content'];
    }

    public function processAfterCreate($comment, $values): void
    {
        $this->create_calendar_event = $this->create_calendar_event ?: (bool)($values['mission_create_event'] ?? false);
        if ($this->create_calendar_event && $this->calendar_event_id === null && static::isCalendarEnabled()) {
            $calendarId = \IPS\Settings::i()->penh_calendar_node;
            $calendar = \IPS\calendar\Calendar::load($calendarId);

            $eventValues = [
                'event_container' => $calendarId,
                'event_dates' => [
                    'start_date' => $values['mission_start']->localeDate(),
                    'start_time' => $values['mission_start']->localeTime(),
                    'end_date' => $values['mission_end']->localeDate(),
                    'end_time' => $values['mission_end']->localeTime(),
                    'event_timezone' => \IPS\Member::loggedIn()->timezone,
                    'all_day' => false,
                    'repeat_end' => 'never',
                ],
                'event_content' => "<p><a href='{$this->url()}'>{$this->name}</a></p>" . $values['mission_content'],
                'event_cover_photo' => null,
                'event_location' => null,
                'event_title' => $this->name,
                'event_title_seo' => $this->name,
            ];

            $event = \IPS\calendar\Event::createFromForm($eventValues, $calendar);
            $this->calendar_event_id = $event->id;
        }

        $this->create_combat_record = $this->create_combat_record ?: (bool)($values['mission_create_combat_record_entry'] ?? false);
        if ($this->create_combat_record) {
            $this->combat_record_entry = $values['mission_combat_record_entry'];
        }

        $this->save();
        $this->sendNotification();
    }

    protected static function isCalendarEnabled(): bool
    {
        return \IPS\Application::load('calendar')->enabled && \IPS\Settings::i()->penh_calendar_enable && \IPS\Settings::i()->penh_calendar_node;
    }

    protected function sendNotification(): void
    {
        if (!\IPS\Settings::i()->penh_missions_notification_enable) {
            return;
        }
        $notification = new \IPS\Notification(\IPS\Application::load('penh'), 'missions', $this, [$this]);

        $statusToReceive = \IPS\Settings::i()->penh_missions_notification_status;

        $where = empty($statusToReceive) ? null : "perscom_personnel.personnel_status IN ({$statusToReceive})";

        $recipientQuery = \IPS\Db::i()->select(
            \IPS\Member::$databaseTable . '.*',
            \IPS\Member::$databaseTable,
            $where
        );

        if ($where !== null) {
            $recipientQuery->join('perscom_personnel', 'perscom_personnel.personnel_member_id = core_members.member_id', 'LEFT');
        }

        foreach ($recipientQuery as $recipient) {
            $notification->recipients->attach(\IPS\Member::constructFromData($recipient));
        }
        $notification->send();
    }
}
