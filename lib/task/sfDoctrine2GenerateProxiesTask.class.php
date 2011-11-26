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
 * Generate Doctrine proxy classes
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn <russ@eatmymonkeydust.com>
 */
class sfDoctrine2GenerateProxiesTask extends sfDoctrine2BaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->aliases = array();
    $this->namespace = 'doctrine2';
    $this->name = 'generate-proxies';
    $this->briefDescription = 'Generate the Doctrine proxy classes';

    $this->detailedDescription = <<<EOF
The [doctrine2:version|INFO] generates the Doctrine proxy clases using your configured proxy directory

  [./symfony doctrine2:generate-proxies|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $arguments = array('dest-path' => sfConfig::get('sf_generator_proxy_dir'));
    $this->callDoctrineCli('orm:generate-proxies', $arguments);
  }
}
