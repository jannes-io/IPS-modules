<?php

namespace IPS\awsservermanager\Email;

use Aws\Exception\AwsException;
use Aws\Ses\SesClient;

/* To prevent PHP errors (extending class does not exist) revealing path */
if (!\defined('\IPS\SUITE_UNIQUE_KEY')) {
    header((isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0') . ' 403 Forbidden');
    exit;
}

require_once \IPS\ROOT_PATH . '/applications/awsservermanager/system/3rd_party/aws-autoloader.php';

class _AwsSES extends \IPS\Email
{
    /**
     * Send the email
     *
     * @param mixed $to The member or email address, or array of members or email addresses, to send to
     * @param mixed $cc Addresses to CC (can also be email, member or array of either)
     * @param mixed $bcc Addresses to BCC (can also be email, member or array of either)
     * @param mixed $fromEmail The email address to send from. If NULL, default setting is used
     * @param mixed $fromName The name the email should appear from. If NULL, default setting is used
     * @param array $additionalHeaders The name the email should appear from. If NULL, default setting is used
     * @return    void
     * @throws    \IPS\Email\Outgoing\Exception
     */
    public function _send($to, $cc = [], $bcc = [], $fromEmail = null, $fromName = null, $additionalHeaders = [])
    {
        $region = \IPS\Settings::i()->aws_ses_region;
        $accessKeyId = \IPS\Settings::i()->aws_ses_access_key_id;
        $accessKeySecret = \IPS\Settings::i()->aws_ses_access_key_secret;

        if (empty($region) || empty($accessKeyId) || empty($accessKeySecret)) {
            throw new \IPS\Email\Outgoing\Exception('aws_ses_empty_credentials');
        }

        $sesClient = new SesClient([
            'region' => $region,
            'version' => '2010-12-01',
            'credentials' => [
                'key' => $accessKeyId,
                'secret' => $accessKeySecret,
            ],
        ]);

        $subject = $this->compileSubject(static::_getMemberFromRecipients($to));
        $htmlBody = $this->compileContent('html', static::_getMemberFromRecipients($to));
        $textBody = $this->compileContent('plaintext', static::_getMemberFromRecipients($to));

        $recipients = [];
        foreach (['to', 'cc', 'bcc'] as $type) {
            $typeRecipients = \is_array($$type) ? $$type : [$$type];
            foreach ($typeRecipients as $recipient) {
                if ($recipient instanceof \IPS\Member) {
                    $recipients[] = $recipient->email;
                } else {
                    $recipients[] = $recipient;
                }
            }
        }

        try {
            $result = $sesClient->sendEmail([
                'Destination' => [
                    'ToAddresses' => $recipients,
                ],
                'ReplyToAddresses' => [$fromEmail],
                'Source' => $fromName !== null ? "$fromName <{$fromEmail}>" : $fromEmail,
                'Message' => [
                    'Body' => [
                        'Html' => [
                            'Charset' => 'UTF-8',
                            'Data' => $htmlBody,
                        ],
                        'Text' => [
                            'Charset' => 'UTF-8',
                            'Data' => $textBody,
                        ],
                    ],
                    'Subject' => [
                        'Charset' => 'UTF-8',
                        'Data' => $subject,
                    ],
                ],
            ]);
           if (empty($result['MessageId'])) {
               throw new \IPS\Email\Outgoing\Exception('Something went wrong sending email through SES: ' . \json_encode($result));
           }
        } catch (AwsException $e) {
            throw new \IPS\Email\Outgoing\Exception($e->getMessage());
        }
    }
}
