<?php

return array(




    'app_id' => env('ONESIGNAL_APP_ID','null'),

    'rest_api_key' => env('ONESIGNAL_REST_API_KEY','null'),

    'tomorrow_hour' => 10, // Hour used on next day. Time is converted to server time (not onesignal server time)

);