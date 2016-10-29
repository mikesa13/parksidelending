<?php

	namespace Classes;

	class Request
	{
		public $httpMethod = null;
		public $urlElements = null;
		public $parameters = null;

		public function __construct(
			$httpMethod,
			$urlElements,
			$parameters
			)
		{
			$this->httpMethod = $httpMethod;
			$this->urlElements = $urlElements;
			$this->parameters = $parameters;
		}
	}
?>
