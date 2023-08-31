<?php
    namespace backends\dvr
    {
        require_once __DIR__ . "/../internal/internal.php";
    
        class custom extends internal
        {
            
            /**
             * @inheritDoc
             */
            public function getDVRTokenForCam($cam, $subscriberId)
            {
                // Custom realisation
                $subscriber = backend("households")->getSubscribers("id", $subscriberId)[0];

                return 'your custom token for number ' . $subscriber['mobile'];
            }

            
        }
    }
