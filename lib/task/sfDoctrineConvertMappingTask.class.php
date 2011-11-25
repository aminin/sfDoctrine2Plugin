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
 * Convert Doctrine mapping information between various supported formats.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 */
class sfDoctrineConvertMappingTask extends sfDoctrine2BaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $task = new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand();

        // Options
        $this->addOptions(array(
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        ));

        $this->importTaskDefinition($task);

        $this->aliases = array();
        $this->namespace = 'doctrine2';
        $this->name = 'convert-mapping';
    }


    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $arguments = $this->prepareDoctrineCliArguments($arguments, $this->argumentNames);
        $options   = $this->prepareDoctrineCliOptions($options, $this->optionNames);

        $this->callDoctrineCli('orm:convert-mapping', array_merge($options, $arguments));
    }

}
