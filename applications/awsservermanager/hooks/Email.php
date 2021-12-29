//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class awsservermanager_hook_Email extends _HOOK_CLASS_
{
    /**
     * Get the class to use
     *
     * @param string $type See TYPE_* constants
     * @return    string
     */
    public static function classToUse($type)
    {
        if (\IPS\Settings::i()->aws_ses_enable) {
            return 'IPS\awsservermanager\Email\AwsSES';
        }

        if ($type === \IPS\Email::TYPE_BULK && \IPS\Settings::i()->aws_ses_enable_bulk_only) {
            return 'IPS\awsservermanager\Email\AwsSES';
        }

        return parent::classToUse($type);
    }
}
