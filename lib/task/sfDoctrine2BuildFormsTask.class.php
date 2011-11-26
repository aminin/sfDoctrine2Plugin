<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrine2BaseTask.class.php');

/**
 * Create form classes for the current model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 */
class sfDoctrine2BuildFormsTask extends sfDoctrine2BaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->namespace = 'doctrine2';
    $this->name = 'build-forms';
    $this->briefDescription = 'Creates form classes for the current model';

    $this->detailedDescription = <<<EOF
The [doctrine2:build-forms|INFO] task creates form classes from the schema:

  [./symfony doctrine2:build-forms|INFO]

The task read the schema information in [config/*schema.xml|COMMENT] and/or
[config/*schema.yml|COMMENT] from the project and all installed plugins.

The model form classes files are created in [lib/form|COMMENT].

This task never overrides custom classes in [lib/form|COMMENT].
It only replaces base classes generated in [lib/form/base|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection('doctrine', 'generating form classes');
    $databaseManager = $this->initDBM();
    $generatorManager = new sfGeneratorManager($this->configuration);
    $generatorManager->generate('sfDoctrineFormGenerator', array(
      'database_manager' => $databaseManager,
    ));

    $properties = parse_ini_file(sfConfig::get('sf_config_dir').DIRECTORY_SEPARATOR.'properties.ini', true);

    $ns = trim(sfConfig::get('sf_generator_form_ns'), '\\/');

    $constants = array(
      'PROJECT_NAME' => isset($properties['symfony']['name']) ? $properties['symfony']['name'] : 'symfony',
      'AUTHOR_NAME'  => isset($properties['symfony']['author']) ? $properties['symfony']['author'] : 'Your name here',
      'NAMESPACE'    => $ns ? "namespace {$ns};": '',
    );

    // customize php and yml files
    $rootDir = sfConfig::get('sf_generator_form_dir');
    $finder = sfFinder::type('file')->name('*.php');
    $this->getFilesystem()->replaceTokens($finder->in($rootDir), '##', '##', $constants);

    $this->reloadAutoload();
  }
}
