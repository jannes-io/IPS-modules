//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    exit;
}

class penh_hook_SoldierFrontModule extends _HOOK_CLASS_
{
    protected function service(\IPS\perscom\Personnel\Soldier $soldier)
    {
        $table = new \IPS\Helpers\Table\Db(\IPS\perscom\Records\Service::$databaseTable, \IPS\Http\Url::internal('app=perscom&module=personnel&controller=soldier&tab=service&id=' . $soldier->id, 'front', 'soldier_service'));
        $table->joins = [
            ['select' => 'd.documents_title', 'from' => [\IPS\perscom\Documents\Document::$databaseTable, 'd'], 'where' => "d.documents_id=perscom_service_records.service_records_document"],
            ['select' => 'c.award_citation_text', 'from' => [\IPS\penh\Records\ServiceRecordCitation::$databaseTable, 'c'], 'where' => 'c.award_citation_service_record_id=perscom_service_records.service_records_id'],
        ];
        $table->include = ['service_records_date', 'service_records_text'];
        $table->limit = \IPS\Settings::i()->perscom_settings_personnel_service_records_table;
        $table->tableTemplate = [\IPS\Theme::i()->getTemplate('tables', 'perscom', 'front'), 'table'];
        $table->rowsTemplate = [\IPS\Theme::i()->getTemplate('tables', 'perscom', 'front'), 'rows'];
        $table->rowClasses = ['service_records_text' => ['ipsTable_wrap']];
        $table->sortDirection = $table->sortDirection ?: 'desc';
        $table->sortBy = $table->sortBy ?: 'service_records_date';
        $table->paginationKey = 'serviceRecordsPage';
        $table->resortKey = 'serviceRecords';
        $table->extra['noRowsLangKey'] = 'perscom_table_no_service_records';
        $table->extra['popupTitle'] = 'perscom_service_records';
        $table->extra['addPadding'] = true;

        // Compose where statement
        $table->where[] = ['service_records_soldier=?', $soldier->id];

        // Table search
        $table->quickSearch = function ($val) {
            return ['( LOWER(service_records_text) LIKE CONCAT( \'%\', ?, \'%\' ) )', $val];
        };

        // Column widths
        $table->widths = [
            'service_records_date' => '15',
            'service_records_text' => '75',
        ];

        // Format row data
        $table->parsers = [
            'service_records_date' => function ($value, $row) {
                return \IPS\DateTime::ts($row['service_records_date'])->html();
            },
        ];

        // Row Buttons
        $table->rowButtons = function ($row) use ($soldier) {
            // Create array to return
            $return = [];

            // If we have a document
            if (isset($row['service_records_document']) and $row['service_records_document'] != 0 and \IPS\Application\Module::get('perscom', 'documents', 'front')->visible) {
                // Load the document to see if they have permissions to view it
                try {
                    // Get document
                    \IPS\perscom\Documents\Document::loadAndCheckPerms($row['service_records_document']);

                    // Add document button
                    $return['view'] = [
                        'icon' => 'search',
                        'title' => 'perscom_service_record_view_document',
                        'link' => \IPS\Http\Url::internal('app=perscom&module=documents&controller=document&do=view&soldier=' . $soldier->id . '&id=' . $row['service_records_document'] . '&record=' . $row['service_records_id'] . '&type=service', 'front', 'document_service'),
                        'data' => ['ipsdialog' => '', 'ipsdialog-title' => $row['documents_title']],
                    ];
                } // Unable to find document or no permissions
                catch (\OutOfRangeException $e) {
                }
            }

            if (!empty($row['award_citation_text'])) {
                $return['view_citation'] = [
                    'icon' => 'comments',
                    'title' => 'penh_service_record_citation',
                    'link' => \IPS\Http\Url::internal('app=penh&module=personnel&controller=citation&id=' . $row['service_records_id']),
                    'data' => ['ipsdialog' => ''],
                ];
            }

            // Return the buttons
            return $return;
        };

        // Create filters array
        $filters = [];

        // Loop through status
        foreach (\IPS\perscom\Records\Service::types() as $key => $status) {
            // Add filter to array
            $filters[$status] = '( service_records_action=' . $key . ' )';
        }

        // Add fitlers
        $table->filters = array_reverse($filters);

        // If an ajax request
        if (\IPS\Request::i()->isAjax()) {
            // Send the output
            \IPS\Output::i()->sendOutput(\IPS\Theme::i()->getTemplate('templates', 'perscom', 'global')->blankTemplate($table), 200, 'text/html', \IPS\Output::i()->httpHeaders);
        } // Not an ajax request
        else {
            // Return the table
            return (string)$table;
        }
    }

}
