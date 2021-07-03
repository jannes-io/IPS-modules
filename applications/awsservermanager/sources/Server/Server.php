<?php

namespace IPS\awsservermanager;

use Aws\Ec2\Ec2Client;
use DateTime;
use IPS\awsservermanager\Server\SteamServerInfo;
use IPS\Helpers\Form;
use IPS\Http\Url;
use IPS\Settings;

require_once \IPS\ROOT_PATH . '/applications/awsservermanager/system/3rd_party/aws-autoloader.php';

/**
 * Class _Server
 * @package IPS\awsservermanager
 * @property string $name
 * @property int $position
 * @property string $ip
 * @property int $port
 * @property string $domain
 * @property int $appid
 * @property int $steam_query_port
 * @property string $aws_instance_id
 * @property string $last_activity
 */
class _Server extends \IPS\Node\Model implements \IPS\Node\Permissions
{
    private const STEAM_ENDPOINT = 'https://api.steampowered.com/IGameServersService/GetServerList/v1/';

    public static $nodeTitle = 'awsservermanager_servers';
    public static $databaseTable = 'awsservermanager_servers';
    public static $databaseColumnOrder = 'position';
    public static $permApp = 'awsservermanager';
    public static $permType = 'servers';
    public static $permissionLangPrefix = 'awsservermanager_server';
    public static $permissionMap = [
        'view' => 'view',
        'start' => 2,
        'stop' => 3,
    ];
    public static $seoTitleColumn = 'name';

    protected static $multitons;
    protected static $restrictions = [
        'app' => 'awsservermanager',
        'module' => 'servers',
        'prefix' => 'server_'
    ];

    /** @var null|SteamServerInfo */
    protected $steamInfoCache = null;

    /** @var null|Ec2Client */
    protected $ecClientCache = null;

    /**
     * @param Form $form
     */
    public function form(&$form): void
    {
        $form->addHeader('awsservermanager_servers_settings');
        $form->add(new Form\Text('name', $this->name, false, [], null, null, null, 'name'));
        $form->add(new Form\Number('position', $this->position, false, [], null, null, null, 'position'));
        $form->add(new Form\Text('ip', $this->ip, false, ['placeholder' => '0.0.0.0'], null, null, null, 'ip'));
        $form->add(new Form\Number('port', $this->port, false, ['placeholder' => '2302'], null, null, null, 'port'));
        $form->add(new Form\Text('domain', $this->domain, false, ['placeholder' => 'server.example.org'], null, null, null, 'domain'));
        $form->add(new Form\Number('appid', $this->appid, false, ['placeholder' => '107410'], null, null, null, 'appid'));
        $form->add(new Form\Number('steam_query_port', $this->steam_query_port, false, ['placeholder' => '2303'], null, null, null, 'steam_query_port'));
        $form->add(new Form\Text('aws_instance_id', $this->aws_instance_id, false, ['placeholder' => 'i-XX..X'], null, null, null, 'aws_instance_id'));
    }

    public function formatFormValues($values)
    {
        if (!$this->id) {
            $this->last_activity = (new DateTime())->format('Y-m-d H:i:s');
            $this->save();
        }
        return $values;
    }

    protected function get__title(): string
    {
        return "{$this->name} ({$this->ip}:{$this->port})";
    }

    public function getState(): string
    {
        $result = $this->createEC2Client()->describeInstanceStatus([
            'InstanceIds' => [$this->aws_instance_id]
        ]);

        return $result['InstanceStatuses'][0]['InstanceState']['Name'] ?? 'stopped';
    }

    public function start(): void
    {
        $this->createEC2Client()->startInstances([
            'InstanceIds' => [$this->aws_instance_id]
        ]);

        $this->last_activity = (new DateTime())->format('Y-m-d H:i:s');
        $this->save();
    }

    public function stop(): void
    {
        $this->createEC2Client()->stopInstances([
            'InstanceIds' => [$this->aws_instance_id]
        ]);
    }

    public function reboot(): void
    {
        $this->createEC2Client()->rebootInstances([
            'InstanceIds' => [$this->aws_instance_id]
        ]);

        $this->last_activity = (new DateTime())->format('Y-m-d H:i:s');
        $this->save();
    }

    protected function createEC2Client(): Ec2Client
    {
        if ($this->ecClientCache !== null) {
            return $this->ecClientCache;
        }

        $this->ecClientCache = new Ec2Client([
            'region' => Settings::i()->aws_region,
            'version' => '2016-11-15',
            'credentials' => [
                'key' => Settings::i()->aws_access_key_id,
                'secret' => Settings::i()->aws_access_key_secret
            ]
        ]);
        return $this->ecClientCache;
    }

    public function getSteamInfo(): ?SteamServerInfo
    {
        if ($this->steamInfoCache !== null) {
            return $this->steamInfoCache;
        }

        $endpoint = self::STEAM_ENDPOINT . "?filter=\appid\\{$this->appid}\addr\\{$this->ip}:{$this->steam_query_port}";
        $endpoint .= '&key=' . Settings::i()->steam_api_key;
        $response = Url::external($endpoint)->request()->get()->decodeJson();

        if (empty($response['response']['servers'][0])) {
            return null;
        }

        $serverInfoArr = $response['response']['servers'][0];
        $this->steamInfoCache = new SteamServerInfo($serverInfoArr);
        return $this->steamInfoCache;
    }

    public function url()
    {
        return \IPS\Http\Url::internal('app=awsservermanager&module=servers&controller=servers', 'front');
    }
}
