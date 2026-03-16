<?php

return [
    'project_id' => 'almoftahapp',
    'client_email' => 'firebase-adminsdk-fbsvc@almoftahapp.iam.gserviceaccount.com',
    'private_key' => env('FIREBASE_PRIVATE_KEY', ''),
    'credentials_file' => base_path('firebase-credentials.json'),
];
