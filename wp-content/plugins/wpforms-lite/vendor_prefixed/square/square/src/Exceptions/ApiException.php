<?php

declare (strict_types=1);
namespace WPForms\Vendor\Square\Exceptions;

use WPForms\Vendor\CoreInterfaces\Sdk\ExceptionInterface;
use WPForms\Vendor\Square\Http\HttpResponse;
use WPForms\Vendor\Square\Http\HttpRequest;
/**
 * Thrown when there is a network error or HTTP response status code is not okay.
 */
class ApiException extends \Exception implements ExceptionInterface
{
    /**
     * HTTP request
     *
     * @var HttpRequest
     */
    private $request;
    /**
     * HTTP response
     *
     * @var HttpResponse|null
     */
    private $response;
    /**
     * @param string $reason the reason for raising an exception
     * @param HttpRequest $request
     * @param HttpResponse|null $response
     */
    public function __construct(string $reason, HttpRequest $request, ?HttpResponse $response)
    {
        parent::__construct($reason, \is_null($response) ? 0 : $response->getStatusCode());
        $this->request = $request;
        $this->response = $response;
    }
    /**
     * Returns the HTTP request
     */
    public function getHttpRequest() : HttpRequest
    {
        return $this->request;
    }
    /**
     * Returns the HTTP response
     */
    public function getHttpResponse() : ?HttpResponse
    {
        return $this->response;
    }
    /**
     * Is the response available?
     */
    public function hasResponse() : bool
    {
        return !\is_null($this->response);
    }
}
