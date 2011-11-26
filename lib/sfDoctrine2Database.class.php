<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use \Doctrine\ORM\Mapping\Driver\YamlDriver;

/**
 * Represents a single Symfony Doctrine Database connection
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 */
class sfDoctrine2Database extends sfDatabase
{
  protected $em;

  static private $conn;

  public function initialize($parameters = array())
  {
    parent::initialize($parameters);

    $schema = $this->getParameter('schema');
    $connectionName = $this->getParameter('name');
    $connectionOptions = $this->getParameter('options');

    $config = new \Doctrine\ORM\Configuration();
    $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);

    $configuration = sfProjectConfiguration::getActive();

    $paths = array();
    if ($schema) {
      $paths[] = $schema;
    } else {
      $paths[] = sfConfig::get('sf_config_dir') . '/doctrine';
    }

    $paths[] = realpath(__DIR__ . '/../config/doctrine');
    $paths[] = realpath(sfConfig::get('sf_config_dir').'/doctrine');

    $enabledPlugins = $configuration->getPlugins();
    foreach ($configuration->getAllPluginPaths() as $plugin => $path)
    {
      if (!in_array($plugin, $enabledPlugins))
      {
        continue;
      }
      $pluginPath = $path.'/config/doctrine';
      if (is_dir($pluginPath)) {
          $paths[] = $path.'/config/doctrine';
      }
    }
    $paths = array_unique($paths);
    $config->setMetadataDriverImpl(new YamlDriver($paths));

    // Proxy
    $config->setProxyDir(sfConfig::get('sf_generator_proxy_dir'));
    $config->setProxyNamespace(sfConfig::get('sf_generator_proxy_ns'));

    if (sfConfig::get('sf_debug'))
    {
      $config->setSqlLogger(new sfDoctrine2SqlLogger($configuration->getEventDispatcher()));
    }

    $method = sprintf('configureDoctrineConnection%s', $connectionName);
    $methodExists = method_exists($configuration, $method);

    if (method_exists($configuration, 'configureDoctrineConnection') && !$methodExists)
    {
      $configuration->configureDoctrineConnection($config);
    } else if ($methodExists) {
      $configuration->$method($config);
    }

    if (!self::$conn) {
        self::$conn = \Doctrine\DBAL\DriverManager::getConnection($connectionOptions, $config, new \Doctrine\Common\EventManager);
    }
    $this->em = \Doctrine\ORM\EntityManager::create(self::$conn, $config);

    if (method_exists($configuration, 'configureEntityManager'))
    {
      $configuration->configureEntityManager($this->em);
    }

  }

  public function connect()
  {
    return self::$conn->connect();
  }

  public function shutdown()
  {
    return self::$conn->close();
  }

  public function getEntityManager()
  {
    return $this->em;
  }
}
