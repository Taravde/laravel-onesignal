<?php

namespace jonlod\OneSignal\Helpers;

use jonlod\OneSignal\jobs\SendPushes;
use App\Models\User;
use Carbon\Carbon;
use OneSignal;

/**
 * Created by PhpStorm.
 * User: jonasl
 * Date: 01/02/2017
 * Time: 14:38
 */
trait Push{

    /**
     * @param  string|array       $message
     * @param  User               $user
     * @param  null|string|array  $heading
     * @param  null|array         $additional_data
     */
    public static function pushToUser($message, User $user, $heading = null, $additional_data = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading, $additional_data);
            $params["include_external_user_ids"] = [$user->id];

            SendPushes::dispatch($params);
        }
    }


    /**
     * @param  string|array       $message
     * @param  User[]             $users
     * @param  null|string|array  $heading
     * @param  null|array         $additional_data
     */
    public static function pushToUsers($message, $users, $heading = null, $additional_data = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading, $additional_data);

            $params["include_external_user_ids"] = collect($users)->pluck('id');
            SendPushes::dispatch($params);
        }

    }

    /**
     * @param  string|array       $message
     * @param  array              $ids
     * @param  null|string|array  $heading
     * @param  null|array         $additional_data
     */
    public static function pushToExternalIds($message, array $ids, $heading = null, $additional_data = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading, $additional_data);

            $params["include_external_user_ids"] = $ids;
            SendPushes::dispatch($params);
        }

    }


    /**
     * @param  Carbon             $time
     * @param  string|array       $message
     * @param  User[]             $users
     * @param  null|string|array  $heading
     * @param  null|array         $additional_data
     */
    public static function pushToUsersScheduled(Carbon $time,
        $message,
        $users,
        $heading = null,
        $additional_data = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading, $additional_data);

            $params["include_external_user_ids"] = collect($users)->pluck('id');
            $params['send_after'] = $time->setTimezone('UTC')->toDateTimeString();

            SendPushes::dispatch($params);
        }

    }

    /**
     * @param  string|array       $message
     * @param  null|string|array  $heading
     */
    public function pushToAll($message, $heading = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading);
            $params['included_segments'] = ['Subscribed Users'];
            SendPushes::dispatch($params);
        }
    }

    /**
     * @param  string|array       $message
     * @param  string             $tag
     * @param  string             $value
     * @param  null|string|array  $heading
     * @param  null|array         $additional_data
     */
    public function pushToAllTag($message, $tag, $value, $heading = null, $additional_data = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading, $additional_data);
            $params["tags"] = [
                ['key' => $tag, 'relation' => '=', 'value' => $value],
            ];
            SendPushes::dispatch($params);
        }
    }

    /**
     * @param  string|array       $message
     * @param  null|string|array  $heading
     */
    public function pushToAllTomorrow($message, $heading = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading);
            $params['included_segments'] = ['All'];
            $params['send_after'] = Carbon::tomorrow()->addHours(config('onesignal.tomorrow_hour'))->setTimezone('UTC')
                ->toDateTimeString();
            SendPushes::dispatch($params);
        }

    }

    /**
     * @param  Carbon             $time
     * @param  string|array       $message
     * @param  null|string|array  $heading
     */
    public static function pushToAllScheduled(Carbon $time, $message, $heading = null){
        if(env('APP_ENV') != 'local')
        {
            $params = Push::paramBuilder($message, $heading);
            $params['included_segments'] = ['All'];
            $params['send_after'] = $time->setTimezone('UTC')->toDateTimeString();
            SendPushes::dispatch($params);
        }
    }


    // HELPERS


    public static function transAll($string, $replace_data = []){
        $result = [];

        foreach(config('languages.options') as $lang)
        {
            $temp =
            $result[strtolower($lang)] = trans($string, $replace_data, strtolower($lang));
        }

        if(!key_exists('en', $result))
        {
            $result['en'] = array_slice($result, 0, 1);
        }

        return $result;
    }

    private static function paramBuilder($message, $heading = null, $additional_data = null){
        $params = [];
        if(is_array($message))
            $params["contents"] = $message;
        else
            $params["contents"] = ["en" => $message];
        if(!$heading)
            $params["headings"] = ["en" => trans('main.app')];
        else
        {
            if(is_array($heading))
                $params["headings"] = $heading;
            else
                $params["headings"] = ["en" => $heading];
        }
        $params['ios_badgeType'] = 'Increase';
        $params['ios_badgeCount'] = 1;

        $params["data"] = $additional_data;

        return $params;
    }


}