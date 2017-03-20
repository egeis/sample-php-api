<?php
    require_once("abstracts/API.php");

    class NPAApi extends API 
    {
        public function __construct( $request, $debug = false )
        {
            parent::__construct( $request, $debug );
        }
    }
?>