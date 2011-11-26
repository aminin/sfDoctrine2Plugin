<?php

class sfDoctrine2SqlLogger implements Doctrine\DBAL\Logging\SQLLogger
{
  protected $dispatcher;

  public function __construct($dispatcher)
  {
    $this->dispatcher = $dispatcher;
  }

  public function startQuery($sql, array $params = null, array $types = null)
  {
  	$this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('query : %s - (%s)', $sql, join(', ', self::fixParams($params))))));
  }

  public function stopQuery()
  {
  }

  /**
   * Fixes query parameters for logging.
   *
   * @param  array $params
   *
   * @return array
   */
  static public function fixParams($params)
  {
    foreach ((array) $params as $key => $param)
    {
      if (is_object($param))
      {
        $params[$key] = "(Object, " . get_class($param) . ")";
      }
      elseif (strlen($param) >= 255)
      {
        $params[$key] = '['.number_format(strlen($param) / 1024, 2).'Kb]';
      }
    }

    return $params;
  }
}
