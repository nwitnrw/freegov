<?php

namespace Drupal\degov\Robo\Plugin\Commands;

use Drupal\degov\Robo\Plugin\Commands\Exception\Performance\AlreadyExistingScreenLogfileException;
use Drupal\degov\Robo\Plugin\Commands\Exception\Performance\ExistingScreenProcessesException;
use Drupal\degov\Robo\Plugin\Commands\Exception\Performance\InvalidInstancesNumberException;
use Drupal\degov\Robo\Plugin\Commands\Exception\Performance\NoBehatFileException;
use Robo\Tasks as TasksAlias;

/**
 * Class PerformanceCommands.
 */
class PerformanceCommands extends TasksAlias {

  /**
   * Provides a command for running parallel Behat tests for measuring performance. Make sure the Chromedriver is
   * running, you are on a *NIX machine (MacOS or Linux) so you have the Screen application on your system. Also it's
   * required to have a correctly configured `behat.yml` file in your projects root folder.
   * @command degov:performance:run-tests
   */
  public function runParallelBehat(): void {
    if (!file_exists('behat.yml')) {
      try {
        throw new NoBehatFileException();
      }
      catch (NoBehatFileException $exception) {
        $this->showErrorMessage($exception->getMessage());
        exit();
      }
    }

    $screenLogfileQuestion = <<<HERE
Swipe any existing screen logfile? Otherwise the command will fail, if the current directory contains an already existing file with the `screenlog.0` filename. Because this file is used for indicating, if the test succeeded or failed. (yes/no)
HERE;

    $answerScreenLogFileSwipe = $this->askDefault($screenLogfileQuestion, 'yes');
    if ($answerScreenLogFileSwipe === 'yes') {
      exec('rm -f screenlog.*');
    }

    if (file_exists('screenlog.0')) {
      try {
        throw new AlreadyExistingScreenLogfileException();
      }
      catch (AlreadyExistingScreenLogfileException $exception) {
        $this->showErrorMessage($exception->getMessage());
        exit();
      }
    }

    if ($this->isBehatProcessRunning()) {
      try {
        throw new ExistingScreenProcessesException();
      }
      catch (ExistingScreenProcessesException $exception) {
        $this->showErrorMessage($exception->getMessage());
        exit();
      }
    }

    $answerLogfileDeletion = $this->askDefault('Should the log file be removed automatically, if all tests pass without failure? (yes/no)', 'yes');
    $answerInstances = (int) $this->askDefault('How many instances do you want me to run?', 5);

    if ($answerInstances <= 0) {
      try {
        throw new InvalidInstancesNumberException();
      }
      catch (InvalidInstancesNumberException $exception) {
        $this->showErrorMessage($exception->getMessage());
        exit();
      }
    }

    for ($i = 0; $i <= $answerInstances; ++$i) {
      exec("screen -dmSL behat-$i bin/behat");
    }

    $this->say('Tests are running..');

    $processesAreRunning = TRUE;

    while ($processesAreRunning === TRUE) {
      if (!$this->isBehatProcessRunning()) {
        $processesAreRunning = FALSE;
      }
    }

    if ($this->isErrorColorCodeInLogfile()) {
      $this->showErrorMessage('Test execution has finished, but some test has failed. Check the "screenlog.0 file" for details.');
      return;
    }

    if ($answerLogfileDeletion === 'yes') {
      exec('rm screenlog.0');
    }

    $this->showSuccessMessage('Test execution has finished. All tests passed successfully.');
  }

  private function isBehatProcessRunning(): bool {
    $command = 'screen -list';
    exec($command, $output);
    if (strpos(implode(' ', $output), 'behat-') !== FALSE) {
      return TRUE;
    }
    return FALSE;
  }

  private function showErrorMessage(string $text): void {
    $this->yell('[ERROR] ' . $text, 40, 'black');
  }

  private function showSuccessMessage(string $text): void {
    $this->yell('[SUCCESS] ' . $text, 40, 'green');
  }

  private function isErrorColorCodeInLogfile(): bool {
    $logfileContents = file_get_contents('screenlog.0');
    return strpos($logfileContents, '[31m') !== FALSE;
  }

}
