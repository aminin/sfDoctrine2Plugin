<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Generate migration class
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Maxim Oleinik <maxim.oleinik@gmail.com>
 */
class sfDoctrineMigrationsGenerateTask extends sfDoctrineMigrationsBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->task = new \Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand();
        $this->importTaskDefinition($this->task);

        $this->namespace = $this->task->getNamespace();
        $this->name = $this->task->getName();
    }

}
