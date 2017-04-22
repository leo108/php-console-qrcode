<?php

namespace Leo108\ConsoleQrCode;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Leo108\ConsoleQrCode\Command\ConvertCommand;

class Application extends BaseApplication
{
    protected function getCommandName(InputInterface $input)
    {
        return 'convert';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands   = parent::getDefaultCommands();
        $defaultCommands[] = new ConvertCommand();

        return $defaultCommands;
    }

    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
