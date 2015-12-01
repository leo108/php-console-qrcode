<?php
namespace ConsoleQrCode\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use PHPQRCode\QRcode;

class ConvertCommand extends Command {
    protected function configure() {
        $this->setName('convert')
             ->addOption('text', null, InputOption::VALUE_REQUIRED, 'The text you want to convert to QRcode')
             ->addOption('lr', null, InputOption::VALUE_REQUIRED, 'The left and right padding you want the QRcode has', 1)
             ->addOption('tb', null, InputOption::VALUE_REQUIRED, 'The top and bottom padding you want the QRcode has', 1)
             ->addOption('stdin', null, InputOption::VALUE_NONE, 'Read the text from SDTIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $lrPadding = $input->getOption('lr');
        $tbPadding = $input->getOption('tb');
        if($input->getOption('stdin')) {
            $text = '';
            while($line = fgets(STDIN)){
                $text .= $line;
            }
        } else {
            $text = $input->getOption('text');
        }

        if (empty($text)) {
            $output->getErrorOutput()->writeln('<error>Convert text cannot be empty</error>');
            return;
        }

        $map = array(
            0 => '<whitec>  </whitec>',
            1 => '<blackc>  </blackc>',
        );
        $this->initStyle($output);
        $text   = QRcode::text($text);
        $length = strlen($text[0]);

        $screenSize = $this->getTTYSize();
        if(!$screenSize) {
            $output->getErrorOutput()->writeln('<comment>Get Screen Size Failed</comment>');
        } else {
            list($maxLines, $maxCols) = $screenSize;
            $qrCols = 2 * ($length + $lrPadding * 2);
            $qrLines = count($text) + $tbPadding * 2;
            if($qrCols > $maxCols || $qrLines > $maxLines){
                $output->getErrorOutput()->writeln('<error>Max Lines/Columns Reached</error>');
                return;
            }
        }

        $paddingLine = str_repeat($map[0], $length + $lrPadding * 2) . "\n";
        $after = $before = str_repeat($paddingLine, $tbPadding);
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

    private function initStyle(OutputInterface $output) {
        $style = new OutputFormatterStyle('black', 'black');
        $output->getFormatter()->setStyle('blackc', $style);
        $style = new OutputFormatterStyle('white', 'white');
        $output->getFormatter()->setStyle('whitec', $style);
    }

    private function getTTYSize() {
        if(!posix_isatty(STDOUT)){
            return false;
        }
        $ttyName = posix_ttyname(STDOUT);

        $builder = new ProcessBuilder();
        $process = $builder->setPrefix('stty')->setArguments(array('-f', $ttyName, 'size'))->getProcess();
        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            return false;
        }
        $output = $process->getOutput();
        if(!preg_match('~^(\d+)\s+(\d+)$~', $output, $match)) {
            return false;
        }
        return array($match[1], $match[2]);
    }
}
