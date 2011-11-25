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
 * Show current migration status
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Maxim Oleinik <maxim.oleinik@gmail.com>
 */
class sfDoctrineMigrationsStatusTask extends sfDoctrineMigrationsBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        // Options
        $this->addOptions(array(
          new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
        ));

        $this->task = new \Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand();
        $this->importTaskDefinition($this->task);

        $this->namespace = $this->task->getNamespace();
        $this->name = $this->task->getName();
    }

}
