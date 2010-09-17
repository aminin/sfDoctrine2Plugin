<?php

/**
 * sfValidatorDoctrineDateTime extends standart validator to return DateTime object
 *
 * @author Maxim Oleinik <maxim.oleinik@gmail.com>
 */
class sfValidatorDoctrineDateTime extends sfValidatorDateTime
{
  /**
   * @see sfValidatorBase
   */
    protected function doClean($value)
    {
        return new DateTime(parent::doClean($value));
    }
}
