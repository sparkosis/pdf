<?php

namespace Sparkosis\Pdf;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Safe\Exceptions\FilesystemException;
use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\ClientException;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\RequestException;

class Pdf
{
    /**
     * @var Client
     * @return Client
     */
    private $client;

    private $margins;
    private $header;
    private $index;
    private $footer;
    private $paperSize;
    private $pdfRequest;


    public function __construct()
    {
        $this->client = new Client(config('pdf.GOT_URI'), new \Http\Adapter\Guzzle6\Client());
        $this->assets = [];
        $this->header = null;
        $this->index = null;
        $this->footer = null;
        $this->paperSize = Request::A4;
        $this->margins = Request::NO_MARGINS;
        $this->pdfRequest = null;
    }

    public function generateFromString(string $html) : Pdf {
        try {
            $factory = DocumentFactory::makeFromString('index.html', $html);
            $this->index = $factory;
        } catch (FilesystemException $e) {
            dd($e->getMessage());
        }

        return $this;
    }

    public function generateFromStream(StreamInterface $stream) : Pdf {
        try {
            $factory = DocumentFactory::makeFromStream('index.html', $stream);
            $this->index = $factory;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

        return $this;
    }

    public function generateFromPath(string $path) : Pdf {

        try {
            $factory = DocumentFactory::makeFromPath('index.html', $path);
            $this->index = $factory;
        } catch (FilesystemException $e) {
            dd($e->getMessage());
        }
        return $this;
    }

    public function generateFromView(string $viewName, array $data = []): Pdf {

        try {
            $html = view($viewName)->with($data)->render();
            $factory = DocumentFactory::makeFromString('index.html', $html);
            $this->index = $factory;
        } catch (\Exception $e) {
            dd($e->getMessage());
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
        return $this;
    }

    public function handleStore(string $path) {
        try {
            $this->makeHtmlRequest();
            $this->client->store($this->pdfRequest, $path);
        } catch (RequestException $e) {
            dd($e->getMessage());
        } catch (ClientException $e) {
            dd($e->getMessage());
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    public function handlePost() : ResponseInterface {
        try {
            $this->makeHtmlRequest();

            return $this->client->post($this->pdfRequest);
        } catch (RequestException $e) {
            dd($e->getMessage());
        } catch (ClientException $e) {
            dd($e->getMessage());
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    public function get() {
       $response = $this->handlePost()->getBody();
       return response($response, 200)->header('Content-Type', 'application/pdf');
    }

    private function makeHtmlRequest() {
        $this->pdfRequest = new HTMLRequest($this->index);
        if(!is_null($this->header))
            $this->pdfRequest->setHeader($this->header);

        if(!is_null($this->footer))
            $this->pdfRequest->setHeader($this->footer);

        if(!is_null($this->assets))
            $this->pdfRequest->setAssets($this->assets);

        try {
            $this->pdfRequest->setPaperSize($this->paperSize);
            $this->pdfRequest->setMargins($this->margins);
        } catch (RequestException $e) {
            dd($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * @param array $assets
     */
    public function addAssets(string $name, string $path): void
    {
        $factory = DocumentFactory::makeFromPath($name, $path);
        array_push($this->assets, $factory);
    }

    /**
     * @return array
     */
    public function getMargins(): array
    {
        return $this->margins;
    }

    /**
     * @param array $margins
     */
    public function setMargins(array $margins): void
    {
        $this->margins = $margins;
    }

    /**
     * @return null
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param null $header
     */
    public function setHeader($header): void
    {
        $this->header = $header;
    }

    /**
     * @return null
     */
    public function getFooter()
    {
        return $this->footer;
    }

    /**
     * @param null $footer
     */
    public function setFooter($footer): void
    {
        $this->footer = $footer;
    }

    /**
     * @return array
     */
    public function getPaperSize(): array
    {
        return $this->paperSize;
    }

    /**
     * @param array $paperSize
     */
    public function setPaperSize(array $paperSize): void
    {
        $this->paperSize = $paperSize;
    }


}
