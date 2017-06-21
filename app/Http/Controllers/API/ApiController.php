<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
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

    	//switch
    }

}
