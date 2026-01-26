<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Ici, vous définissez quels domaines externes ont le droit de contacter
    | votre API. Pour votre projet, c'est crucial car Vercel (Front) 
    | doit pouvoir parler à Render (Back).
    |
    */

    // Les chemins de l'API qui seront protégés/autorisés par CORS
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // On autorise toutes les méthodes (GET, POST, etc.)
    'allowed_methods' => ['*'],

    // C'EST ICI : On autorise tout pour le développement. 
    // En production, il est VIVEMENT recommandé de limiter aux domaines autorisés.
    'allowed_origins' => ['https://sae301-aquavision.vercel.app'], 

    'allowed_origins_patterns' => [],

    // On autorise tous les headers (Content-Type, X-Requested-With, etc.)
    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];