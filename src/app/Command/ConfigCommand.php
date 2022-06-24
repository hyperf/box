<?php

declare(strict_types=1);

namespace App\Command;

use App\Config;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ConfigCommand extends HyperfCommand
{

    protected Config $config;

    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('config');
        $this->config = $this->container->get(Config::class);
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Config of box.');
        $this->addArgument('action', InputArgument::REQUIRED, '');
        $this->addArgument('key', InputArgument::OPTIONAL, '');
        $this->addArgument('value', InputArgument::OPTIONAL, '');
    }

    public function handle()
    {
        $action = $this->input->getArgument('action');
        switch ($action) {
            case 'list':
                $this->output->block(json_encode($this->config->getConfigContent(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                break;
            case 'set':
                $key = $this->input->getArgument('key');
                $value = $this->input->getArgument('value');
                $this->config->updateConfig($key, $value);
                break;
            case 'get':
                $key = $this->input->getArgument('key');
                $value = $this->config->getConfig($key);
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }
                $this->output->block([
                    sprintf('%s: %s', $key, $value)
                ]);
                break;
            default:
                throw new \Exception('Unexpected action');
        }
    }


}
