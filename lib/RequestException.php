<?php

namespace CodeFarma\SkuVault;

class RequestException extends \Exception
{
	/**
	 * @var	GuzzleHttp\Psr7\Request
	 */
	protected $request;
	
	/**
	 * @var	GuzzleHttp\Psr7\Response
	 */
	protected $response;
	
	/**
	 * Set the request
	 * 
	 * @param	GuzzleHttp\Psr7\Request			$request			The request
	 * @return	void
	 */
	public function setRequest( $request )
	{
		$this->request = $request;
	}
	
	/**
	 * Get the request
	 * 
	 * @return	GuzzleHttp\Psr7\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}	
	
	/**
	 * Set the response
	 * 
	 * @param	GuzzleHttp\Psr7\Response			$response			The response
	 * @return	void
	 */
	public function setResponse( $response )
	{
		$this->response = $response;
	}
	
	
	/**
	 * Get the response
	 * 
	 * @return	GuzzleHttp\Psr7\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}
}