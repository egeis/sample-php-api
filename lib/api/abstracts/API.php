<?php
require_once("Endpoint.php");

abstract class API 
{    
    /**
     *  Property: Request Type
     *  The type of HTTP request.
     * 
     *  @example 'GET'
     *  @example 'POST'
     *  @example 'PUT'
     *  @example 'DELETE'
     * 
     *  @var string
     */
    protected $requestType = null;

    /**
     *  Property: Request
     *  Array of request data.
     * 
     *  @var string[]
     */
    public static $request;

    /**
     *  Property: Args
     *  Additional URI components after the endpoint and action.$_COOKIE
     * 
     *  @example 'forcast/3-16-2017' Requests the forcast for the date '3-16-2017'
     * 
     *  @var string
     */
    protected $args = array();

    /**
     *  Property: Filters
     * 
     * @var string|mixed[]
     */
    protected $filters = array();

    /**
     *  Property: Endpoints
     *  Registered endpoints and their endpoint instance.
     * 
     *  @var string|class[]
     */
    protected $endpoints = array();

    /**
     *  Property: Model Name
     *  Name of the requested model.
     * 
     *  @var string|void
     */
    protected $modelName = null;

    /**
     *  Function: Constructor
     * 
     * @param string $request
     * @return void
     */
    public function __construct( $request, $debug = false ) 
    {
        //Setup Varibles
        $request = rtrim($request,'/');
        $this->args = explode('/', $request);
        $this->modelName = ucfirst( filter_var( array_shift($this->args), FILTER_SANITIZE_STRING ) );
        $this->requestType = $_SERVER['REQUEST_METHOD'];

        //Determine if HTTP_X_HTTP_METHOD addition was made to POST.
        if ( $this->requestType == 'POST' &&  array_key_exists( 'HTTP_X_HTTP_METHOD', $_SERVER ) ) 
        {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE')
            {
                $this->requestType = 'DELETE';
            }
            else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT')
            {
                $this->requestType = 'PUT';
            } else {
                $this->__response('Invalid Method', 405);
            }
        }

        self::$request = array( 
            'url' => $request,
            'args' => $this->args,
            'model' => $this->modelName,
            'type' => $this->requestType
        );
    }

    public function execute()
    {
        if( !(is_subclass_of($this->modelName, 'Endpoint')) )
        {
            throw new Exception("Endpoint does not exist.");
            $this->__response('Invalid Method', 405);
        }

        $endpoint = $this->modelName::getInstance( $_REQUEST );
        $this->__response( $endpoint->execute(), 200);
    }

    /**
     *  Function: Respond
     *  Sets the HTTP response header and sends the json response.
     * 
     *  @param string[] $json
     *  @param int $status
     *  @return void
     */
    private function __response( $json, $status = 200 )
    {
        header("HTTP/1.1 " . $status . " " . $this->__requestStatus($status));
        header( "Access-Control-Allow-Orgin: *" );
        header( "Access-Control-Allow-Methods: *" );
        header( "Content-Type: application/json" );
        
        if(is_array($json) )
        {
            echo json_encode($json);
        }

        exit;
    }

    /**
     *  Function: Request Status
     *  Returns the status message.
     * 
     *  @param  int $code
     *  @return void
     */
    private function __requestStatus( $code )
    {
        $status = array(  
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            402 => 'Request Limit Reached',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        ); 
        return ( $status[$code] ) ? $status[$code] : $status[500]; 
    }

    /**
     *  Function: Register Endpoint
     *  Registers the endpoint using its given name.
     * 
     *  @param instanceof IEndpoint
     *  @return bool
     */
    public function registerEndpoint( $class )
    {
        if( !$class instanceof Endpoint)
        {
            throw new Exception('This endpoint not an Instance of Endpoint.');
            return false;
        }

        if( array_key_exists( $class::getName(), $this->endpoints) )
        {
            throw new Exception('Endpoint already registered.');
            return false;
        }

        $this->endpoints[$class::getName()] = $class;
        return true;
    }
}
?>