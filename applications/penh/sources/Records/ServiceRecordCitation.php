<?php

namespace IPS\penh\Records;

class _ServiceRecordCitation extends \IPS\Patterns\ActiveRecord
{
    public static $databaseTable = 'penh_award_citations';
    public static $databasePrefix = 'award_citation_';
    public static $databaseColumnId = 'service_record_id';
}
