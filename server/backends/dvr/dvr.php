<?php

namespace backends\dvr;

use backends\backend;

abstract class dvr extends backend
{

    /**
     * @param $url
     * @return mixed
     */
    abstract public function getDVRServerByStream($url);

    /**
     * @param $url
     * @return mixed
     */
    abstract public function getDVRTokenForCam($cam, $subscriberId);

    /**
     * @return mixed
     */
    abstract public function getDVRServers();

    /**
     * @param object $cam Camera object
     * @param integer $start unixtime of start
     * @param integer $end unixtime of end
     * @return string URL with DVR archive on a DVR-server
     */
    abstract public function getUrlOfRecord($cam, $subscriberId, $start, $finish);

    /**
     * @param object $cam Camera object
     * @param integer $time unixtime of screenshot
     * @param boolean $addTokenToUrl add token information
     * @return string URL with mp4-screenshot on a DVR-server
     */
    abstract public function getUrlOfScreenshot($cam, $time = false, $addTokenToUrl = false);
}
