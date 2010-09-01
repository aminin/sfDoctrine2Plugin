<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all symfony Doctrine tasks.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn <russ@eatmymonkeydust.com>
 * @author     Maxim Oleinik <maxim.oleinik@gmail.com>
 */

abstract class sfDoctrineBaseTask extends sfBaseTask
{
    protected
        $optionNames   = array(),
        $argumentNames = array();

    static protected $databaseManagers = array();


    /**
     * Prepare arguments
     *
     * @param  array $arguments
     * @param  array $keys
     * @return array
     */
    protected function prepareDoctrineCliArguments(array $arguments, array $keys)
    {
        return array_intersect_key($arguments, array_flip($keys));
    }


    /**
     * Prepare options
     *
     * @param  array $arguments
     * @param  array $keys
     * @return array
     */
    protected function prepareDoctrineCliOptions(array $options, array $keys)
    {
        $result = array();
        $options = array_intersect_key($options, array_flip($keys));

        if ($options) {
            $optionsKeys = array_keys($options);
            foreach ($optionsKeys as &$key) {
                $key = '--'.$key;
            }
            $result = array_combine($optionsKeys, $options);
        }

        return $result;
    }


    /**
     * Inport task definition
     *
     * @param  $command
     * @param  array $ignoreKeys
     * @return void
     */
    protected function importTaskDefinition(Symfony\Component\Console\Command\Command $command, array $ignoreKeys = array())
    {
        $definition = $command->getDefinition();

        if ($ignoreKeys) {
            $ignoreKeys = array_flip($ignoreKeys);
        }

        // Options
        foreach ($definition->getOptions() as $item) {

            if ($ignoreKeys && isset($ignoreKeys[$item->getName()])) {
                continue;
            }

            $default = $item->getDefault();
            if ($item->isParameterRequired()) {
                $mode = sfCommandOption::PARAMETER_REQUIRED;
            } else if ($item->isParameterOptional()) {
                $mode = sfCommandOption::PARAMETER_OPTIONAL;
            } else if ($item->isArray()) {
                $mode = sfCommandOption::IS_ARRAY;
            } else {
                $mode = sfCommandOption::PARAMETER_NONE;
                $default = null;
            }

            $this->addOption(
                $item->getName(), $item->getShortcut(), $mode, $item->getDescription(), $default
            );
            $this->optionNames[] = $item->getName();
        }

        // Arguments
        foreach ($definition->getArguments() as $item) {

            if ($ignoreKeys && isset($ignoreKeys[$item->getName()])) {
                continue;
            }

            if ($item->isRequired()) {
                $mode = sfCommandArgument::REQUIRED;
            } else if ($item->isArray()) {
                $mode = sfCommandArgument::IS_ARRAY;
            } else {
                $mode = sfCommandArgument::OPTIONAL;
            }

            $this->addArgument(
                $item->getName(), $mode, $item->getDescription(), $item->getDefault()
            );
            $this->argumentNames[] = $item->getName();
        }

        $this->importTaskDescription($command);
    }


    /**
     * Inport task description
     *
     * @param  $command
     * @return void
     */
    protected function importTaskDescription(Symfony\Component\Console\Command\Command $command)
    {
        $this->briefDescription = $command->getDescription();
        $help = str_replace('%command.full_name%', $command->getFullName(), strip_tags($command->getHelp()));
        $this->detailedDescription = $help;
    }


  protected function getCli()
  {
    $helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
        'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($this->getEntityManager()),
        'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($this->getEntityManager()->getConnection()),
        'dialog' => new \Symfony\Component\Console\Helper\DialogHelper(),
    ));

    $cli = new \Symfony\Component\Console\Application('Doctrine Command Line Interface', Doctrine\Common\Version::VERSION);
    $cli->setCatchExceptions(false);
    $cli->setAutoExit(false);
    $cli->setHelperSet($helperSet);
    $cli->addCommands(array(
      // DBAL Commands
      new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
      new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

      // ORM Commands
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
      new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
      new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
      new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
      new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
      new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
      new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
      new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
      new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),

      // Migrations Commands
      new \Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand(),
      new \Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand(),
      new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand(),
      new \Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand(),
      new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand(),
      new \Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand()
    ));

    return $cli;
  }

  protected function callDoctrineCli($task, $arguments = array())
  {
    $this->initDBM();

    $args = array($task);
    $args = array_merge($args, $arguments);

    $input = new \Symfony\Component\Console\Input\ArrayInput($args);

    $output = new sfDoctrineCliPrinter();
    $output->setFormatter($this->formatter);
    $output->setDispatcher($this->dispatcher);

    $cli = $this->getCli();
    return $cli->run($input, $output);
  }

  protected function initDBM()
  {
    $hash = spl_object_hash($this->configuration);

    if (!isset(self::$databaseManagers[$hash]))
    {
      self::$databaseManagers[$hash] = new sfDatabaseManager($this->configuration);
    }

    return self::$databaseManagers[$hash];
  }

  protected function getEntityManager($name = null)
  {
    $databaseManager = $this->initDBM();

    $names = $databaseManager->getNames();

    if ($name !== null)
    {
      if (!in_array($name, $names))
      {
        throw new sfException(
          sprintf('Could not get the entity manager for '.
                  'the database connection named: "%s"', $name)
        );
      }
      $database = $databaseManager->getDatabase($name);
    } else {
      $database = $databaseManager->getDatabase(end($names));
    }

    return $database->getEntityManager();
  }

}
