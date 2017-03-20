<?php
require_once('API.php');
abstract class Endpoint
{
    protected static $acl = array();
    protected static $name = '';

    private static $instance = null;
        
    protected $action = null;
    protected $filedata = null;

    protected $data = array();
    protected $filters = array();

    /**
     *  Function: Get Name
     * 
     * @return string $name
     */
    final public static function getName()
    {
        return ( empty( self::$name ) ) ? get_called_class() : self::$name;
    }

    /**
     *  Function: Get Instance
     * 
     *  @return instance
     */
    final public static function getInstance( $data  )
    {
        if ( self::$instance === null )
        {
            $class = get_called_class();
            self::$instance = new $class( $data  );
        }

        return self::$instance;
    }

    /**
     *  
     * 
     * @return void
     */
    public function execute()
    {
        $action = $this->action;
        if( is_callable( array($this, $action) )){
            return $this->$action();
        } else {
            return $this->default();
        }
    }

    private function default()
    {
        return array('error' => true, 'message' => 'Model method not implemented.', 'request' => API::$request );
    }

    /**
     *  Function: Has Action
     *  Checks if a method exists.
     * 
     *  @param string $action
     *  @return bool
     */
    public function hasAction( $action )
    {
        return (method_exists ( $this, $action) && is_callable( array($this, $action) ) );
    }

    protected function __construct( $data )
    {
        switch( API::$request['type'] ) 
        {
            case 'DELETE':
            case 'POST':
            case 'GET':
                $this->data = $this->__clean( $_REQUEST );
                break;
            case 'PUT':
                $this->data = $this->__clean( $_REQUEST );
                $this->file = file_get_contents("php://input");
                break;
            default:
                $this->__response('Invalid Method', 405);
                break;
        }
        if( is_array(API::$request['args']) && sizeof(API::$request['args']) > 0)
        {
            if( $this->hasAction ( ucfirst(API::$request['args'][0]) ) )
            {
                $this->action = ucfirst( array_shift(API::$request['args']) );
                API::$request['action'] = $this->action;
            }
        } else {
            $this->action = "default";
            API::$request['action'] = $this->action;
        }
    }

    /**
     *  Function: Clean
     *  Cleans up the data provided by HTTP_VARS GET or POST
     * 
     *  @param string|mixed[]
     *  @return string|mixed[]
     */
    private function __clean( $vars, $key = null )
    {
        $c = array();
        if( is_array( $vars ) )
        {
            foreach($vars as $k => $v)
            {
                $c[$k] = $this->__clean($v, $key);
            }
        } else {
           $filter = FILTER_SANITIZE_STRING;

            if( !empty( $key ) && !empty( self::$filters ) )
            {
                if( array_key_exists($key, self::$filters) )
                {
                    $filter = ( empty(self::$filters[$key]) ) ? $filter : self::$filters[$key];
                }
            }

            $c = filter_var( trim( $vars ), $filter) ;
        }

        return $c;
    }
}
?>