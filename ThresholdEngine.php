<?php

class ThresholdEngine
{
    private $attemptModel;
    private $blockedModel;
    private $thresholdModel;
    private $detectionModel;
    private $systemModel;

    private $limit = 5;
    private $windowMinutes = 1;
    private $blockTime = 15;

    public function __construct(
        $attemptModel,
        $blockedModel,
        $thresholdModel,
        $detectionModel,
        $systemModel,
        $config = []
    ){

        $this->attemptModel   = $attemptModel;
        $this->blockedModel   = $blockedModel;
        $this->thresholdModel = $thresholdModel;
        $this->detectionModel = $detectionModel;
        $this->systemModel    = $systemModel;
        $this->limit          = max(1,(int) ($config['threshold_limit'] ?? $this->limit));
        $this->windowMinutes  = max(1,(int) ($config['detection_window_minutes'] ?? $this->windowMinutes));
        $this->blockTime      = max(1,(int) ($config['block_minutes'] ?? $this->blockTime));

    }

    public function check($username,$ip)
    {

        $failed = $this->attemptModel->failedAttempts($ip,$this->windowMinutes);

        if($failed >= $this->limit){

            if($this->blockedModel->isBlocked($ip)){
                return true;
            }

            $this->blockedModel->blockIP($ip,$this->blockTime);

            $this->thresholdModel->save(
                $username,
                $ip,
                $failed,
                $this->limit
            );

            $this->detectionModel->save(
                $username,
                $ip,
                "Threshold ".$this->limit." login gagal dalam ".$this->windowMinutes." menit."
            );

            $this->systemModel->save(
                "IP ".$ip." diblokir karena ".$failed." login gagal dalam ".$this->windowMinutes." menit."
            );

            return true;

        }

        return false;

    }

}
