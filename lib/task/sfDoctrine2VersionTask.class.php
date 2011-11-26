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
 * Check Doctrine version task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 */
class sfDoctrine2VersionTask extends sfDoctrine2BaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'doctrine2';
    $this->name = 'version';
    $this->briefDescription = 'Check which version of Doctrine you are using';

    $this->detailedDescription = <<<EOF
The [doctrine2:version|INFO] outputs which version of Doctrine you are using:

  [./symfony doctrine2:version|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->callDoctrineCli('--version');
  }
}
