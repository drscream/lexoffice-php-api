<?php declare(strict_types=1);


namespace Clicksports\LexOffice;

use Clicksports\LexOffice\Exceptions\BadMethodCallException;
use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

abstract class BaseClient implements ClientInterface
{
    protected string $resource;
    /**
     * @var Api $api
     */
    protected Api $api;

    public function __construct(Api $lexOffice)
    {
        $this->api = $lexOffice;
    }

    /**
     * @param array[] $data
     * @return ResponseInterface
     * @throws Exceptions\CacheException
     * @throws Exceptions\LexOfficeApiException
     */
    public function create(array $data): ResponseInterface
    {
        $api = $this->api->newRequest('POST', $this->resource);

        $api->request = $api->request->withBody($this->createStream($data));

        return $api->getResponse();
    }

    /**
     * @param string $id
     * @param array[] $data
     * @return ResponseInterface
     * @throws BadMethodCallException
     */
    public function update(string $id, array $data): ResponseInterface
    {
        throw new BadMethodCallException('method update is defined for ' . $this->resource);
    }

    /**
     * @param string $id
     * @return ResponseInterface
     * @throws Exceptions\CacheException
     * @throws Exceptions\LexOfficeApiException
     */
    public function get(string $id): ResponseInterface
    {
        return $this->api->newRequest('GET', $this->resource . '/' . $id)
            ->getResponse();
    }

    /**
     * @param ResponseInterface $response
     * @return object
     */
    public function getAsJson(ResponseInterface $response): object
    {
        $body = $response->getBody()->__toString();

        return Utils::jsonDecode($body);
    }

    /**
     * @param mixed $content
     * @return StreamInterface
     */
    protected function createStream($content): StreamInterface
    {
        return Utils::streamFor(
            Utils::jsonEncode($content)
        );
    }

    /**
     * @param string[]|bool[]|resource[] $content
     * @param string|null $boundary
     * @return MultipartStream
     */
    protected function createMultipartStream(array $content, string $boundary = null): MultipartStream
    {
        $stream = [];
        $boundary = $boundary ?: '--lexoffice';

        foreach ($content as $key => $value) {
            $stream[] = [
                'name' => $key,
                'contents' => $value
            ];
        }

        return new MultipartStream($stream, $boundary);
    }
}
