<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2015, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\Utils\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Container aware command abstract implementation
 */
abstract class AbstractContainerAwareCommand extends ContainerAwareCommand
{
    const MESSAGE_COMMENT  = 'comment';
    const MESSAGE_ERROR    = 'error';
    const MESSAGE_INFO     = 'info';
    const MESSAGE_QUESTION = 'question';
    const MESSAGE_REGULAR  = 'fg=white';

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    protected $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var bool
     */
    private $initialized;

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->init($input, $output);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Input
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output
     */
    protected function init(InputInterface $input, OutputInterface $output)
    {
        if ($this->initialized) {
            return;
        }

        $this->input = $input;
        $this->output = $output;

        $this->initialized = true;
    }

    /**
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $type     The type of output (one of the OUTPUT constants)
     */
    protected function comment($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->write($messages, self::MESSAGE_COMMENT, $newline, $type);
    }

    /**
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $type     The type of output (one of the OUTPUT constants)
     */
    protected function error($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->write($messages, self::MESSAGE_ERROR, $newline, $type);
    }

    /**
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $type     The type of output (one of the OUTPUT constants)
     */
    protected function info($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->write($messages, self::MESSAGE_INFO, $newline, $type);
    }

    /**
     * @param string|array $messages The message as an array of lines or a single string
     * @param bool         $newline  Whether to add a newline
     * @param int          $type     The type of output (one of the OUTPUT constants)
     */
    protected function question($messages, $newline = true, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->write($messages, self::MESSAGE_QUESTION, $newline, $type);
    }

    /**
     * @param string|array $messages     The message as an array of lines or a single string
     * @param string       $messagesType Messages type
     * @param int          $type         The type of output (one of the OUTPUT constants)
     */
    protected function writeln($messages, $messagesType = self::MESSAGE_REGULAR, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->write($messages, $messagesType, true, $type);
    }

    /**
     * @param string|array $messages     The message as an array of lines or a single string
     * @param string       $messagesType Messages type
     * @param bool         $newline      Whether to add a newline
     * @param int          $type         The type of output (one of the OUTPUT constants)
     */
    protected function write(
        $messages,
        $messagesType = self::MESSAGE_REGULAR,
        $newline = false,
        $type = OutputInterface::OUTPUT_NORMAL
    ) {
        $this->checkIfInitialized();

        if (is_array($messages)) {
            foreach ($messages as &$message) {
                $this->decorateMessage($message, $messagesType);
            }

            unset($message);
        } else {
            $this->decorateMessage($messages, $messagesType);
        }

        $this->output->write($messages, $newline, $type);
    }

    /**
     * @param string $message     Message to decorate
     * @param string $messageType Message type
     */
    private function decorateMessage(&$message, $messageType)
    {
        $message = sprintf('<%s>%s</%1$s>', $messageType, $message);
    }

    private function checkIfInitialized()
    {
        if (!$this->initialized) {
            throw new CommandException(sprintf('You forgot to call "%s::init()".', __CLASS__));
        }
    }
}
