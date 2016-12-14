<?php
// Routes
use WCMD\BattlenetAPI;
use WCMD\MountComparer;
use WCMD\Config;

// Homepage
$app->get('/', function($request, $response) {
    $bn_api = new BattlenetAPI(Config::get('battlenet_api_key'));
    
    $regions = [];
    $available_regions = $bn_api->getAvailableRegions();
    foreach ($available_regions as $region) {
        $regions[$region] = $bn_api->getRealms($region);
    }
    
    return $this->renderer->render($response, 'layout.php', [
        'content' => $this->renderer->fetch('index.php', [
            'regions' => $regions,
        ]),
    ]);
});

// Contact
$app->get('/contact', function($request, $response) {
    return $this->renderer->render($response, 'layout.php', [
        'content' => $this->renderer->fetch('contact.php'),
    ]);
});

// Compare API
$app->get('/compare', function($request, $response) use ($app) {
    $char1 = [
        'region'    => $request->getParam('char1_region'),
        'realm'     => $request->getParam('char1_realm'),
        'name'      => $request->getParam('char1_name'),
    ];
    $char2 = [
        'region'    => $request->getParam('char2_region'),
        'realm'     => $request->getParam('char2_realm'),
        'name'      => $request->getParam('char2_name'),
    ];
    
    $error = [];
    
    if (in_array(null, $char1)) {
        $error[] = 'Character 1 is missing information';
    }
    if (in_array(null, $char2)) {
        $error[] = 'Character 2 is missing information';
    }
    
    
    if (empty($error)) {
        $bn_api = new BattlenetAPI(Config::get('battlenet_api_key'));
        
        $char1_mounts = $bn_api->getPlayer($char1['region'], $char1['realm'], $char1['name'], ['mounts']);
        $char2_mounts = $bn_api->getPlayer($char2['region'], $char2['realm'], $char2['name'], ['mounts']);
        
        if (!is_array($char1_mounts)) {
            $error[] = 'Character 1 ' . $char1_mounts;
        }
        if (!is_array($char2_mounts)) {
            $error[] = 'Character 2 ' . $char2_mounts;
        }
    }
    
    if (empty($error)) {
        $mount_comparer = new MountComparer();
        $char1_id = $mount_comparer->addPlayer($char1_mounts);
        $char2_id = $mount_comparer->addPlayer($char2_mounts);
        
        $ret = [
            'char1' => $mount_comparer->compare($char1_id),
            'char2' => $mount_comparer->compare($char2_id),
        ];
    } else {
        $ret = [
            'error' => $error,
        ];
    }
    
    return $response->withJson($ret);
    
});

// Get realms API
$app->get('/realms', function($request, $response) {
    $bn_api = new BattlenetAPI(Config::get('battlenet_api_key'));
    
    return $response->withJson($bn_api->getRealms($request->getParam('region')));
});