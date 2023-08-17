<?php

use backends\plog\plog;

$validate = validate(@$postdata, [
    'camera_id' => [Rule::required(), Rule::int(), Rule::min(0), Rule::max(), Rule::nonNullable()],
    'date' => [Filter::default(1), Rule::int(), Rule::min(0), Rule::max(14), Rule::nonNullable()]
], 'mobile.cctv.ranges');

if (!$validate)
    response(400, $validate, $validate);

$user = auth();

$households = loadBackend("households");
$plog = loadBackend("plog");

if (!$households || !$plog)
    response(403);

$domophoneId = $households->getDomophoneIdByEntranceCameraId($validate['camera_id']);

if (is_null($domophoneId))
    response(404);

$flats = array_filter(
    array_map(static fn(array $item) => ['id' => $item['flatId'], 'owner' => $item['role'] == 0], $user['flats']),
    static function (array $flat) use ($households) {
        $plog = $households->getFlatPlog($flat['id']);

        return $plog == plog::ACCESS_ALL || $plog == plog::ACCESS_OWNER_ONLY && $flat['owner'];
    }
);

$flatsId = array_map(static fn(array $item) => $item['id'], $flats);

if (count($flatsId) == 0)
    response(404);

$events = $plog->getEventsByFlatsAndDomophone($flats, $domophoneId, $validate['date']);

response(200, array_map(static fn(array $item) => $item['date'], $events));
