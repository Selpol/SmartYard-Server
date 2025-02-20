<?php

namespace Selpol\Device\Ip\Trait;

use Selpol\Device\Exception\DeviceException;
use Selpol\Device\Ip\InfoDevice;
use SensitiveParameter;
use Throwable;

trait HikVisionTrait
{
    public function getSysInfo(): InfoDevice
    {
        try {
            $info = $this->get('/ISAPI/System/deviceInfo');

            if ($info === null) {
                throw new DeviceException($this, 'Не удалось получить информацию об устройстве');
            }

            $serial = strlen($info['serialNumber']) > 9 ? substr($info['serialNumber'], -9) : $info['serialNumber'];

            return new InfoDevice(
                $serial,
                $info['model'],
                $info['hardwareVersion'],
                $info['firmwareVersion'] . ' ' . $info['firmwareReleasedDate'],
                null
            );
        } catch (Throwable $throwable) {
            if ($throwable instanceof DeviceException) {
                throw $throwable;
            }

            throw new DeviceException($this, 'Не удалось получить информацию об устройстве', $throwable->getMessage(), previous: $throwable);
        }
    }

    public function setLoginPassword(#[SensitiveParameter] string $password): static
    {
        $this->put('/Security/users/1', sprintf('<User><id>1</id><userName>%s</userName><password>%s</password><userLevel>Administrator</userLevel><loginPassword>%s</loginPassword></User>', $this->login, $password, $this->password), ['Content-Type' => 'application/xml']);

        return $this;
    }
}