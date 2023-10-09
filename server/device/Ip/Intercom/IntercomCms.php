<?php declare(strict_types=1);

namespace Selpol\Device\Ip\Intercom;

class IntercomCms
{
    /**
     * @var IntercomCms[]
     */
    public static array $models;

    public readonly string $title;
    public readonly string $model;

    public readonly int $dozenStart;

    /**
     * @var array<string, array<string, int>>
     */
    public readonly array $cms;

    public function __construct(string $title, string $model, int $dozenStart, array $cms)
    {
        $this->title = $title;
        $this->model = $model;

        $this->dozenStart = $dozenStart;

        $this->cms = $cms;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'model' => $this->model,
            'dozen_start' => $this->dozenStart,
            'cms' => $this->cms
        ];
    }

    public static function modelsToArray(): array
    {
        return array_map(static fn(IntercomCms $cms) => $cms->toArray(), self::models());
    }

    /**
     * @return IntercomCms[]
     */
    public static function models(): array
    {
        if (!isset(self::$models))
            self::$models = [
                'bk-100' => new IntercomCms(
                    'VIZIT BK-100',
                    'BK-100',
                    0,
                    [
                        'BK-100' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                ),
                'com-25u' => new IntercomCms(
                    'METAKOM COM-25U',
                    'COM-25U',
                    1,
                    [
                        'COM-25U.1/0' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5],
                        'COM-25U.2' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5],
                        'COM-25U.3' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5],
                        'COM-25U.4' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5],
                        'COM-25U.5' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5],
                        'COM-25U.6' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5],
                        'COM-25U.7' => ['1' => 5, '2' => 5, '3' => 5, '4' => 5, '5' => 5]
                    ]
                ),
                'com-100u' => new IntercomCms(
                    'METAKOM COM-100U',
                    'COM-100U',
                    0,
                    [
                        'COM-100U.1/0' => ['1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10, '10' => 10],
                        'COM-100U.2' => ['1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10, '10' => 10],
                        'COM-100U.3' => ['1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10, '10' => 10],
                        'COM-100U.4' => ['1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10, '10' => 10],
                        'COM-100U.5' => ['1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10, '10' => 10],
                        'COM-100U.6' => ['1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10, '10' => 10]
                    ]
                ),
                'com-220u' => new IntercomCms(
                    'METAKOM COM-220U',
                    'COM-220U',
                    0,
                    [
                        'COM-220U.1/0' => ['1' => 22, '2' => 22, '3' => 22, '4' => 22, '5' => 22, '6' => 22, '7' => 22, '8' => 22, '9' => 22, '10' => 22],
                        'COM-220U.2' => ['1' => 22, '2' => 22, '3' => 22, '4' => 22, '5' => 22, '6' => 22, '7' => 22, '8' => 22, '9' => 22, '10' => 22],
                        'COM-220U.3' => ['1' => 16, '2' => 16, '3' => 16, '4' => 16, '5' => 16, '6' => 16, '7' => 16, '8' => 16, '9' => 16, '10' => 16]
                    ]
                ),
                'kad-2501' => new IntercomCms(
                    'BEWARD KAD2501',
                    'KAD2501',
                    0,
                    ['KAD2501' => ['1' => 26, '2' => 26, '3' => 26, '4' => 26, '5' => 26, '6' => 25, '7' => 25, '8' => 25, '9' => 25, '10' => 25]]
                ),
                'kad-2502' => new IntercomCms(
                    'BEWARD KAD2502',
                    'KAD2502',
                    0,
                    ['KAD2501' => ['1' => 26, '2' => 26, '3' => 26, '4' => 26, '5' => 26, '6' => 25, '7' => 25, '8' => 25, '9' => 25, '10' => 25]]
                ),
                'factorial_8x8' => new IntercomCms(
                    'FACTORIAL 8x8',
                    'FACTORIAL 8x8',
                    0,
                    [
                        'FACTORIAL 8x8.1' => ['1' => 8, '2' => 8, '3' => 8, '4' => 8, '5' => 8, '6' => 8, '7' => 8, '8' => 8],
                        'FACTORIAL 8x8.2' => ['1' => 8, '2' => 8, '3' => 8, '4' => 8, '5' => 8, '6' => 8, '7' => 8, '8' => 8]
                    ]
                ),
                'kkm-100s2' => new IntercomCms(
                    'BEWARD KKM-100S2',
                    'KKM-100S2',
                    0,
                    [
                        'ККМ-100S2.1' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-100S2.2' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                ),
                'kkm-105' => new IntercomCms(
                    'BEWARD KKM-105',
                    'KKM-105',
                    0,
                    [
                        'ККМ-105.1' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.1/2' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.2' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.3' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.4' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.5' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                ),
                'kkm-108' => new IntercomCms(
                    'BEWARD KKM-108',
                    'KKM-108',
                    0,
                    [
                        'ККМ-105.1' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.2' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.3' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.4' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.5' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.6' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.7' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'ККМ-105.8' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                ),
                'km100-7.1' => new IntercomCms(
                    'ELTIS KM100-7.1',
                    'KM100-7.1',
                    0,
                    [
                        'KM100-7.1' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                ),
                'km100-7.5' => new IntercomCms(
                    'ELTIS KM100-7.5',
                    'KM100-7.5',
                    0,
                    [
                        'KM100-7.5.1' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'KM100-7.5.2' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'KM100-7.5.3' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'KM100-7.5.4' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'KM100-7.5.5' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                ),
                'kmg-100' => new IntercomCms(
                    'CYFRAL KMG-100',
                    'KMG-100',
                    0,
                    [
                        'KMG-100.1' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10],
                        'KMG-100.2' => ['0' => 10, '1' => 10, '2' => 10, '3' => 10, '4' => 10, '5' => 10, '6' => 10, '7' => 10, '8' => 10, '9' => 10]
                    ]
                )
            ];

        return self::$models;
    }

    public static function model(string $value): ?IntercomCms
    {
        if (array_key_exists($value, self::models()))
            return self::$models[$value];

        return null;
    }
}