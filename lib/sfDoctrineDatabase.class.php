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
 * @version    SVN: $Id: sfDoctrineDatabase.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineDatabase extends sfDatabase
{
  protected $em;

  static private $conn;

  public function initialize($parameters = array())
  {
    parent::initialize($parameters);

    $schema = $this->getParameter('schema');
    $connectionName = $this->getParameter('name');
    $connectionOptions = $this->getParameter('options');
    $plugins = (array) $this->getParameter('plugins');

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

    $enabledPlugins = $configuration->getPlugins();
    foreach ($configuration->getAllPluginPaths() as $plugin => $path)
    {
      if (!in_array($plugin, $enabledPlugins) || !in_array($plugin, $plugins))
      {
        continue;
      }
      $paths[] = $path.'/config/doctrine';
    }
    $paths = array_unique($paths);

    $config->setMetadataDriverImpl(new YamlDriver($paths));
    $config->setProxyDir(sfConfig::get('sf_lib_dir') . '/Proxies');
    $config->setProxyNamespace('Proxies');

    if (sfConfig::get('sf_debug'))
    {
      $config->setSqlLogger(new sfDoctrineSqlLogger($configuration->getEventDispatcher()));
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
