<?php
use Symfony\Component\Console\Output\OutputInterface;

class sfDoctrine2CliPrinter implements OutputInterface
{
  protected
    $formatter,
    $dispatcher,
    $level = Symfony\Component\Console\Output\Output::VERBOSITY_VERBOSE;

  public function setFormatter($formatter)
  {
    $this->formatter = $formatter;
  }

  public function setDispatcher($dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public function logSection($section, $message, $size = null, $style = 'INFO')
  {
    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($this->formatter->formatSection($section, $message, $size, $style))));
  }

  /**
   * Writes a message to the output.
   *
   * @param string|array $messages The message as an array of lines of a single string
   * @param Boolean      $newline  Whether to add a newline or not
   * @param integer      $type     The type of output
   */
  public function write($messages, $newline = false, $type = 0)
  {
    $this->logSection("Doctrine", $messages);
    return $this;
  }

  /**
   * Writes a message to the output and adds a newline at the end.
   *
   * @param string|array $messages The message as an array of lines of a single string
   * @param integer      $type     The type of output
   */
  public function writeln($messages, $type = 0)
  {
    $this->write($messages, true, $type);
  }

  public function setVerbosity($level)
  {
    $this->level = $level;
  }

  public function getVerbosity()
  {
    return $this->level;
  }

  public function setDecorated($decorated)
  {
  }
}
