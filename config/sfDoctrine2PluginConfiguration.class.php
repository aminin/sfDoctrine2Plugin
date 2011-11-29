<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfDoctrine2Plugin configuration class
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 */
class sfDoctrine2PluginConfiguration extends sfPluginConfiguration
{
  private static $initializedAutoload = false;

  public function initialize()
  {
    sfConfig::set('sf_orm', 'doctrine2');

    // Generator config
    // Do not overwrite user defined values
    sfConfig::set('sf_generator_proxy_dir', sfConfig::get('sf_generator_proxy_dir', sfConfig::get('sf_lib_dir').'/Proxy'));
    sfConfig::set('sf_generator_proxy_ns',  sfConfig::get('sf_generator_proxy_ns', 'Proxy'));
    sfConfig::set('sf_generator_form_dir',  sfConfig::get('sf_generator_form_dir', sfConfig::get('sf_lib_dir').'/form/doctrine2'));
    sfConfig::set('sf_generator_form_ns',   sfConfig::get('sf_generator_form_ns', ''));

    if (!sfConfig::get('sf_admin_module_web_dir'))
    {
      sfConfig::set('sf_admin_module_web_dir', '/sfDoctrine2Plugin');
    }

    if (sfConfig::get('sf_web_debug'))
    {
      require_once __DIR__.'/../lib/debug/sfWebDebugPanelDoctrine2.class.php';
      $this->dispatcher->connect('debug.web.load_panels', array('sfWebDebugPanelDoctrine2', 'listenToAddPanelEvent'));
    }

    // Autoloading
    if (!self::$initializedAutoload) {
        $libDir = __DIR__ . '/../lib/vendor/doctrine2';
        $classes = array(
            'Doctrine\Common' => $libDir . '/Common/lib',
            'Doctrine\DBAL\Migrations' => $libDir . '/Migrations/lib',
            'Doctrine\DBAL'   => $libDir . '/DBAL/lib',
            'Doctrine\ORM'    => $libDir . '/ORM/lib',
            'Symfony'         => $libDir . '/ORM/lib/vendor',
        );
        require_once $libDir .'/Common/lib/Doctrine/Common/ClassLoader.php';
        foreach ($classes as $ns => $path) {
            $classLoader = new \Doctrine\Common\ClassLoader($ns, $path);
            $classLoader->register();
        }
        self::$initializedAutoload = true;
    }

    $this->dispatcher->connect('component.method_not_found', array($this, 'componentMethodNotFound'));
  }

  public function componentMethodNotFound(sfEvent $event)
  {
    $actions = $event->getSubject();
    $method = $event['method'];
    $args = $event['arguments'];

    if ($method == 'getEntityManager')
    {
      $databaseManager = $actions->getContext()->getDatabaseManager();
      $names = $databaseManager->getNames();
      if ($args)
      {
        $name = $args[0];
        if (!in_array($name, $names))
        {
          throw new sfException(
            sprintf('Could not get the entity manager for '.
                    'the database connection named: "%s"', $name)
          );
        }
        $database = $databaseManager->getDatabase($args[0]);
      } else {
        $database = $databaseManager->getDatabase(end($names));
      }

      $event->setReturnValue($database->getEntityManager());

      return true;
    }
    else if ($method == 'getEntityManagerFor')
    {
      $entityName = $args[0];
      if (is_object($entityName))
      {
        $entityName = get_class($entityName);
      }
      $databaseManager = $actions->getContext()->getDatabaseManager();
      $names = $databaseManager->getNames();
      foreach ($names as $name)
      {
        $em = $databaseManager->getDatabase($name)->getEntityManager();
        $cmf = $em->getMetadataFactory();
        if ($cmf->hasMetadataFor($entityName))
        {
          $event->setReturnValue($em);
          return true;
        }
      }
      return false;
    }
    else if ($method == 'getMetadataFor')
    {
      $entityName = $args[0];
      if (is_object($entityName))
      {
        $entityName = get_class($entityName);
      }
      $databaseManager = $actions->getContext()->getDatabaseManager();
      $names = $databaseManager->getNames();
      foreach ($names as $name)
      {
        $em = $databaseManager->getDatabase($name)->getEntityManager();
        $cmf = $em->getMetadataFactory();
        if ($cmf->hasMetadataFor($entityName))
        {
          $event->setReturnValue($cmf->getMetadataFor($entityName));
          return true;
        }
      }
      return false;
    }
    else
    {
      return false;
    }
  }
}
