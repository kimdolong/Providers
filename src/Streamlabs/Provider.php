<?php

namespace SocialiteProviders\Streamlabs;

use GuzzleHttp\RequestOptions;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class Provider extends AbstractProvider
{
    public const IDENTIFIER = 'STREAMLABS';

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://streamlabs.com/api/v1.0/authorize',
            $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://streamlabs.com/api/v1.0/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            'https://streamlabs.com/api/v1.0/user',
            [
                RequestOptions::QUERY => [
                    'access_token' => $token,
                ],
            ]
        );

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $mainAccount = $user['streamlabs'];

        return (new User())->setRaw($user)->map([
            'id'        => $mainAccount['id'],
            'name'      => $mainAccount['display_name'],
            'accounts'  => [
                'twitch'    => $user['twitch'] ?? null,
                'youtube'   => $user['youtube'] ?? null,
                'facebook'  => $user['facebook'] ?? null,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code',
        ]);
    }
}
