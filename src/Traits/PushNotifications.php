<?php
namespace jonlod\OneSignal\traits;
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



trait PushNotifications{

    /**
     * @param $message
     * @param User $user
     */
    public function pushToUser($message, User $user,  $heading = null ,$additional_data = null){
        if(env('APP_ENV') != 'local'){

            $params = $this->paramBuilder($message,$heading, $additional_data);

            if(config('onesignal.email_toggle'))
                $params["filters"] = [$this->insertUserByMail($user)];
            else
                $params["tags"] = [$this->insertUserById($user)];

            SendPushes::dispatch($params);
        }
    }

    /**
     * @param $message
     */
    public function pushToAll($message){
        OneSignal::sendNotificationToAll($message);
    }


    public function pushToAllTag($message, $tag, $value, $heading = null, $additional_data = null){

        $params = $this->paramBuilder($message, $heading, $additional_data);

        $params["tags"]  = [
            ['key' => $tag, 'relation' => '=', 'value' => $value],
        ];

        OneSignal::sendNotificationCustom($params);
    }
    /**
     * @param $message
     */
    public function pushToAllTomorrow($message){
        OneSignal::sendNotificationToAll($message,null,null,null,Carbon::tomorrow()->addHours(config('onesignal.tomorrow_hour'))->toDateTimeString());
    }

    /**
     * @param $message
     * @param User[] $users
     */
    public function pushToUsers($message, $users, $heading = null,$additional_data = null){
        if(env('APP_ENV') != 'local'){

            $params = $this->paramBuilder($message, $heading, $additional_data);

            if(config('onesignal.email_toggle'))
                $this->pushToUsersByMail($users, $params);
            else
                $this->pushToUsersById($users, $params);

        }
    }

    // HELPERS


    private function paramBuilder($message, $heading = null, $additional_data = null){
        $params = [];
        if(is_array($message))
            $params["contents"] = $message;
        else
            $params["contents"] = ["en" => $message];
        if(!$heading)
            $params["headings"] = ["en" => trans('main.app')];
        else{
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

    /**
     * @param $message
     * @param User[] $users
     */
    private function pushToUsersById($users, &$params){
        $tags = 0;

        $params["tags"] = [];
        $last = $users->last();
        foreach ($users as $key=>$user){
            array_push($params["tags"], $this->insertUserById($user));
//            if($param){
//                array_push($params["tags"],$this->insertAnd());
//                array_push($params["tags"], $this->insertAdditionalParam($param,'true'));
//            }
            $tags += 3;
            if($tags >= 180){
                SendPushes::dispatch($params);
                //OneSignal::sendNotificationCustom($params);
                $params["tags"] = [];
                $tags = 0;
            }else{
                if($last != $user){
                    array_push($params["tags"], $this->insertOr());
                    $tags++;
                }
            }
        }
        if($params["tags"] != []){
            SendPushes::dispatch($params);
            //OneSignal::sendNotificationCustom($params);
        }
    }

    /**
     * @param $message
     * @param User[] $users
     */
    private function pushToUsersByMail($users, &$params){

        $filters = 0;
        $params["filters"] = [];
        $params["tags"] = [];

        $last = $users->last();
        foreach ($users as $key=>$user){
            array_push($params["filters"], $this->insertUserByMail($user));
            $filters++;
            if($filters >= 180){
                OneSignal::sendNotificationCustom($params);
                $params["filters"] = [];
                $filters = 0;
            }else{
                if($last != $user){
                    array_push($params["filters"], $this->insertOr());
                    $filters++;
                }
            }
        }
        if($params["filters"] != [])
            OneSignal::sendNotificationCustom($params);
    }


    //HELP FUNCTIONS
    /**
     * @return array
     */
    private function insertOr(){
        return ["operator"=> 'OR'];
    }

    private function insertAnd(){
        return ["operator"=> 'AND'];
    }
    /**
     * @param User $user
     * @return array
     */
    private function insertUserById(User $user){
        return ["key" => 'user_id', 'relation' => '=', 'value' => $user->id];
    }

    /**
     * @param User $user
     * @return array
     */
    private function insertUserByMail(User $user){
        return ["field" => 'email', 'value' => $user->email];
    }

    private function insertAdditionalParam($param, $val){
        return ["key" => $param, 'relation' => '=', 'value' => $val];
    }
    private function insertAdditionalParamFilter($param, $val){
        return ["field" => 'tag', 'key' => $param, 'relation' => '=', 'value' => $val];
    }

}