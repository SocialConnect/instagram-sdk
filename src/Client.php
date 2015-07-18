<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\Instagram;

use InvalidArgumentException;
use SocialConnect\Common\HttpClient;

class Client extends \SocialConnect\Common\ClientAbstract
{
    use HttpClient;

    /**
     * @var string
     */
    protected $apiUrl = 'https://api.instagram.com/v1/';

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param $uri
     * @param array $parameters
     * @param bool|false $accessToken
     * @return bool|mixed
     * @throws \Exception
     */
    public function request($uri, array $parameters = array(), $accessToken = false)
    {
        $parameters['client_id'] = $this->appId;
        $parameters['access_token'] = $this->accessToken;

        foreach ($parameters as $key => $parameter) {
            if (is_array($parameter)) {
                $parameters[$key] = implode(',', $parameter);
            }
        }

        $response = $this->httpClient->request($this->apiUrl . $uri . '?' . http_build_query($parameters), []);
        if ($response) {
            if ($response->isServerError()) {
                throw new \Exception($response);
            }
            $body = $response->getBody();
            if ($body) {
                $json = json_decode($body);
                if ($json->data) {
                    return $json->data;
                }

                throw new \Exception($response);
            } else {
                throw new \Exception($response);
            }
        }
        return false;
    }

    protected function checkLimit($limit)
    {
        if (!is_int($limit) || $limit <= 0) {
            throw new InvalidArgumentException('$limit must be > 0, actual: ' . $limit);
        }
    }

    /**
     * @param $name
     * @param null $limit
     * @return bool|mixed
     * @throws \Exception
     */
    public function searchUser($name, $limit = null)
    {
        if (!is_null($limit)) {
            $this->checkLimit($limit);
        }

        return $this->request('users/search', ['name' => $name]);
    }

    /**
     * @param string $id
     * @return bool|mixed
     * @throws \Exception
     */
    public function getUser($id = 'self')
    {
        return $this->request('users/' . $id);
    }

    /**
     * See the authenticated user's feed.
     *
     * @link https://instagram.com/developer/endpoints/users/#get_users_feed
     *
     * @param integer|null $limit
     * @param integer|null $minId
     * @param integer|null $maxId
     * @return bool|object
     * @throws \Exception
     */
    public function getUserFeed($limit = null, $minId = null, $maxId = null)
    {
        $parameters = [];
        if ($limit !== null) {
            $this->checkLimit($limit);
            $parameters['count'] = $limit;
        }

        if ($minId !== null) {
            $parameters['min_id'] = $minId;
        }

        if ($maxId !== null) {
            $parameters['min_id'] = $maxId;
        }

        return $this->request('users/self/feed');
    }

    /**
     * @link https://instagram.com/developer/endpoints/users/#get_users_media_recent
     *
     * @param integer $id
     * @param null $limit
     * @param array $parameters
     * @return bool|mixed
     * @throws \Exception|\InvalidArgumentException
     */
    public function getUserMediaRecent($id = 1, array $parameters = [], $limit = null)
    {
        if (!is_int($id)) {
            throw new InvalidArgumentException('$id must be an integer type');
        }

        if (!is_null($limit)) {
            $this->checkLimit($limit);
        }

        return $this->request('users/' . $id . '/media/recent', [], true);
    }


    /**
     * @link https://instagram.com/developer/endpoints/users/#get_users_feed_liked
     *
     * @param integer|null $limit
     * @return bool|mixed
     * @throws \Exception
     */
    public function getUserMediaLiked($limit = null)
    {
        if (!is_null($limit)) {
            $this->checkLimit($limit);
        }

        return $this->request('users/self/media/liked', [], true);
    }


    public function getUserLikes($limit = 0)
    {
        if (!is_null($limit)) {
            $this->checkLimit($limit);
        }
    }

    public function getUserFollows($id = 'self', $limit = null)
    {
        if (!is_null($limit)) {
            $this->checkLimit($limit);
        }
    }

    public function getUserFollower($id = 'self', $limit = null)
    {
        if (!is_null($limit)) {
            $this->checkLimit($limit);
        }
    }

    /**
     * @link https://instagram.com/developer/endpoints/media/#get_media
     * Get information about a media object. The returned type key will allow you to differentiate between image and video media.
     *
     * @param $mediaId
     * @return bool|mixed
     * @throws \Exception
     */
    public function getMedia($mediaId)
    {
        return $this->request('media/' . $mediaId, [], true);
    }

    /**
     * @link https://instagram.com/developer/endpoints/media/#get_media_popular
     * Get a list of what media is most popular at the moment. Can return mix of image and video types.
     *
     * @return bool|mixed
     * @throws \Exception
     */
    public function getMediaPopular()
    {
        return $this->request('media/popula', [], true);
    }
}
