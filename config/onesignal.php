<?php

return array(




    'app_id' => env('ONESIGNAL_APP_ID','null'),

    'rest_api_key' => env('ONESIGNAL_REST_API_KEY','null'),

    'email_toggle' => env('ONESIGNAL_EMAIL_TOGGLE', false),

    'tomorrow_hour' => 10, // Hour used on next day. Time is converted to server time (not onesignal server time)

    'user_auth_key' => 'YOUR-USER-AUTH-KEY'// I don't use this
);