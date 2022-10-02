<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Command;

use Exception;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Swow\Coroutine;
use Swow\CoroutineException;
use Swow\Errno;
use Swow\Http\Client;
use Swow\Http\ResponseException;
use Swow\Http\Server as HttpServer;
use Swow\Socket;
use Swow\SocketException;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class ReverseProxyCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('reverse-proxy');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Start the reverse proxy server for upstreams.');
        $this->addOption('host', '', InputOption::VALUE_OPTIONAL, 'The host of the reverse proxy server', '127.0.0.1');
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port of the reverse proxy server', 9764);
        $this->addOption('backlog', '', InputOption::VALUE_OPTIONAL, 'The backlog of the reverse proxy server', Socket::DEFAULT_BACKLOG);
        $this->addOption('upstream', 'u', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'The target upstream servers of the reverse proxy server', []);
    }

    public function handle()
    {
        $host = $this->input->getOption('host');
        $port = (int) $this->input->getOption('port');
        $backlog = (int) $this->input->getOption('backlog');
        $upstreams = $this->input->getOption('upstream');
        foreach ($upstreams as $key => $upstream) {
            [$upstreamHost, $upstreamPort] = explode(':', $upstream);
            $upstreams[$key] = [
                'host' => $upstreamHost,
                'port' => (int) $upstreamPort,
            ];
        }

        $server = new HttpServer();
        $server->bind($host, $port)->listen($backlog);
        $this->output->writeln(sprintf('<info>[INFO] Reverse Proxy Server listening at %s:%d</info>', $host, $port));
        while (true) {
            try {
                $connection = $server->acceptConnection();
                Coroutine::run(static function () use ($connection, $upstreams): void {
                    try {
                        $target = $upstreams[array_rand($upstreams)];
                        $httpClient = new Client();
                        $httpClient->connect($target['host'], $target['port']);
                        while (true) {
                            $request = null;
                            try {
                                $request = $connection->recvHttpRequest();
                                $connection->sendHttpResponse(
                                    $httpClient->sendRequest($request->setKeepAlive(true))
                                );
                            } catch (ResponseException $exception) {
                                $connection->error($exception->getCode(), $exception->getMessage());
                            }
                            if (! $request || ! $request->getKeepAlive()) {
                                break;
                            }
                        }
                    } catch (Exception) {
                        // you can log error here
                    } finally {
                        $connection->close();
                        isset($httpClient) && $httpClient->close();
                    }
                });
            } catch (SocketException|CoroutineException $exception) {
                if (in_array($exception->getCode(), [Errno::EMFILE, Errno::ENFILE, Errno::ENOMEM], true)) {
                    sleep(1);
                } else {
                    break;
                }
            }
        }
    }
}
