<?php
class Blog extends Endpoint 
{
    protected function __construct( $data )
    {
        parent::__construct( $data );
    }
    
    public function View()
    {
        echo json_encode( array('id'=> API::$request['args'][0]) );
    }
}
?>