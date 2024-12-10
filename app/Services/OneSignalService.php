<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    const API_URL = "https://onesignal.com/api/v1";
    const ENDPOINT_NOTIFICATIONS = "/notifications";

    protected static $appId;
    protected static $restApiKey;
    protected static $additionalParams = [];

    /**
     * Load the configuration and initialize appId and restApiKey.
     */
    protected static function init()
    {
        $config = config('services.onesignal');
        self::$appId = $config['app_id'];
        self::$restApiKey = $config['api_key'];
    }

    private static function setHeaders()
    {
        return [
            'Authorization' => 'Basic ' . self::$restApiKey,
            'Content-Type' => 'application/json'
        ];
    }

    public static function addParams($keyOrArray, $value = null)
    {
        if (is_array($keyOrArray)) {
            self::$additionalParams = array_merge(self::$additionalParams, $keyOrArray);
        } else {
            self::$additionalParams[$keyOrArray] = $value;
        }
    }

    private static function pushParams($params = [], $title, $message, $imageUrl = null, $url = null)
    {
        return array_filter(array_merge([
            'app_id' => self::$appId,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'big_picture' => $imageUrl,
            'url' => $url,
            'priority' => 10
        ], $params));
    }

    public static function sendPushByUserId($userId, $title, $message, $imageUrl = null, $url = null)
    {
        self::init(); // Ensure initialization
        $params = [
            'filters' => [["field" => "tag", "key" => 'user_id', "relation" => "=", "value" => $userId]],
        ];
        $params = self::pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return self::sendPush($params);
    }

    // TODO remove comment after app integration
    public static function sendPushByUserIds(array $userIds, $title, $message, $imageUrl = null, $url = null)
    {
        // return true;
        self::init(); // Ensure initialization
        $fields = [];
        foreach ($userIds as $uid) {
            $fields[] = [["field" => "tag", "key" => 'user_id', "relation" => "=", "value" => $uid]];
        }

        $params = [
            'filters' => [$fields],
        ];
        $params = self::pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return self::sendPush($params);
    }
    public static function sendPushByEmail($email, $title, $message, $imageUrl = null, $url = null)
    {
        self::init();
        $params = [
            'filters' => [["field" => "tag", "key" => 'user_email', "relation" => "=", "value" => $email]],
        ];
        $params = self::pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return self::sendPush($params);
    }

    // TODO remove comment after app integration
    public static function sendPushByPhone($phone, $title, $message, $imageUrl = null, $url = null)
    {
        // return true;
        self::init();
        $params = [
            'filters' => [["field" => "tag", "key" => 'user_phone', "relation" => "=", "value" => $phone]],
        ];
        $params = self::pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return self::sendPush($params);
    }

    public static function sendPushUsingTags($tags = [], $title, $message, $imageUrl = null, $url = null)
    {
        self::init();
        $filters = [];
        foreach ($tags as $key => $value) {
            $filters[] = ["field" => "tag", "key" => $key, "relation" => "=", "value" => $value];
        }
        $params = self::pushParams(['filters' => $filters], $title, $message, $imageUrl, $url);
        return self::sendPush($params);
    }

    public static function sendPushToAll($title, $message, $imageUrl = null, $url = null)
    {
        //
        // return true;
        //
        self::init();
        $params = ['included_segments' => ['All']];
        $push = self::pushParams($params, $title, $message, $imageUrl, $url);
        return self::sendPush($push);
    }

    public static function sendPushToSegment($segment, $title, $message, $imageUrl = null, $url = null)
    {
        self::init();
        $params = self::pushParams(['included_segments' => [$segment]], $title, $message, $imageUrl, $url);
        return self::sendPush($params);
    }

    private static function sendPush(array $parameters)
    {
        return self::sendPushRequest($parameters);
    }

    public static function sendPushRequest(array $parameters)
    {
        try {
            self::init();
            $parameters['app_id'] = $parameters['app_id'] ?? self::$appId;
            $parameters['priority'] = $parameters['priority'] ?? 10;
            $parameters = array_merge($parameters, self::$additionalParams);

            $response = Http::retry(3, 200)->withHeaders(self::setHeaders())
                ->post(self::API_URL . self::ENDPOINT_NOTIFICATIONS, $parameters);

            Log::channel('onesignal')->debug('sendPushRequest', [
                'parameters' => $parameters,
                'response'   => $response,
            ]);

            return $response->json();
        } catch (\Throwable $th) {
            Log::channel('onesignal')->error('sendPushRequest Error: ' . $th->getMessage(), [
                'exception' => $th,
                'parameters' => $parameters,
            ]);
        }
    }

    public static function getNotifications($limit = null, $offset = null)
    {
        self::init();
        $endpoint = self::ENDPOINT_NOTIFICATIONS . '?app_id=' . self::$appId;

        if ($limit) {
            $endpoint .= "&limit=" . $limit;
        }

        if ($offset) {
            $endpoint .= "&offset=" . $offset;
        }

        $response = Http::withHeaders(self::setHeaders())
            ->get(self::API_URL . $endpoint);

        return $response->json();
    }

    public static function getNotification($notification_id)
    {
        self::init();
        $response = Http::withHeaders(self::setHeaders())
            ->get(self::API_URL . self::ENDPOINT_NOTIFICATIONS . '/' . $notification_id . '?app_id=' . self::$appId);

        return $response->json();
    }

    public static function deletePush($notificationId)
    {
        self::init();
        $response = Http::withHeaders(self::setHeaders())
            ->delete(self::API_URL . self::ENDPOINT_NOTIFICATIONS . "/$notificationId?app_id=" . self::$appId);

        return $response->json();
    }
}
