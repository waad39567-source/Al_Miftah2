<?php

return [
    'project_id'      => 'almiftah-real-estate',
    'client_email'    => 'firebase-adminsdk-fbsvc@almiftah-real-estate.iam.gserviceaccount.com',
    'private_key'     => env('FIREBASE_PRIVATE_KEY', ''),
    'credentials_file'=> base_path('firebase-credentials.json'),
    'web_api_key'     => env('FIREBASE_WEB_API_KEY', ''),
    'sender_id'       => env('FIREBASE_SENDER_ID', ''),
];
