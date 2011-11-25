<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrine2BaseTask.class.php');

/**
 * Base migration task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Maxim Oleinik <maxim.oleinik@gmail.com>
 */
abstract class sfDoctrineMigrationsBaseTask extends sfDoctrine2BaseTask
{
    protected $task;


    /**
     * Get Docrine2 task
     *
     * @return Symfony\Component\Console\Command\Command
     */
    public function getTask()
    {
        if (!$this->task || !$this->task instanceof Symfony\Component\Console\Command\Command) {
            throw new Exception(__METHOD__.": Expected Doctrine2 task is initialized");
        }
        return $this->task;
    }


    /**
     * @see sfTask
     */
    final protected function execute($arguments = array(), $options = array())
    {
        $arguments = $this->prepareDoctrineCliArguments($arguments, $this->argumentNames);
        $options   = $this->prepareDoctrineCliOptions($options, $this->optionNames);

        $this->callDoctrineCli($this->getTask()->getFullName(), array_merge($options, $arguments));
    }


    /**
     * Inport task definition
     *
     * @see parent
     */
    protected function importTaskDefinition(Symfony\Component\Console\Command\Command $command, array $ignoreKeys = array())
    {
        $ignoreKeys = array_merge($ignoreKeys, array('configuration', 'db-configuration'));
        return parent::importTaskDefinition($command, $ignoreKeys);
    }


    /**
     * Prepare options
     * @see parent
     */
    protected function prepareDoctrineCliOptions(array $options, array $keys)
    {
        $options = parent::prepareDoctrineCliOptions($options, $keys);

        $conf = sfConfig::get('sf_config_dir') . '/migrations.yml';
        if (!file_exists($conf)) {
            $conf = __DIR__ . '/../../config/migrations.yml';
        }
        $options['--configuration'] = $conf;
        $this->_checkMigrationsDir($conf);

        return $options;
    }


    /**
     * Create migration dir if not exists
     *
     * @param  string $conf - Path to config file
     * @return void
     */
    private function _checkMigrationsDir($conf)
    {
        $data = \Symfony\Component\Yaml\Yaml::load($conf);

        $dir = $data['migrations_directory'];
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new Exception("Failed to create migration dir `{$dir}`");
            }
        }
    }
}
