<?php

use Clue\React\Buzz\Browser;
use Psr\Http\Message\ResponseInterface;
use React\Filesystem\Filesystem;
use React\Stream\WritableStreamInterface;
use function \React\Promise\Stream\UnwrapWritable;

final class Downloader
{
    private $client;

    private $filesystem;

    private $directory;

    public function __construct(Browser $client, Filesystem $filesystem, string $directory)
    {
        $this->client = $client;
        $this->filesystem = $filesystem;
        $this->directory = $directory;
    }

    public function download(string ...$urls): void
    {
        foreach ($urls as $url) {
            $file = $this->openFileFor($url);

            $this->client->get($url)->then(
                function (ResponseInterface $response) use ($file) {
                    $response->getBody()->pipe($file);
                }
            );
        }
    }

    private function openFileFor(string $url): WritableStreamInterface
    {
        $path = $this->directory . DIRECTORY_SEPARATOR . basename($url);

        return UnwrapWritable($this->filesystem->file($path)->open('cw'));
    }
}
