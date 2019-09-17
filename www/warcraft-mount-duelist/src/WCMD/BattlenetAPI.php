<?php

namespace WCMD;

// TODO: Add logging, specifically when the hourly limit is reached
class BattlenetAPI {
    const REGIONS = [
        'us',
        'eu'
    ];
    const LOCALE = 'en_US';
    const MAX_API_CALLS_HOUR = 35500;
    const MAX_API_CALLS_SECOND = 50;
    const PLAYER_CACHE_TIME = 300;
    const REALM_CACHE_TIME = 86400;

    private $timeout = 10;
    private $api_key;

    public function __construct(string $api_key, string $region='us') {
        $this->api_key = $api_key;
    }

    public function getAvailableRegions() {
        return self::REGIONS;
    }

    public function getPlayer(string $region, string $realm, string $player_name, array $fields=[]) {
        if (!($this->isValidRegion($region) && $this->isValidRealm($region, $realm) && $this->isValidCharacter($player_name))) {
            return 'Invalid character';
        }

        $player = $this->retrievePlayerFromCache($region, $realm, $player_name, $fields);

        if ($player == false) {
            $player = $this->fetchPlayer($region, $realm, $player_name, $fields);
        }

        return $player;
    }

    private function retrievePlayerFromCache(string $region, string $realm, string $player_name, array $fields=[]) {
        $player_key = $region . '_' . $realm . '_' . $player_name;

        $mem = Cache::getMemcachedInstance();

        $player = $mem->get($player_key);
        if ($player == false) {
            return false;
        }

        foreach ($fields as $field) {
            $data = $mem->get($player_key . '_' . $field);
            if ($data) {
                $player[$field] = $data;
            } else {
                return false;
            }
        }

        return $player;
    }

    private function fetchPlayer(string $region, string $realm, string $player_name, array $fields=[]) {
        $url = $this->constructURL($region, '/wow/character/' . urlencode($realm) . '/' . urlencode($player_name));
        $url .= '&fields=' . implode(',', $fields);
        $player = $this->query($url);

        if (empty($player)) {
            return 'Error retrieving character';
        } elseif (isset($player['status']) && $player['status'] === 'nok') {
            return $player['reason'];
        }

        $mem = Cache::getMemcachedInstance();
        $player_key = $region . '_' . $realm . '_' . $player_name;
        foreach ($fields as $field) {
            // Cache individual fields
            $val = $player[$field];
            $mem->set($player_key . '_' . $field, $val, self::PLAYER_CACHE_TIME);
        }
        // Cache base player data
        $mem->set($player_key, array_diff_key($player, array_flip($fields)), self::PLAYER_CACHE_TIME);

        return $player;
    }

    public function getRealms(string $region) {
        if (!$this->isValidRegion($region)) {
            return [];
        }
        $key = $region . '_realms';

        $realms = Cache::cache($key, function() use ($region) {
            $url = $this->constructURL($region, '/data/wow/realm/index');
            $url .= '&namespace=dynamic-' . $region;
            $realms = $this->query($url)['realms'];
            return self::indexArray($realms, 'slug');
        }, self::REALM_CACHE_TIME);

        return $realms;
    }

    /**
        Converts an array of associative arrays into an associative array of associative arrays.
        The values remain the same, but keys are changed.
        @param $array Array to index
        @param $key Key to index for each element of $array
    */
    private static function indexArray(array &$arr, $key) {
        $data = [];

        foreach ($arr as $value) {
            $data[$value[$key]] = $value;
        }

        return $data;
    }

    private function isValidRegion(string $region) {
        return in_array($region, self::REGIONS);
    }

    private function isValidRealm(string $region, string $realm) {
        if (!$this->isValidRegion($region)) {
            return false;
        }
        $realms = $this->getRealms($region);
        return isset($realms[$realm]);
    }

    /**
        Checks if a World of Warcraft character name matches some basic rules to avoid unneccessary api calls
    */
    private function isValidCharacter(string $name) {
        return strlen($name) >= 3 && strlen($name) <= 30 && $name === urlencode($name);
    }

    private function getNextHourTimestamp() {
        $date = getdate();
        return mktime(
            $date['hours'] + 1,
            0, // $date['minutes'],
            0, // $date['seconds'],
            $date['mon'],
            $date['mday'],
            $date['year']
        );
    }

    /**
        Calls the battlenet api.
        If the api rate limit is reached, returns an error status array similar to battlenet.
        @param $url URL to call. Should be created using the constructURL function.
    */
    private function query(string $url) {
        $cache = Cache::getMemcachedInstance();
        $num_calls_hour = $cache->increment('battlenet_' . $this->api_key .'_calls_hour', 1, 0, $this->getNextHourTimestamp());
        $num_calls_second = $cache->increment('battlenet_' . $this->api_key . '_calls_second', 1, 0, 1);

        if ($num_calls_second < self::MAX_API_CALLS_SECOND && $num_calls_hour < self::MAX_API_CALLS_HOUR) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            $json = curl_exec($ch);

            $data = json_decode($json, true);
            return $data;
        } else {
            return [
                'status' => 'nok',
                'reason' => 'hourly api limit reached, try again later',
            ];
        }
    }

    /**
        Builds a URL to call the battlenet api
    */
    private function constructURL(string $region, string $path) {
        $url = 'https://' . $region . '.api.blizzard.com' . $path . '?locale=' . self::LOCALE . '&access_token=' . $this->api_key;
        return $url;
    }
}
