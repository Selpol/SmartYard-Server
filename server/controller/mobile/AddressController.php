<?php

namespace Selpol\Controller\mobile;

use backends\plog\plog;
use Selpol\Controller\Controller;
use Selpol\Http\Response;

class AddressController extends Controller
{
    public function getAddressList(): Response
    {
        /** @var array|null $user */
        $user = $this->request->getAttribute('auth')();

        if (!$user)
            return $this->rbtResponse(401);

        $households = backend("households");
        $plog = backend("plog");
        $cameras = backend("cameras");

        $houses = [];
        foreach ($user['flats'] as $flat) {
            $houseId = $flat['addressHouseId'];

            if (array_key_exists($houseId, $houses)) $house = &$houses[$houseId];
            else {
                $houses[$houseId] = [];
                $house = &$houses[$houseId];
                $house['houseId'] = strval($houseId);
                $house['address'] = $flat['house']['houseFull'];

                $is_owner = ((int)$flat['role'] == 0);
                $flat_plog = $households->getFlat($flat["flatId"])['plog'];
                $has_plog = $plog && ($flat_plog == plog::ACCESS_ALL || $flat_plog == plog::ACCESS_OWNER_ONLY && $is_owner);

                if ($plog && $flat_plog != plog::ACCESS_RESTRICTED_BY_ADMIN)
                    $house['hasPlog'] = $has_plog ? 't' : 'f';


                $house['cameras'] = $households->getCameras("houseId", $houseId);
                $house['doors'] = [];
            }

            if (array_key_exists('flats', $house)) $house['flats'][] = ['id' => $flat['flatId'], 'flat' => $flat['flat']];
            else $house['flats'] = [['id' => $flat['flatId'], 'flat' => $flat['flat']]];

            $house['cameras'] = array_merge($house['cameras'], $households->getCameras("flatId", $flat['flatId']));
            $house['cctv'] = count($house['cameras']);

            $flatDetail = $households->getFlat($flat['flatId']);

            foreach ($flatDetail['entrances'] as $entrance) {
                if (array_key_exists($entrance['entranceId'], $house['doors']))
                    continue;

                $e = $households->getEntrance($entrance['entranceId']);

                $door = [];
                $door['domophoneId'] = strval($e['domophoneId']);
                $door['doorId'] = intval($e['domophoneOutput']);
                $door['icon'] = $e['entranceType'];
                $door['name'] = $e['entrance'];

                if ($e['cameraId']) {
                    $cam = $cameras->getCamera($e["cameraId"]);

                    $house['cameras'][] = $cam;
                    $house['cctv']++;
                }

                // TODO: проверить обработку блокировки
                //
                if ($flatDetail['autoBlock'] || $flatDetail['adminBlock'])
                    $door['blocked'] = "Услуга домофонии заблокирована";

                $house['doors'][$entrance['entranceId']] = $door;
            }
        }

        // конвертируем ассоциативные массивы в простые и удаляем лишние ключи
        foreach ($houses as $house_key => $h) {
            $houses[$house_key]['doors'] = array_values($h['doors']);

            unset($houses[$house_key]['cameras']);
        }

        $result = array_values($houses);

        if (count($result))
            return $this->rbtResponse(data: $result);

        return $this->rbtResponse();
    }

    public function registerQR()
    {
        /** @var array|null $user */
        $user = $this->request->getAttribute('auth')();

        if (!$user)
            return $this->rbtResponse(401);

        $body = $this->request->getParsedBody();

        $code = trim(@$body['QR']);

        if (!$code)
            return $this->rbtResponse(404);

        //полагаем, что хэш квартиры является суффиксом параметра QR
        $hash = '';

        for ($i = strlen($code) - 1; $i >= 0; --$i) {
            if (!in_array($code[$i], ['/', '=', "_"]))
                $hash = $code[$i] . $hash;
            else
                break;
        }

        if ($hash == '')
            return $this->rbtResponse(data: "QR-код не является кодом для доступа к квартире");

        $households = backend("households");
        $flat = $households->getFlats("code", ["code" => $hash])[0];

        if (!$flat)
            return $this->rbtResponse(data: "QR-код не является кодом для доступа к квартире");

        $flat_id = (int)$flat["flatId"];

        //проверка регистрации пользователя в квартире
        foreach ($user['flats'] as $item)
            if ((int)$item['flatId'] == $flat_id)
                return $this->rbtResponse(data: "У вас уже есть доступ к данной квартире");

        if ($households->addSubscriber($user["mobile"], null, null, $flat_id))
            return $this->rbtResponse(data: "Ваш запрос принят и будет обработан в течение одной минуты, пожалуйста подождите");

        return $this->rbtResponse(422);
    }
}