<?php

namespace Leo108\ConsoleQrCode\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use PHPQRCode\QRcode;

class ConvertCommand extends Command
{
    protected function configure()
    {
        $this->setName('convert')
            ->addOption('text', null, InputOption::VALUE_REQUIRED, 'The text you want to convert')
            ->addOption('lr', null, InputOption::VALUE_REQUIRED, 'The left and right padding', 1)
            ->addOption('tb', null, InputOption::VALUE_REQUIRED, 'The top and bottom padding', 1)
            ->addOption('stdin', null, InputOption::VALUE_NONE, 'Read the text from SDTIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lrPadding = $input->getOption('lr');
        $tbPadding = $input->getOption('tb');
        if ($input->getOption('stdin')) {
            $text = '';
            while ($line = fgets(STDIN)) {
                $text .= $line;
            }
        } else {
            $text = $input->getOption('text');
        }

        if (empty($text)) {
            throw new InvalidArgumentException('Convert text cannot be empty');
        }

        $map = array(
            0 => '<whitec>  </whitec>',
            1 => '<blackc>  </blackc>',
        );
        $this->initStyle($output);
        $text        = QRcode::text($text);
        $length      = strlen($text[0]);
        $paddingLine = str_repeat($map[0], $length + $lrPadding * 2)."\n";
        $after       = $before = str_repeat($paddingLine, $tbPadding);
        $output->write($before);
        foreach ($text as $line) {
            $output->write(str_repeat($map[0], $lrPadding));
            for ($i = 0; $i < $length; $i++) {
                $type = substr($line, $i, 1);
                $output->write($map[$type]);
            }
            $output->writeln(str_repeat($map[0], $lrPadding));
        }
        $output->write($after);
    }

    protected function initStyle(OutputInterface $output)
    {
        $style = new OutputFormatterStyle('black', 'black');
        $output->getFormatter()->setStyle('blackc', $style);
        $style = new OutputFormatterStyle('white', 'white');
        $output->getFormatter()->setStyle('whitec', $style);
    }
}
