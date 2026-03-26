<?php

namespace Artamonov\Rest\Foundation;


use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\Text\Encoding;

class Response
{
    private static $_instance;

    /**
     * Возвращаем ответ клиенту в Json-формате
     * @param array $data
     * @param int $statusCode
     * @param array $options
     * @param array $headers
     */
    public function json($data = [], $statusCode = 200, $options = [], $headers = [])
    {
        $this->setHeaders($headers);
        $this->setStatus($statusCode);
        if ($data !== null) {
            $this->jsonEncode($data, $options);
        }
        die;
    }

    public function switchingProtocols($upgrade = '')
    {
        if ($upgrade) header('Upgrade: ' . $upgrade);
        $this->setStatus(101);
        die;
    }

    public function processing()
    {
        $this->setStatus(102);
        die;
    }

    public function ok($data = [])
    {
        $this->setStatus(200);
        if ($data) $this->jsonEncode($data);
        die;
    }

    public function created($data = [])
    {
        $this->setStatus(201);
        if ($data) $this->jsonEncode($data);
        die;
    }

    public function accepted()
    {
        $this->setStatus(202);
        die;
    }

    public function nonAuthoritativeInformation()
    {
        $this->setStatus(203);
        die;
    }

    public function noContent()
    {
        $this->setStatus(204);
        die;
    }

    public function resetContent()
    {
        $this->setStatus(205);
        die;
    }

    public function partialContent($contentRange = '')
    {
        if ($contentRange) header('Content-Range: ' . $contentRange);
        $this->setStatus(206);
        die;
    }

    public function multipleChoices()
    {
        $this->setStatus(300);
        die;
    }

    public function movedPermanently($location = '')
    {
        if ($location) header('Location: ' . $location);
        $this->setStatus(301);
        die;
    }

    public function seeOther($location = '')
    {
        if ($location) header('Location: ' . $location);
        $this->setStatus(303);
        die;
    }

    public function notModified()
    {
        $this->setStatus(304);
        die;
    }

    public function useProxy($location = '')
    {
        if ($location) header('Location: ' . $location);
        $this->setStatus(305);
        die;
    }

    public function temporaryRedirect($location = '')
    {
        if ($location) header('Location: ' . $location);
        $this->setStatus(307);
        die;
    }

    public function permanentRedirect($location = '')
    {
        if ($location) header('Location: ' . $location);
        $this->setStatus(308);
        die;
    }

    public function badRequest($message = '')
    {
        $this->setStatus(400);
        if ($message) $this->jsonEncode(['message' => $message]);
        die;
    }

    public function unauthorized($message = '')
    {
        $this->setStatus(401);
        if ($message) $this->jsonEncode(['message' => $message]);
        die;
    }

    public function paymentRequired()
    {
        $this->setStatus(402);
        die;
    }

    public function forbidden()
    {
        $this->setStatus(403);
        die;
    }

    public function notFound()
    {
        $this->setStatus(404);
        die;
    }

    public function methodNotAllowed()
    {
        $this->setStatus(405);
        die;
    }

    public function notAcceptable()
    {
        $this->setStatus(406);
        die;
    }

    public function conflict()
    {
        $this->setStatus(409);
        die;
    }

    public function gone()
    {
        $this->setStatus(410);
        die;
    }

    public function lengthRequired()
    {
        $this->setStatus(411);
        die;
    }

    public function preconditionFailed()
    {
        $this->setStatus(412);
        die;
    }

    public function payloadTooLarge($retryAfterSeconds = 60)
    {
        $this->setStatus(413);
        header('Retry-After: ' . $retryAfterSeconds . ' seconds');
        die;
    }

    public function uriTooLong()
    {
        $this->setStatus(414);
        die;
    }

    public function unsupportedMediaType()
    {
        $this->setStatus(415);
        die;
    }

    public function misdirectedRequest()
    {
        $this->setStatus('421 Misdirected Request');
        die;
    }

    public function unProcessableEntity()
    {
        $this->setStatus(422);
        die;
    }

    public function locked()
    {
        $this->setStatus(423);
        die;
    }

    public function failedDependency()
    {
        $this->setStatus(424);
        die;
    }

    public function upgradeRequired()
    {
        $this->setStatus(426);
        die;
    }

    public function tooManyRequests()
    {
        $this->setStatus('429 Too Many Requests');
        die;
    }

    public function requestedHostUnavailable($message = '')
    {
        $this->setStatus('434 Requested host unavailable');
        if ($message) $this->jsonEncode(['message' => $message]);
        die;
    }

    public function unavailableForLegalReasons()
    {
        $this->setStatus('451 Unavailable For Legal Reasons');
        die;
    }

    public function internalServerError($message = '')
    {
        $this->setStatus(500);
        if ($message) $this->jsonEncode(['message' => $message]);
        die;
    }

    public function notImplemented()
    {
        $this->setStatus(501);
        die;
    }

    public function badGateway()
    {
        $this->setStatus(502);
        die;
    }

    public function serviceUnavailable()
    {
        $this->setStatus(503);
        die;
    }

    public function httpVersionNotSupported()
    {
        $this->setStatus(505);
        die;
    }

    public function unknownError()
    {
        $this->setStatus('520 Unknown Error');
        die;
    }

    public function webServerIsDown()
    {
        $this->setStatus('521 Web Server Is Down');
        die;
    }

    public function sslHandshakeFailed()
    {
        $this->setStatus('525 SSL Handshake Failed');
        die;
    }

    public function invalidSslCertificate()
    {
        $this->setStatus('526 Invalid SSL Certificate');
        die;
    }

    /**
     * @param array $headers
     *
     * @return void
     */
    private function setHeaders(array $headers = []): void
    {
        foreach ($headers as $header => $item) {
            if (is_string($item)) {
                header($header . ': ' . $item);
            } else if (is_array($item)) {
                $header = $header . ': ' . $item['value'];
                $replace = true;
                $http_response_code = null;
                if (isset($item['replace'])) {
                    $replace = $item['replace'];
                }
                if (isset($item['http_response_code'])) {
                    $http_response_code = $item['http_response_code'];
                }
                header($header, $replace, $http_response_code);
            }
        }
    }

    /**
     * @param $code
     *
     * @return void
     */
    private function setStatus($code = 200): void
    {
        $headers = headers_list();
        $headerContentTypeExists = false;
        foreach ($headers as $headerString) {
            if (str_contains($headerString, 'Content-Type: ')) {
                $headerContentTypeExists = $headerString;
                break;
            }
        }
        if (!$headerContentTypeExists) {
            header('Content-Type: application/json; charset=' . SITE_CHARSET);
        }
        helper()->cgi() ? header('Status: ' . $code) : header($_SERVER['SERVER_PROTOCOL'] . ' ' . $code);
    }

    private function jsonEncode($data = [], $options = [])
    {
        if (!$options) $options = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE;
        try {
            if ($data && !Application::getInstance()->isUtfMode()) {
                $data = Encoding::convertEncoding($data, SITE_CHARSET, 'UTF-8');
            }
        } catch (SystemException $e) {
        }
        echo json_encode($data, $options);
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    private function __construct()
    {
    }

    public function __call($name, $arguments)
    {
        $this->internalServerError('Method \'' . $name . '\' is not defined');
    }

    private function __clone()
    {
    }
}
