<?php

namespace backends\cameras;

class internal extends cameras
{
    /**
     * @inheritDoc
     */
    public function getCameras($by = false, $params = false)
    {
        $q = "select * from cameras order by camera_id";
        $p = false;

        switch ($by) {
            case "id":
                $q = "select * from cameras where camera_id = :camera_id";
                $p = [
                    "camera_id" => $params,
                ];
        }

        return $this->db->get($q, $p, [
            "camera_id" => "cameraId",
            "enabled" => "enabled",
            "model" => "model",
            "url" => "url",
            "stream" => "stream",
            "credentials" => "credentials",
            "name" => "name",
            "dvr_stream" => "dvrStream",
            "timezone" => "timezone",
            "lat" => "lat",
            "lon" => "lon",
            "direction" => "direction",
            "angle" => "angle",
            "distance" => "distance",
            "frs" => "frs",
            "md_left" => "mdLeft",
            "md_top" => "mdTop",
            "md_width" => "mdWidth",
            "md_height" => "mdHeight",
            "common" => "common",
            "comment" => "comment"
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getCamera($cameraId)
    {
        if (!check_int($cameraId)) {
            return false;
        }

        $cams = $this->getCameras("id", $cameraId);

        if (count($cams) === 1) {
            return $cams[0];
        } else {
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function addCamera($enabled, $model, $url, $stream, $credentials, $name, $dvrStream, $timezone, $lat, $lon, $direction, $angle, $distance, $frs, $mdLeft, $mdTop, $mdWidth, $mdHeight, $common, $comment)
    {
        if (!$model) {
            return false;
        }

        $configs = backend("configs");
        $models = $configs->getCamerasModels();

        if (!@$models[$model]) {
            return false;
        }

        if (!check_string($url)) {
            return false;
        }

        if (!check_int($mdLeft) || !check_int($mdTop) || !check_int($mdWidth) || !check_int($mdHeight)) {
            return false;
        }

        return $this->db->insert("insert into cameras (enabled, model, url, stream, credentials, name, dvr_stream, timezone, lat, lon, direction, angle, distance, frs, md_left, md_top, md_width, md_height, common, comment) values (:enabled, :model, :url, :stream, :credentials, :name, :dvr_stream, :timezone, :lat, :lon, :direction, :angle, :distance, :frs, :md_left, :md_top, :md_width, :md_height, :common, :comment)", [
            "enabled" => (int)$enabled,
            "model" => $model,
            "url" => $url,
            "stream" => $stream,
            "credentials" => $credentials,
            "name" => $name,
            "dvr_stream" => $dvrStream,
            "timezone" => $timezone,
            "lat" => $lat,
            "lon" => $lon,
            "direction" => $direction,
            "angle" => $angle,
            "distance" => $distance,
            "frs" => $frs,
            "md_left" => $mdLeft,
            "md_top" => $mdTop,
            "md_width" => $mdWidth,
            "md_height" => $mdHeight,
            "common" => $common,
            "comment" => $comment,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function modifyCamera($cameraId, $enabled, $model, $url, $stream, $credentials, $name, $dvrStream, $timezone, $lat, $lon, $direction, $angle, $distance, $frs, $mdLeft, $mdTop, $mdWidth, $mdHeight, $common, $comment)
    {
        if (!check_int($cameraId)) {
            last_error("noId");
            return false;
        }

        if (!$model) {
            last_error("noModel");
            return false;
        }

        $configs = backend("configs");
        $models = $configs->getCamerasModels();

        if (!@$models[$model]) {
            last_error("modelUnknown");
            return false;
        }

        if (!check_string($url)) {
            return false;
        }

        return $this->db->modify("update cameras set enabled = :enabled, model = :model, url = :url, stream = :stream, credentials = :credentials, name = :name, dvr_stream = :dvr_stream, timezone = :timezone, lat = :lat, lon = :lon, direction = :direction, angle = :angle, distance = :distance, frs = :frs, md_left = :md_left, md_top = :md_top, md_width = :md_width, md_height = :md_height, common = :common, comment = :comment where camera_id = $cameraId", [
            "enabled" => (int)$enabled,
            "model" => $model,
            "url" => $url,
            "stream" => $stream,
            "credentials" => $credentials,
            "name" => $name,
            "dvr_stream" => $dvrStream,
            "timezone" => $timezone,
            "lat" => $lat,
            "lon" => $lon,
            "direction" => $direction,
            "angle" => $angle,
            "distance" => $distance,
            "frs" => $frs,
            "md_left" => $mdLeft,
            "md_top" => $mdTop,
            "md_width" => $mdWidth,
            "md_height" => $mdHeight,
            "common" => $common,
            "comment" => $comment,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function deleteCamera($cameraId)
    {
        if (!check_int($cameraId)) {
            last_error("noId");
            return false;
        }

        return $this->db->modify("delete from cameras where camera_id = $cameraId");
    }

    /**
     * @inheritDoc
     */
    public function cron($part): bool
    {
        if ($part === "hourly") {
            $cameras = $this->db->get("select camera_id, url from cameras");

            foreach ($cameras as $camera) {
                $ip = gethostbyname(parse_url($camera['url'], PHP_URL_HOST));

                if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                    $this->db->modify("update cameras set ip = :ip where camera_id = " . $camera['camera_id'], [
                        "ip" => $ip,
                    ]);
                }
            }
        }
        return true;
    }
}
