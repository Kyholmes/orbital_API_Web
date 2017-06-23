<?php

namespace App\Http\Controllers\API;

// use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use Input;
use Response;

class ApiController extends Controller
{
    //

    protected $statusCode = 200;

    const CODE_WRONG_ARGS = 'WRG_ARGS';

    const CODE_NOT_FOUND = 'NOT_FOUND';

    const CODE_INTERNAL_ERROR = 'INT_ERROR';

    const CODE_UNAUTHORIZED = 'UNAUTH';

    const CODE_FORBIDDEN = 'FORBID';

    const CODE_INVALID_MIME_TYPE = 'INV_M_TYPE';

    const CODE_CONFLICT = 'CONFLICT';

    public function __construct()
    {
    	$this->fractal = new Manager();

    	if(isset($_GET['include']))
    	{
    		$fractal->parseIncludes($_GET['include']);
    	}
    }

    /**
    * getter for statusCode
    * @return int
    */
    public function getStatusCode()
    {
    	return $this->statusCode;
    }

    /**
    * setter for statusCode
    * @param int $statusCode value to set
    * @return self
    */
    public function setStatusCode($statusCode)
    {
    	$this->statusCode = $statusCode;
    	return $this;
    }

    //return single item
    protected function respondWithItem($item, $callback)
    {
    	$resource = new Item($item, $callback);

    	$rootScope = $this->fractal->createData($resource);

    	return $this->respondWithArray($rootScope->toArray());
    }

    //return a collection of items
    protected function respondWithCollection($collection, $callback, $cursor = null)
    {
    	$resource = new Collection($collection, $callback);

    	if(!empty($cursor))
    	{
    		$resource->setCursor($cursor);
    	}

    	$rootScope = $tis->fractal->createData($resource);

    	return $this->respondWithArray($rootScope->toArray());
    }

    //return message array
    protected function respondWithArray(array $array, array $headers = [])
    {
    	$mimeTypeRaw = Input::server('HTTP_ACCEPT', '*/*');

    	//if it is empty or has */* then default to JSON
    	if($mimeTypeRaw === '*/*')
    	{
    		$mimeType = 'application/json';
    	}
    	else
    	{
    		$mimeParts = (array) explode(';', $mimeTypeRaw);
    		$mimeType = strtolower($mimeParts[0]);
    	}

    	switch ($mimeType) {
    		case 'application/json':
    			$contentType = 'application/json';

    			$content = json_encode($array);
    			break;
    		
    		default:
    			$contentType = 'application/json';
    			$content = json_encode([
    				'error' => [
    					'code' => static::CODE_INVALID_MIME_TYPE,
    					'http_code' => 415,
    					'message' => sprintf('Content of type %s is not supported.', $mimeType),
    				]
    			]);
    			break;
    	}

    	$response = Response::make($content, $this->statusCode, $headers);

    	$response->header('Content-Type', $contentType);

    	return $response;
    }

    //return error message
    protected function respondWithError($message, $errorCode)
    {
    	if($this->statusCode === 200)
    	{
    		trigger_error("error on 200???", E_USER_WARNING);
    	}

    	return $this->respondWithArray([
    		'error' => [
    		'code' => $errorCode,
    		'http_code' => $this->statusCode,
    		'message' => $message,
    		]
    	]);
    }

    /**
	*generate a response with 403 HTTP header and a given message
	* @return Response
	*/
	public function errorForbidden($message = 'Forbidden')
	{
		return $this->setStatusCode(403)->respondWithError($message, self::CODE_FORBIDDEN);
	}

	/**
	* generate a response with 500 HTTP header and a given message
	* @return Response
	*/
	public function errorInternalError($message = 'Internal Error')
	{
		return $this->setStatusCode(500)->respondWithError($message, self::CODE_INTERNAL_ERROR);
	}

	/**
	* generate Response with 404 HTTP header and a given message
	* @return Response
	*/
	public function errorNotFound($message = 'Resource Not Found')
	{
		return $this->setStatusCode(404)->respondWithError($message, self::CODE_NOT_FOUND);
	}

	/**
	* generate Response with 401 HTTP header and a given message
	* @return Response
	*/
	public function errorUnauthorized($message = 'Unauthorized')
	{
		return $this->setStatusCode(401)->respondWithError($message, self::CODE_UNAUTHORIZED);
	}

	/**
	* generate Response with 400 HTTP header and a given message
	* @return Response
	*/
	public function errorWrongArgs($message = 'Wrong Arguments')
	{
		return $this-> setStatusCode(400)->respondWithError($message, self::CODE_WRONG_ARGS);
	}

	/**
	* generate Response with 409 HTTP header and a given message
	* @return Response
	*/
	public function errorConflict($message = 'Conflict')
	{
		return $this->setStatusCode(409)->respondWithError($message, self::CODE_CONFLICT);
	}

	/**
	* generate Response with 204 HTTP header and a given message
	* @return Response
	*/
	public function successNoContent()
	{
		$empty = array();
		return $this->setStatusCode(204)->respondWithArray($empty);
	}
}
