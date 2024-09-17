<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Modules\Waitlist\Jobs\SendPushJob;

class OneSignalService
{
    const API_URL = "https://onesignal.com/api/v1";
    const ENDPOINT_NOTIFICATIONS = "/notifications";

    protected $appId;
    protected $restApiKey;
    protected $additionalParams;

    public function __construct()
    {
        $config = config('services.onesignal');

        $this->appId = $config['app_id'];
        $this->restApiKey = $config['rest_api_key'];
        $this->additionalParams = [];
    }

    private function setHeaders()
    {
        return [
            'Authorization' => 'Basic ' . $this->restApiKey,
            'Content-Type' => 'application/json'
        ];
    }

    public function addParams($keyOrArray, $value = null)
    {
        if (is_array($keyOrArray)) {
            $this->additionalParams = array_merge($this->additionalParams, $keyOrArray);
        } else {
            $this->additionalParams[$keyOrArray] = $value;
        }
        return $this;
    }

    private function pushParams($params = [], $title, $message, $imageUrl = null, $url = null)
    {
        return array_filter(array_merge([
            'app_id' => $this->appId,
            'headings' => ['en' => $title],
            'contents' => ['en' => $message],
            'big_picture' => $imageUrl,
            'url' => $url,
            'priority' => 10
        ], $params));
    }

    public function sendPushByUserId($userId, $title, $message, $imageUrl = null, $url = null)
    {
        $params =             [
            'filters' => [["field" => "tag", "key" => 'user_id', "relation" => "=", "value" => $userId]],
        ];
        $params = $this->pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return $this->sendPush($params);
    }

    public function sendPushByEmail($email, $title, $message, $imageUrl = null, $url = null)
    {
        $params =             [
            'filters' => [["field" => "tag", "key" => 'user_email', "relation" => "=", "value" => $email]],
        ];
        $params = $this->pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return $this->sendPush($params);
    }

    public function sendPushByPhone($phone, $title, $message, $imageUrl = null, $url = null)
    {
        $params =             [
            'filters' => [["field" => "tag", "key" => 'user_phone', "relation" => "=", "value" => $phone]],
        ];
        $params = $this->pushParams(
            $params,
            $title,
            $message,
            $imageUrl,
            $url
        );
        return $this->sendPush($params);
    }

    public function sendPushUsingTags($tags = [], $title, $message, $imageUrl = null, $url = null)
    {
        $filters = [];
        foreach ($tags as $key => $value) {
            $filters[] = ["field" => "tag", "key" => $key, "relation" => "=", "value" => $value];
        }
        $params = $this->pushParams(['filters' => $filters], $title, $message, $imageUrl, $url);
        $this->sendPush($params);
    }

    public function sendPushToAll($title, $message, $imageUrl = null, $url = null)
    {
        $params = ['included_segments' => ['All']];
        $push = $this->pushParams($params, $title, $message, $imageUrl, $url);

        $this->sendPush($push);
    }

    public function sendPushToSegment($segment, $title, $message, $imageUrl = null, $url = null)
    {
        $params = $this->pushParams(['included_segments' => [$segment]], $title, $message, $imageUrl, $url);
        $this->sendPush($params);
    }

    private function sendPush(array $parameters)
    {
        $this->sendPushRequest($parameters);
    }
    public function sendPushRequest(array $parameters)
    {
        try {
            $parameters['app_id'] = $parameters['app_id'] ?? $this->appId;
            $parameters['priority'] = $parameters['priority'] ?? 10;
            $parameters = array_merge($parameters, $this->additionalParams);

            $response = Http::retry(3, 200)->withHeaders($this->setHeaders())
                ->post(self::API_URL . self::ENDPOINT_NOTIFICATIONS, $parameters);
            return $response->json();
        } catch (\Throwable $th) {
            logger('OneSignalService.php:137: ' . $th->getMessage());
        }
    }

    //
    public function getNotifications($limit = null, $offset = null)
    {
        $endpoint = self::ENDPOINT_NOTIFICATIONS . '?app_id=' . $this->appId;

        if ($limit) {
            $endpoint .= "&limit=" . $limit;
        }

        if ($offset) {
            $endpoint .= "&offset=" . $offset;
        }

        $response = Http::withHeaders($this->setHeaders())
            ->get(self::API_URL . $endpoint);

        return $response->json();
    }
    public function getNotification($notification_id)
    {
        $response = Http::withHeaders($this->setHeaders())
            ->get(self::API_URL . self::ENDPOINT_NOTIFICATIONS . '/' . $notification_id . '?app_id=' . $this->appId);

        return $response->json();
    }
    public function deletePush($notificationId)
    {
        $response = Http::withHeaders($this->setHeaders())
            ->delete(self::API_URL . self::ENDPOINT_NOTIFICATIONS . "/$notificationId?app_id={$this->appId}");

        return $response->json();
    }
}
