<?php declare(strict_types=1);

namespace Selpol\Device\Ip\Intercom\Beward\Trait;

use Selpol\Device\Ip\Intercom\Setting\Video\VideoDetection;
use Selpol\Device\Ip\Intercom\Setting\Video\VideoDisplay;
use Selpol\Device\Ip\Intercom\Setting\Video\VideoEncoding;
use Selpol\Device\Ip\Intercom\Setting\Video\VideoOverlay;

trait VideoTrait
{
    public function getVideoEncoding(): VideoEncoding
    {
        return new VideoEncoding(0, 0);
    }

    public function getVideoDetection(): VideoDetection
    {
        return new VideoDetection(false, null, null, null, null);
    }

    public function getVideoDisplay(): VideoDisplay
    {
        $response = $this->parseParamValueHelp($this->get('/cgi-bin/display_cgi', ['action' => 'get'], parse: false));

        return new VideoDisplay($response['TickerText']);
    }

    public function getVideoOverlay(): VideoOverlay
    {
        $response = $this->parseParamValueHelp($this->get('/cgi-bin/textoverlay_cgi', ['action' => 'get'], parse: false));

        return new VideoOverlay($response['Title']);
    }

    public function setVideoEncoding(VideoEncoding $videoEncoding): void
    {
        $this->get('/webs/videoEncodingCfgEx', [
            'vlevel' => '2',
            'encoder' => '0',
            'sys_cif' => '0',
            'advanced' => '1',
            'ratectrl' => '0',
            'quality' => '1',
            'iq' => '1',
            'rc' => '1',
            'bitrate' => $videoEncoding->primaryBitrate,
            'frmrate' => '25',
            'frmintr' => '25',
            'first' => '0',
            'framingpos' => '0',
            'vlevel2' => '0',
            'encoder2' => '0',
            'sys_cif2' => '1',
            'advanced2' => '1',
            'ratectrl2' => '0',
            'quality2' => '1',
            'iq2' => '1',
            'rc2' => '1',
            'bitrate2' => $videoEncoding->secondaryBitrate,
            'frmrate2' => '25',
            'frmintr2' => '25',
            'first2' => '0',
            'maxfrmintr' => '200',
            'maxfrmrate' => '25',
            'nlevel' => '1',
            'nfluctuate' => '1',
        ]);
    }

    public function setVideoDetection(VideoDetection $videoDetection): void
    {
        $params = [
            'sens' => $videoDetection->enable ? 4 : 0,
            'ckdetect' => $videoDetection->enable ? '1' : '0',
            'ckevery' => $videoDetection->enable ? '1' : '0',
            'ckevery2' => '0',
            'begh1' => '0',
            'begm1' => '0',
            'endh1' => 23,
            'endm1' => 59,
            'ckhttp' => '0',
            'alarmoutemail' => '0',
            'ckcap' => '0',
            'ckalarmrecdev' => '0',
        ];

        $params['nLeft1'] = $videoDetection->left ?: 0;
        $params['nTop1'] = $videoDetection->top ?: 0;
        $params['nWidth1'] = $videoDetection->width ?: 704;
        $params['nHeight1'] = $videoDetection->height ?: 576;

        $this->get('webs/motionCfgEx', $params);
    }

    public function setVideoDisplay(VideoDisplay $videoDisplay): void
    {
        $this->post('/cgi-bin/display_cgi', ['action' => 'set', 'TickerEnable' => $videoDisplay->title ? 'on' : 'off', 'TickerText' => $videoDisplay->title, 'TickerTimeout' => 125, 'LineEnable1' => 'off', 'LineEnable2' => 'off', 'LineEnable3' => 'off', 'LineEnable4' => 'off', 'LineEnable5' => 'off']);
    }

    public function setVideoOverlay(VideoOverlay $videoOverlay): void
    {
        $this->post('/cgi-bin/textoverlay_cgi', ['action' => 'set', 'Title' => $videoOverlay->title, 'TitleValue' => $videoOverlay->title ? 1 : 0, 'DateValue' => 1, 'TimeValue' => 1, 'TimeFormat12' => 'False', 'DateFormat' => 2, 'WeekValue' => 0, 'BitrateValue' => 0, 'Color' => 0, 'ClientNum' => 0]);
    }
}