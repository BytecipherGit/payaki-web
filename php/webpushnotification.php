<?php
   function sendFCM(){
    $url = 'https://fcm.googleapis.com/fcm/send';
    echo $url;
    exit;

    //Server key
    $apiKey = 'AAAAmTnfBpg:APA91bE8wPfNl5tN53IBe2eRmvzr-lhl_VmLiQJQXQ6f3XEzC149SeV-8jg4BJ9vj9YVGDOdxQ_r7FkBVOvbNKWXgDRLhcz0PubIBUg-kkPlBofCSGsbCsKH2MG-W_4BuL8QXxdcp94O';

    $headers = array(
        'Authorization:key='.$apiKey,
        'Content-Type:application/json'
    );

    //Notification Content
    $notifData = [
        'title' =>  'My test notification',
        'body'  =>  'My test notification body',
        // 'image' =>  'image url',
        // 'click_action'  => 'activity.notifhandler'
    ];

    //Optional
    $dataPayload = [
        'to'    =>  'VIP',
        'date'  =>  '2023-06-20',
        'other_data'    =>  'Test other data'
    ];

    // Create Api Body
    $notifBody = [
        'notification'  => $notifData,
        //Datapayload is optional
        'data'  =>  $dataPayload,
        // optional - in seconds, max_time  = 4 weeks
        'time_to_live'  =>  3600,
        // 'to'    =>  'Token or Reg id'
        'to'    => 'topics/newOffer',
        // 'registration_ids'   => Array of regisration ids or token JSON
    ];

    $ch = curl_init(); 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($notifBody));
    //Execute
    $result = curl_exec($ch);
    print_r($result);
    curl_close($ch);

   }

   sendFCM(); 
?>