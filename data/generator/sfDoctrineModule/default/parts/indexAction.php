  public function executeIndex(sfWebRequest $request)
  {
<?php if (isset($this->params['with_doctrine_route']) && $this->params['with_doctrine_route']): ?>
    $this-><?php echo $this->getPluralName() ?> = $this->getRoute()->getObjects();
<?php else: ?>
    $this-><?php echo $this->getPluralName() ?> = doctrine2::getTable('<?php echo $this->getModelClass() ?>')
      ->createQuery('a')
      ->execute();
<?php endif; ?>
  }
