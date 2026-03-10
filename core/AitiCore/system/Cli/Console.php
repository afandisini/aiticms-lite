<?php

declare(strict_types=1);

namespace System\Cli;

use System\Foundation\Application;

class Console
{
    public const VERSION = '0.1.0';

    /**
     * @var array<string, Command>
     */
    private array $commands = [];

    /**
     * @var array<string, string>
     */
    private array $aliases = [];

    public function __construct(private Application $app)
    {
    }

    public function register(Command $command): void
    {
        $this->commands[$command->name()] = $command;
        foreach ($command->aliases() as $alias) {
            $this->aliases[$alias] = $command->name();
        }
    }

    /**
     * @param array<int, string> $argv
     */
    public function run(array $argv): int
    {
        $input = $argv;
        array_shift($input);
        $name = $input[0] ?? 'list';

        if (in_array($name, ['--version', '-V'], true)) {
            fwrite(STDOUT, 'AitiCore Flex CLI v' . self::VERSION . PHP_EOL);
            return 0;
        }

        if (isset($this->aliases[$name])) {
            $name = $this->aliases[$name];
        }

        if (!isset($this->commands[$name])) {
            fwrite(STDOUT, 'Command not found: ' . $name . PHP_EOL);
            fwrite(STDOUT, 'Run `php aiti list` for available commands.' . PHP_EOL);
            return 1;
        }

        array_shift($input);
        return $this->commands[$name]->handle($input, $this->app);
    }

    /**
     * @return array<string, Command>
     */
    public function commands(): array
    {
        ksort($this->commands);
        return $this->commands;
    }
}
