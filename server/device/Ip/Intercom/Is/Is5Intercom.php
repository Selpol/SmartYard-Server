<?php declare(strict_types=1);

namespace Selpol\Device\Ip\Intercom\Is;

use Selpol\Device\Ip\Intercom\IntercomCms;
use Selpol\Device\Ip\Intercom\Setting\Common\DDns;
use Selpol\Device\Ip\Intercom\Setting\Common\Syslog;
use Selpol\Device\Ip\Intercom\Setting\Video\VideoEncoding;

class Is5Intercom extends IsIntercom
{
    public function setVideoEncoding(VideoEncoding $videoEncoding): void
    {
        $this->put('/camera/codec', [
            'Channels' => [
                [
                    "Channel" => 0,
                    "Type" => "H264",
                    "Profile" => 2,
                    "ByFrame" => true,
                    "Width" => 1920,
                    "Height" => 1080,
                    "GopMode" => "NormalP",
                    "IPQpDelta" => 2,
                    "RcMode" => "AVBR",
                    "IFrameInterval" => 30,
                    "MaxBitrate" => $videoEncoding->primaryBitrate
                ],
                [
                    "Channel" => 1,
                    "Type" => "H264",
                    "Profile" => 1,
                    "ByFrame" => true,
                    "Width" => 640,
                    "Height" => 480,
                    "GopMode" => "NormalP",
                    "IPQpDelta" => 2,
                    "RcMode" => "AVBR",
                    "IFrameInterval" => 30,
                    "MaxBitrate" => $videoEncoding->secondaryBitrate
                ]
            ]
        ]);
    }

    public function setSyslog(Syslog $syslog): void
    {
        $this->put('/v1/network/syslog', ['addr' => $syslog->server, 'port' => $syslog->port]);
    }

    public function setDDns(DDns $dDns): void
    {
        $this->put('/v1/ddns', ['enabled' => $dDns->enable]);
    }

    public function clearCms(string $cms): void
    {
        $cms = IntercomCms::model($cms);

        if (!$cms)
            return;

        $length = count($cms->cms);

        for ($i = 1; $i <= $length; $i++) {
            $matrix = $this->get('/switch/matrix/' . $i);

            $matrix['capacity'] = $cms->capacity;

            for ($j = 0; $j < count($matrix['matrix']); $j++)
                for ($k = 0; $k < count($matrix['matrix'][$j]); $k++)
                    $matrix['matrix'][$j][$k] = 0;

            $this->put('/switch/matrix/' . $i, $matrix);
        }
    }
}