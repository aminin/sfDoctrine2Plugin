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
 * Create classes for the current model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 */
class sfDoctrine2BuildModelTask extends sfDoctrine2BaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $command = new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand();
        $this->importTaskDefinition($command, $ignore = array('dest-path'));

        $this->aliases = array('doctrine-build-model');
        $this->namespace = 'doctrine2';
        $this->name = 'build-model';
    }


    /**
     * @see sfTask
     */
    protected function execute($arguments = array(), $options = array())
    {
        $arguments = $this->prepareDoctrineCliArguments($arguments, $this->argumentNames);
        $arguments['dest-path'] = sfConfig::get('sf_lib_dir');

        $options   = $this->prepareDoctrineCliOptions($options, $this->optionNames);

        $this->callDoctrineCli('orm:generate-entities', array_merge($options, $arguments));
        $this->reloadAutoload();
    }

}
