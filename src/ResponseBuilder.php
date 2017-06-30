<?php
namespace ImageCompress;

class ResponseBuilder
{

	protected $response;

	function __construct() {
		$this->response = [
			"status"    =>  "",
			"results"   =>  []
		];
	}

	/**
	 * Returns response array
	 *
	 * @return array
	 */
	public function getResponse() {
		return $this->response;
	}

	/**
	 * Sets a successful response
	 *
	 * @param $filename
	 * @param $urlPath
	 * @param $originalsAbsPath
	 * @param $compressedVal
	 * @param $base64
	 */
	public function setSuccessResponse($filename, $urlPath, $originalsAbsPath, $compressedVal, $base64) {
		$this->response['status'] = "ok";
		$this->response['results']['filename'] = $filename;
		$this->response['results']['urlPath'] = $urlPath;
		$this->response['results']['absPath'] = $originalsAbsPath;
		$this->response['results']['compressedVal'] = $compressedVal;
		$this->response['results']['base64'] = $base64;
	}
}