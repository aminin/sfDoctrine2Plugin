<?php

/**
 * sfValidatorDoctrine2DateTime extends standart validator to return DateTime object
 *
 * @author Maxim Oleinik <maxim.oleinik@gmail.com>
 */
class sfValidatorDoctrine2DateTime extends sfValidatorDateTime
{
  /**
   * @see sfValidatorBase
   */
    protected function doClean($value)
    {
        return new DateTime(parent::doClean($value));
    }
}
