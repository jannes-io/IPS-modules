<?php

namespace IPS\thirdinf\api;

use IPS\Api\Controller;
use IPS\Api\Response;
use IPS\Data\Store;
use IPS\Http\Url;
use IPS\Request;

class _twitchstream extends Controller
{
    /**
     * GET /thirdinf/twitchstream
     * Get a list of combat units
     *
     * @apiparam    string  user_login
     * @return      Response
     */
    public function GETindex()
    {
        $twitchUsername = Request::i()->user_login;
        $twitchEndpoint = 'https://api.twitch.tv/helix/';

        $streamEndpoint = "{$twitchEndpoint}streams?user_login=$twitchUsername";
        $response = $this->doAuthorizedCall($streamEndpoint);

        if (empty($response['data'][0])) {
            return new Response(200, []);
        }

        $stream = $response['data'][0];
        if (empty($stream['game_id'])) {
            return new Response(200, $stream);
        }

        $gameEndpoint = "{$twitchEndpoint}games?id={$stream['game_id']}";
        $gameInfo = $this->doAuthorizedCall($gameEndpoint);

        $stream['game_name'] = isset($gameInfo['data'][0]) ? $gameInfo['data'][0]['name'] : '';
        return new Response(200, $stream);
    }

    /**
     * @param string $endpoint
     * @return array
     */
    private function doAuthorizedCall(string $endpoint)
    {
        $token = $this->getToken();

        return Url::external($endpoint)
            ->request()
            ->setHeaders([
                'Authorization' => "Bearer {$token['access_token']}",
                'client-id' => 'ny0lf0oe3bxdcno8oyxr1ujsswkifb',
            ])
            ->get()
            ->decodeJson();
    }

    /**
     * @return array
     */
    private function getToken()
    {
        if (!isset(Store::i()->twitch_token)) {
            return $this->requestToken();
        }

        $token = Store::i()->twitch_token;
        if ($token['expires_at'] < time()) {
            return $this->requestToken();
        }
        return $token;
    }

    /**
     * @return array
     */
    private function requestToken()
    {
        $tokenEndpoint = 'https://id.twitch.tv/oauth2/token';
        $clientId = 'ny0lf0oe3bxdcno8oyxr1ujsswkifb';
        $clientSecret = '6dao96e3hwevd3um2xlxes0u4q8gyt';
        $grantType = 'client_credentials';

        $token = Url::external($tokenEndpoint)
            ->request()
            ->post([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => $grantType
            ])
            ->decodeJson();
        $token['expires_at'] = time() + $token['expires_in'];
        Store::i()->twitch_token = $token;
        return $token;
    }
}
